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

use Plib\CsrfProtector;
use Plib\Request;
use Plib\Response;
use Plib\View;

class EditEventsController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var EventDataService */
    private $eventDataService;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        array $conf,
        EventDataService $eventDataService,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->eventDataService = $eventDataService;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action") ?? "") {
            case "create":
                return empty($_POST) ? $this->createAction($request) : $this->doCreateAction($request);
            case "update":
                return empty($_POST) ? $this->updateAction($request) : $this->doUpdateAction($request);
            case "delete":
                return empty($_POST) ? $this->deleteAction($request) : $this->doDeleteAction($request);
            default:
                return $this->defaultAction($request);
        }
    }

    private function defaultAction(Request $request): Response
    {
        $events = $this->eventDataService->readEvents();
        $events = array_map(function (Event $event): array {
            return [
                "start_date" => $event->getIsoStartDate(),
                "end_date" => $event->getIsoEndDate(),
                "summary" => $event->summary,
            ];
        }, $events);
        $js = $this->pluginFolder . "js/overview.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "js/overview.js";
        }
        $output = $this->view->render('event-table', [
            'selected' => $request->selected() ? $request->selected() : 'calendar',
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'events' => $events,
            'hash' => sha1(serialize($events)),
            'jsUrl' => $request->url()->path($js)->with("v", CALENDAR_VERSION)->relative(),
        ]);
        return Response::create($output);
    }

    private function createAction(Request $request): Response
    {
        $event = $this->createDefaultEvent($request);
        return Response::create($this->renderEditForm($request, $event, null, "create"));
    }

    private function updateAction(Request $request): Response
    {
        $events = $this->eventDataService->readEvents();
        $id = $request->get("event_id");
        if ($id === null || !array_key_exists($id, $events)) {
            return $this->redirectToOverviewResponse($request);
        }
        $event = $events[$id];
        return Response::create($this->renderEditForm($request, $event, $id, "update"));
    }

    private function deleteAction(Request $request): Response
    {
        $events = $this->eventDataService->readEvents();
        $id = $request->get("event_id");
        if ($id === null || !array_key_exists($id, $events)) {
            return $this->redirectToOverviewResponse($request);
        }
        $event = $events[$id];
        return Response::create($this->renderEditForm($request, $event, $id, "delete"));
    }

    /**
     * @param string|null $id
     */
    private function renderEditForm(Request $request, Event $event, $id, string $action): string
    {
        $url = $request->url()->with("admin", "plugin_main")->with("action", $action);
        if ($id !== null) {
            $url = $url->with("event_id", $id);
        }
        return $this->view->render('edit-form', [
            'action' => $url->relative(),
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
            'button_label' => $action === "delete" ? "label_delete" : "label_save",
            'csrf_token' => $this->csrfProtector->token(),
        ]);
    }

    private function doCreateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return Response::create($this->view->message("fail", "error_unauthorized"));
        }
        $events = $this->eventDataService->readEvents();
        return $this->upsert($request, $events, null);
    }

    private function doUpdateAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return Response::create($this->view->message("fail", "error_unauthorized"));
        }
        $id = $request->get("event_id");
        assert($id !== null); // TODO invalid assertion
        $events = $this->eventDataService->readEvents();
        return $this->upsert($request, $events, array_key_exists($id, $events) ? $id : null);
    }

    /**
     * @param Event[] $events
     * @param string|null $id
     */
    private function upsert(Request $request, array $events, $id): Response
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
            return $this->redirectToOverviewResponse($request);
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
            return $this->redirectToOverviewResponse($request);
        } else {
            return Response::create($this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $maybeEvent, $id, $id !== null ? "create" : "update"));
        }
    }

    private function doDeleteAction(Request $request): Response
    {
        if (!$this->csrfProtector->check($request->post("calendar_token"))) {
            return Response::create($this->view->message("fail", "error_unauthorized"));
        }
        $id = $request->get("event_id");
        assert($id !== null); // TODO invalid assertion
        $events = $this->eventDataService->readEvents();
        if (!array_key_exists($id, $events)) {
            return $this->redirectToOverviewResponse($request);
        }
        $event = $events[$id];
        unset($events[$id]);
        if ($this->eventDataService->writeEvents($events)) {
            return $this->redirectToOverviewResponse($request);
        } else {
            return Response::create($this->view->message("fail", "eventfile_not_saved")
                . $this->renderEditForm($request, $event, $id, "delete"));
        }
    }

    private function redirectToOverviewResponse(Request $request): Response
    {
        if ($request->selected() === '' || $request->selected() === "calendar") {
            $url = $request->url()->page("calendar")->with("admin", "plugin_main")->with("action", "plugin_text");
        } else {
            $url = $request->url()->page($request->selected());
        }
        return Response::redirect($url->absolute());
    }

    private function isValidDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    private function createDefaultEvent(Request $request): Event
    {
        $event = Event::create(
            date("Y-m-d", $request->time()),
            '',
            '',
            '',
            $this->view->plain("event_summary"),
            '',
            '',
            ''
        );
        assert($event !== null);
        return $event;
    }
}
