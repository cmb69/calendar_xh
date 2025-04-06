<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2023 Christoph M. Becker
 *
 * This file is part of Calendar_XH.
 *
 * Calendar_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Calendar_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Calendar_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Calendar;

use Plib\View;
use XH\CSRFProtection as CsrfProtector;

class EditEventsController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var LocalDateTime */
    private $now;

    /** @var EventDataService */
    private $eventDataService;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    /** @var string */
    private $url;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     */
    public function __construct(
        string $pluginFolder,
        array $conf,
        array $lang,
        LocalDateTime $now,
        EventDataService $eventDataService,
        CSRFProtector $csrfProtector,
        View $view,
        string $url
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->lang = $lang;
        $this->now = $now;
        $this->eventDataService = $eventDataService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
        $this->url = $url;
    }

    public function __invoke(): Response
    {
        switch ($_GET["action"] ?? "") {
            case "create":
                return empty($_POST) ? $this->createAction() : $this->doCreateAction();
            case "update":
                return empty($_POST) ? $this->updateAction() : $this->doUpdateAction();
            case "delete":
                return empty($_POST) ? $this->deleteAction() : $this->doDeleteAction();
            default:
                return $this->defaultAction();
        }
    }

    private function defaultAction(): Response
    {
        $events = $this->eventDataService->readEvents();
        $events = array_map(function (Event $event): array {
            return [
                "start_date" => $event->getIsoStartDate(),
                "end_date" => $event->getIsoEndDate(),
                "summary" => $event->summary,
            ];
        }, $events);
        $output = $this->view->render('event-table', [
            'selected' => $this->url ? $this->url : 'calendar',
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'events' => $events,
            'hash' => sha1(serialize($events)),
            'jsUrl' => "{$this->pluginFolder}js/overview.min.js",
        ]);
        return Response::create($output);
    }

    private function createAction(): Response
    {
        $event = $this->createDefaultEvent();
        return Response::create($this->renderEditForm($event, null, "create"));
    }

    private function updateAction(): Response
    {
        $events = $this->eventDataService->readEvents();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        if (!array_key_exists($_GET['event_id'], $events)) {
            return $this->redirectToOverviewResponse();
        }
        $id = $_GET['event_id'];
        $event = $events[$id];
        return Response::create($this->renderEditForm($event, $id, "update"));
    }

    private function deleteAction(): Response
    {
        $events = $this->eventDataService->readEvents();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        if (!array_key_exists($_GET['event_id'], $events)) {
            return $this->redirectToOverviewResponse();
        }
        $id = $_GET['event_id'];
        $event = $events[$id];
        return Response::create($this->renderEditForm($event, $id, "delete"));
    }

    /**
     * @param string|null $id
     */
    private function renderEditForm(Event $event, $id, string $action): string
    {
        $url = "?{$this->url}&admin=plugin_main&action=$action";
        if ($id !== null) {
            $url .= "&event_id=$id";
        }
        $label = $action === "delete" ? "label_delete" : "label_save";
        return $this->view->render('edit-form', [
            'action' => $url,
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'event' => [
                "start_date" => $event->getIsoStartDate(),
                "start_time" => $event->getIsoStartTime(),
                "end_date" => $event->getIsoEndDate(),
                "end_time" => $event->getIsoEndTime(),
                "summary" => $event->summary,
                "linkadr" => $event->linkadr,
                "linktxt" => $event->linktxt,
                "location" => $event->location,
            ],
            'button_label' => $this->lang[$label],
            'csrf_token' => $this->csrfProtector->tokenInput(),
        ]);
    }

    private function doCreateAction(): Response
    {
        $this->csrfProtector->check();
        $events = $this->eventDataService->readEvents();
        return $this->upsert($events, null);
    }

    private function doUpdateAction(): Response
    {
        $this->csrfProtector->check();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        $id = $_GET['event_id'];
        $events = $this->eventDataService->readEvents();
        return $this->upsert($events, array_key_exists($id, $events) ? $id : null);
    }

    /**
     * @param Event[] $events
     * @param string|null $id
     */
    private function upsert(array $events, $id): Response
    {
        $varnames = array(
            'datestart', 'dateend', 'starttime', 'endtime', 'event', 'linkadr', 'linktxt', 'location'
        );
        $post = [];
        foreach ($varnames as $var) {
            assert(!isset($_POST[$var]) || is_string($_POST[$var]));
            $post[$var] = isset($_POST[$var]) ? $_POST[$var] : "";
        }
        if (!$this->isValidDate($post['datestart'])) {
            $post['datestart'] = '';
        }
        if (!$this->isValidDate($post['dateend'])) {
            $post['dateend'] = '';
        }
        $maybeEvent = Event::create(...array_values($post));
        if ($maybeEvent === null) {
            return $this->redirectToOverviewResponse();
        }
        if ($id !== null) {
            $events[$id] = $maybeEvent;
        } else {
            $events[] = $maybeEvent;
        }

        // sorting new event inputs, idea of manu, forum-message
        uasort($events, /** @return int */ function (Event $a, Event $b) {
            return $a->start->compare($b->start);
        });
        /** @var Event[] $events */
        if ($this->eventDataService->writeEvents($events)) {
            return $this->redirectToOverviewResponse();
        } else {
            $output = XH_message('fail', $this->lang['eventfile_not_saved'])
                . $this->renderEditForm($maybeEvent, $id, $id !== null ? "create" : "update");
            return Response::create($output);
        }
    }

    private function doDeleteAction(): Response
    {
        $this->csrfProtector->check();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        $id = $_GET['event_id'];
        $events = $this->eventDataService->readEvents();
        if (!array_key_exists($id, $events)) {
            return $this->redirectToOverviewResponse();
        }
        $event = $events[$id];
        unset($events[$id]);
        if ($this->eventDataService->writeEvents($events)) {
            return $this->redirectToOverviewResponse();
        } else {
            $output = XH_message('fail', $this->lang['eventfile_not_saved'])
                . $this->renderEditForm($event, $id, "delete");
            return Response::create($output);
        }
    }

    private function redirectToOverviewResponse(): Response
    {
        if ($this->url === '' || $this->url === 'calendar') {
            $url = CMSIMPLE_URL . "?calendar&admin=plugin_main&action=plugin_text";
        } else {
            $url = CMSIMPLE_URL . "?{$this->url}";
        }
        return Response::createRedirect($url);
    }

    private function isValidDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    private function createDefaultEvent(): Event
    {
        $event = Event::create(
            $this->now->getIsoDate(),
            '',
            '',
            '',
            $this->lang['event_summary'],
            '',
            '',
            ''
        );
        assert($event !== null);
        return $event;
    }
}
