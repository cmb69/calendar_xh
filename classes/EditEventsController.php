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

use Fa\RequireCommand as FaRequireCommand;

class EditEventsController extends Controller
{
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
        (new FaRequireCommand)->execute();
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        $events = $this->eventDataService->readEvents();
        echo $this->eventForm($events);
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $deleted = false;
        $added = false;

        $varnames = array(
            'datestart', 'dateend', 'starttime', 'endtime', 'event', 'linkadr', 'linktxt', 'location'
        );
        $post = [];
        foreach ($varnames as $var) {
            assert(!isset($_POST[$var]) || is_array($_POST[$var]));
            $post[$var] = isset($_POST[$var]) ? $_POST[$var] : [];
        }
        $events = [];
        foreach (array_keys($post['datestart']) as $i) {
            if (!isset($_POST['delete'][$i])) {
                if (!$this->isValidDate($post['datestart'][$i])) {
                    $post['datestart'][$i] = '';
                }
                if (!$this->isValidDate($post['dateend'][$i])) {
                    $post['dateend'][$i] = '';
                }
                /** @var string[] $args */
                $args = array_column($post, $i);
                $maybeEvent = Event::create(...$args);
                if ($maybeEvent !== null) {
                    $events[] = $maybeEvent;
                }
            } else {
                $deleted = true;
            }
        }

        if (isset($_POST['add'])) {
            $events[] = $this->createDefaultEvent();
            $added = true;
        }

        if (!$deleted && !$added) {
            // sorting new event inputs, idea of manu, forum-message
            usort($events, /** @return int */ function (Event $a, Event $b) {
                return $a->start->compare($b->start);
            });
            /** @var Event[] $events */
            $oldevents = $this->eventDataService->readEvents();
            if ($_POST['calendar_hash'] !== '' && $_POST['calendar_hash'] !== sha1(serialize($oldevents))) {
                echo XH_message('warning', $this->lang['message_changed']),
                    $this->eventForm($events, true);
                return;
            }
            if ($this->eventDataService->writeEvents($events)) {
                echo XH_message('success', $this->lang['eventfile_saved']);
            } else {
                echo XH_message('fail', $this->lang['eventfile_not_saved']);
            }
        }

        echo $this->eventForm($events);
    }

    /**
     * @param Event[] $events
     * @param bool $force
     * @return string
     */
    private function eventForm($events, $force = false)
    {
        return $this->view->getString('event-form', [
            'showEventTime' => (bool) $this->conf['show_event_time'],
            'showEventLocation' => (bool) $this->conf['show_event_location'],
            'showEventLink' => (bool) $this->conf['show_event_link'],
            'events' => $events,
            'hash' => !$force ? sha1(serialize($events)) : '',
        ]);
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
            $this->lang['event_event'],
            '',
            '',
            ''
        );
        assert($event !== null);
        return $event;
    }
}
