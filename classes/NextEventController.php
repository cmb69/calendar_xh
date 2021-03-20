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

class NextEventController extends Controller
{
    /**
     * @return void
     */
    public function defaultAction()
    {
        $nextevent = null;

        $endevents = [];
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        foreach ($events as $event) {
            if (isset($event->dateend)) {
                $endevent = clone $event;
                $event->text = $this->lang['event_date_till_date'] . " " . tag('br')
                    . $event->dateend . " " . $event->endtime;
                list($event_year, $event_month, $event_date) = explode('-', $event->datestart);
                $event->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
                $endevent->text = $this->lang['event_started'] . tag('br')
                        . $event->datestart . " " . $event->starttime;
                list($event_year, $event_month, $event_date) = explode('-', $event->dateend);
                $endevent->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
                $endevents[] = $endevent;
            } elseif (trim($event->location) == '###') {
                $event->text = '';
                list($event_year, $event_month, $event_date) = explode('-', $event->datestart);
                $event->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
            }
        }
        $events = array_merge($events, $endevents);
        usort(
            $events,
            /**
             * @param stdClass $a
             * @param stdClass $b
             * @return int
             */
            function ($a, $b) {
                return $a->timestamp - $b->timestamp;
            }
        );

        $today = strtotime('now');

        foreach ($events as $event) {
            if ($event->timestamp > $today) {
                $nextevent = $event;
                break;
            }
        }
        $view = new View('nextevent');
        if (isset($nextevent)) {
            $view->event = $nextevent;
            $date = date($this->lang['event_date_representation_in_next_event_marquee'], $nextevent->timestamp);
            if (date('H:i', $nextevent->timestamp) != "00:00") {
                $date.= ' — ' . date('H:i', $nextevent->timestamp);
            }
            $view->date = $date;
        }
        $view->render();
    }
}
