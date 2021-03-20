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

use stdClass;
use Fa\RequireCommand as FaRequireCommand;

class EditEventsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        (new FaRequireCommand)->execute();
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
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
            'datestart', 'starttime', 'dateend', 'endtime', 'event', 'location', 'linkadr', 'linktxt'
        );
        $post = [];
        foreach ($varnames as $var) {
            $post[$var] = isset($_POST[$var]) ? $_POST[$var] : [];
        }
        $events = [];
        foreach (array_keys($post['datestart']) as $i) {
            if (!isset($_POST['delete'][$i])) {
                $entry = (object) array_combine($varnames, array_column($post, $i));
                $this->fixPostedEvent($entry);
                $events[] = $entry;
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
            usort($events, array($this, 'dateSort'));
            if ((new EventDataService($this->dpSeparator()))->writeEvents($events)) {
                echo XH_message('success', $this->lang['eventfile_saved']);
            } else {
                echo XH_message('fail', $this->lang['eventfile_not_saved']);
            }
        }

        echo $this->eventForm($events);
    }

    /**
     * @return void
     */
    private function fixPostedEvent(stdClass $event)
    {
        if (!$this->isValidDate($event->datestart)) {
            $event->datestart = '';
        }
        if (!$this->isValidDate($event->dateend)) {
            $event->dateend = '';
        }

        //Birthday should never have an enddate
        if ($event->location == '###') {
            $event->dateend = '';
        }
    }

    /**
     * @param stdClass[] $events
     * @return string
     */
    private function eventForm($events)
    {
        $view = new View('event-form');
        $view->showEventTime = (bool) $this->conf['show_event_time'];
        $view->showEventLocation = (bool) $this->conf['show_event_location'];
        $view->showEventLink = (bool) $this->conf['show_event_link'];
        $view->events = $events;
        return (string) $view;
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
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function dateSort(stdClass $a, stdClass $b)
    {
        $a_i = "{$a->datestart}T{$a->starttime}";
        $b_i = "{$b->datestart}T{$b->starttime}";
        if ($a_i == $b_i) {
            return 0;
        }
        return ($a_i < $b_i) ? -1 : 1;
    }

    /**
     * @return stdClass
     */
    private function createDefaultEvent()
    {
        return (object) array(
            'datestart'   => date('Y-m-d'),
            'starttime'   => '',
            'dateend'     => '',
            'endtime'     => '',
            'event'       => $this->lang['event_event'],
            'location'    => '',
            'linkadr'     => '',
            'linktxt'     => ''
        );
    }
}
