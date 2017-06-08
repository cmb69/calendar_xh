<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
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
        global $plugin;

        $plugin = basename(dirname(__DIR__), '/');

        $event_date_array = array();
        $event_array = array();
        $event_location_array = array();
        $event_stsl_array = array();

        $t = '';
        $remember_event = '';

        $eventfile = (new EventDataService)->getFilename();

        if (is_file($eventfile)) {
            $fp = fopen($eventfile, "r");
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                $txt = '';
                list($eventdates, $event, $location, , $eventtime) = explode(';', $line);
                list($event_date_start, $event_end_date, $event_end_time) = explode(',', $eventdates);

                if ($event_end_date) {
                    $txt = $this->lang['event_date_till_date'] . " " . tag('br') . $event_end_date . " " . $event_end_time;
                    list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $event_date_start);
                    array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
                    array_push($event_array, $event);
                    array_push($event_stsl_array, $txt);
                    array_push($event_location_array, $location);

                    $txt = $this->lang['event_event'] . " " . $this->lang['event_start'] . ":" . tag('br') . $event_date_start . " " . $eventtime;
                    list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $event_end_date);
                    array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $event_end_time"));
                    array_push($event_array, $event);
                    array_push($event_stsl_array, $txt);
                    array_push($event_location_array, $location);
                } else {
                    list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $event_date_start);
                    array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
                    array_push($event_array, $event);
                    array_push($event_stsl_array, $txt);
                    array_push($event_location_array, $location);
                }
            }
            fclose($fp);
        }

        asort($event_date_array);

        $today=strtotime('now');

        foreach ($event_date_array as $event_date) {
            if ($event_date > $today) {
                if ($remember_event == '') {
                    $remember_event = $event_date;
                }
            }
        }
        if ($remember_event > $today) {
            $i = array_search($remember_event, $event_date_array);
            $t.= "<div class=\"nextevent_date\">" . strftime($this->lang['event_date_representation_in_next_event_marquee'], $event_date_array[$i]);
            if (strftime('%H:%M', $event_date_array[$i]) != "00:00") {
                $t.= ' — ' . strftime('%H:%M', $event_date_array[$i]);
            }
            $t.= "</div>\n";
            $t.= "<marquee direction=\"up\" scrolldelay=\"100\" scrollamount=\"1\"><div class=\"nextevent_event\">{$event_array[$i]}</div>\n";
            $t.= "<div class=\"nextevent_date\">{$event_stsl_array[$i]}</div>\n";
            $t.= "<div class=\"nextevent_location\">{$event_location_array[$i]}</div>\n</marquee>\n";
        } elseif ($this->lang['notice_no_next_event_sceduled']) {
            // if no next event - as suggested by oldnema
            $t.= "<div class=\"nextevent_date\">" . tag('br') . $this->lang['notice_no_next_event_sceduled'] . "</div>";
        }

        echo $t;
    }
}
