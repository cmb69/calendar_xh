<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2021 Christoph M. Becker
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

class EditEventsController
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var LocalDateTime */
    private $now;

    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     */
    public function __construct(
        array $conf,
        array $lang,
        LocalDateTime $now,
        EventDataService $eventDataService,
        View $view
    ) {
        $this->conf = $conf;
        $this->lang = $lang;
        $this->now = $now;
        $this->eventDataService = $eventDataService;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $pth, $hjs;

        $hjs .= "<script type=\"module\" src=\"{$pth['folder']['plugins']}calendar/calendar.min.js\"></script>";
        $events = $this->eventDataService->readEvents();
        echo $this->eventForm($events);
    }

    /**
     * @return void
     */
    public function createAction()
    {
        $event = $this->createDefaultEvent();
        $this->renderEditForm($event, null, "create");
    }

    /**
     * @return void
     */
    public function updateAction()
    {
        $events = $this->eventDataService->readEvents();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        if (!array_key_exists($_GET['event_id'], $events)) {
            $this->redirectToOverview();
        }
        $id = $_GET['event_id'];
        $event = $events[$id];
        $this->renderEditForm($event, $id, "update");
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $events = $this->eventDataService->readEvents();
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        if (!array_key_exists($_GET['event_id'], $events)) {
            $this->redirectToOverview();
        }
        $id = $_GET['event_id'];
        $event = $events[$id];
        $this->renderEditForm($event, $id, "delete");
    }

    /**
     * @param string|null $id
     * @param string $action
     * @return void
     */
    private function renderEditForm(Event $event, $id, $action)
    {
        global $su;

        $url = "?$su&admin=plugin_main&action=$action";
        if ($id !== null) {
            $url .= "&event_id=$id";
        }
        $label = $action === "delete" ? "label_delete" : "label_save";
        $this->view->render('edit-form', [
            'action' => $url,
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'event' => $event,
            'button_label' => $this->lang[$label],
        ]);
    }

    /**
     * @return void
     */
    public function doCreateAction()
    {
        $events = $this->eventDataService->readEvents();
        $this->upsert($events, null);
    }

    /**
     * @return void
     */
    public function doUpdateAction()
    {
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        $id = $_GET['event_id'];
        $events = $this->eventDataService->readEvents();
        $this->upsert($events, array_key_exists($id, $events) ? $id : null);
    }

    /**
     * @param Event[] $events
     * @param string|null $id
     * @return void
     */
    private function upsert(array $events, $id)
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
            $this->redirectToOverview();
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
            $this->redirectToOverview();
        } else {
            echo XH_message('fail', $this->lang['eventfile_not_saved']);
            $this->renderEditForm($maybeEvent, $id, $id !== null ? "create" : "update");
        }
    }

    /**
     * @return void
     */
    public function doDeleteAction()
    {
        assert(isset($_GET['event_id']) && is_string($_GET['event_id']));
        $id = $_GET['event_id'];
        $events = $this->eventDataService->readEvents();
        if (!array_key_exists($id, $events)) {
            $this->redirectToOverview();
        }
        $event = $events[$id];
        unset($events[$id]);
        if ($this->eventDataService->writeEvents($events)) {
            $this->redirectToOverview();
        } else {
            echo XH_message('fail', $this->lang['eventfile_not_saved']);
            $this->renderEditForm($event, $id, "delete");
        }
    }

    /**
     * @param Event[] $events
     * @param bool $force
     * @return string
     */
    private function eventForm($events, $force = false)
    {
        global $su;

        return $this->view->getString('event-table', [
            'selected' => $su ? $su : 'calendar',
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'events' => $events,
            'hash' => !$force ? sha1(serialize($events)) : '',
        ]);
    }

    /**
     * @return no-return
     */
    private function redirectToOverview()
    {
        global $su;

        if ($su === '' || $su === 'calendar') {
            $url = CMSIMPLE_URL . "?calendar&admin=plugin_main&action=plugin_text";
        } else {
            $url = CMSIMPLE_URL . "?$su";
        }
        header("Location: $url");
        exit;
    }

    /**
     * Checking the date format. Some impossible dates can be given, but don't hurt.
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate($date)
    {
        return (bool) preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    /**
     * @return Event
     */
    private function createDefaultEvent()
    {
        $event = Event::create(
            $this->now->getDate(),
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
