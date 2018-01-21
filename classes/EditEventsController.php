<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2018 Christoph M. Becker
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

    public function defaultAction()
    {
        $events = (new EventDataService)->readEvents();
        echo $this->eventForm($events);
    }

    public function saveAction()
    {
        $deleted = false;
        $added = false;

        $varnames = array(
            'datestart', 'starttime', 'dateend', 'endtime', 'event', 'location', 'linkadr', 'linktxt'
        );
        $post = [];
        foreach ($varnames as $var) {
            $$var = isset($_POST[$var]) ? $_POST[$var] : [];
            $post[$var] = $$var;
        }
        $newevent = [];
        foreach (array_keys($datestart) as $i) {
            if (!isset($_POST['delete'][$i])) {
                $entry = (object) array_combine($varnames, array_column($post, $i));
                $this->fixPostedEvent($entry);
                $newevent[] = $entry;
            } else {
                $deleted = true;
            }
        }

        if (isset($_POST['add'])) {
            $newevent[] = $this->createDefaultEvent();
            $added = true;
        }

        $o = '';
        if (!$deleted && !$added) {
            // sorting new event inputs, idea of manu, forum-message
            usort($newevent, array($this, 'dateSort'));
            if (!(new EventDataService)->writeEvents($newevent)) {
                $o .= XH_message('fail', $this->lang['eventfile_not_saved']);
            } else {
                $o .= XH_message('success', $this->lang['eventfile_saved']);
            }
        }

        $o .= $this->eventForm($newevent);
        echo $o;
    }

    private function fixPostedEvent(stdClass $event)
    {
        if ($this->isValidDate($event->datestart)) {
            list($year, $month, $day) = explode('-', $event->datestart);
            $event->datestart = $day . $this->dpSeparator() . $month . $this->dpSeparator() . $year;
        } else {
            $event->datestart = '';
        }
        if ($this->isValidDate($event->dateend)) {
            list($year, $month, $day) = explode('-', $event->dateend);
            $event->dateend = $day . $this->dpSeparator() . $month . $this->dpSeparator() . $year;
        } else {
            $event->dateend = '';
        }

        //Birthday should never have an enddate
        if ($event->location == '###') {
            $event->dateend = '';
        }
    }

    /**
     * @param stdClass[] $events
     */
    private function eventForm($events)
    {
        $view = new View('event-form');
        $view->showEventTime = (bool) $this->conf['show_event_time'];
        $view->showEventLocation = (bool) $this->conf['show_event_location'];
        $view->showEventLink = (bool) $this->conf['show_event_link'];
        foreach ($events as $event) {
            if ($event->datestart) {
                list($day, $month, $year) = explode($this->dpSeparator(), $event->datestart);
                $event->datestart = "$year-$month-$day";
            }
            if ($event->dateend) {
                list($day, $month, $year) = explode($this->dpSeparator(), $event->dateend);
                $event->dateend = "$year-$month-$day";
            }
        }
        $view->events = $events;
        return (string) $view;
    }

    /**
     * Checking the date format. Some impossible dates can be given, but don't hurt.
     */
    private function isValidDate($date)
    {
        return preg_match('/^\d{4}-\d\d-(?:\d\d|\?{1-2}|\-{1-2})$/', $date);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function dateSort(stdClass $a, stdClass $b)
    {
        $pattern = '!(.*)\\' . $this->dpSeparator() . '(.*)\\' . $this->dpSeparator() . '(.*)!';
        $replace = '\3\2\1';
        $a_i = preg_replace($pattern, $replace, $a->datestart) . $a->starttime;
        $b_i = preg_replace($pattern, $replace, $b->datestart) . $b->starttime;
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
            'datestart'   => date('d') . $this->dpSeparator() . date('m') . $this->dpSeparator() . date('Y'),
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
