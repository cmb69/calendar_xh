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

class NextEventController extends Controller
{
    public function defaultAction()
    {
        $t = '';
        $nextevent = null;

        $endevents = [];
        $events = (new EventDataService)->readEvents();
        foreach ($events as $event) {
            if (isset($event->dateend)) {
                $endevent = clone $event;
                $event->text = $this->lang['event_date_till_date'] . " " . tag('br')
                    . $event->dateend . " " . $event->endtime;
                list($event_date, $event_month, $event_year) = explode($this->dpSeparator(), $event->datestart);
                $event->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
                $endevent->text = $this->lang['event_event'] . " " . $this->lang['event_start'] . ":" . tag('br')
                        . $event->datestart . " " . $event->starttime;
                list($event_date, $event_month, $event_year) = explode($this->dpSeparator(), $event->dateend);
                $endevent->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
                $endevents[] = $endevent;
            } else {
                $event->text = '';
                list($event_date, $event_month, $event_year) = explode($this->dpSeparator(), $event->datestart);
                $event->timestamp = strtotime("$event_month/$event_date/$event_year {$event->starttime}");
            }
        }
        $events = array_merge($events, $endevents);
        usort($events, function ($a, $b) {
            return $a->timestamp - $b->timestamp;
        });

        $today = strtotime('now');

        foreach ($events as $event) {
            if ($event->timestamp > $today) {
                $nextevent = $event;
                break;
            }
        }
        if (isset($nextevent)) {
            $t.= "<div class=\"nextevent_date\">"
                . strftime($this->lang['event_date_representation_in_next_event_marquee'], $nextevent->timestamp);
            if (strftime('%H:%M', $nextevent->timestamp) != "00:00") {
                $t.= ' — ' . strftime('%H:%M', $nextevent->timestamp);
            }
            $t.= "</div>\n";
            $t.= "<marquee direction=\"up\" scrolldelay=\"100\" scrollamount=\"1\">"
                . "<div class=\"nextevent_event\">{$nextevent->event}</div>\n";
            $t.= "<div class=\"nextevent_date\">{$nextevent->text}</div>\n";
            $t.= "<div class=\"nextevent_location\">{$nextevent->location}</div>\n</marquee>\n";
        } elseif ($this->lang['notice_no_next_event_sceduled']) {
            // if no next event - as suggested by oldnema
            $t.= "<div class=\"nextevent_date\">" . tag('br') . $this->lang['notice_no_next_event_sceduled'] . "</div>";
        }

        echo $t;
    }
}
