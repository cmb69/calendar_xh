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

use stdClass;

class CalendarController extends Controller
{
    /**
     * @var string
     */
    private $year;

    /**
     * @var string
     */
    private $month;

    /**
     * @var string
     */
    private $eventpage;

    /**
     * @param string $year
     * @param string $month
     * @param string $eventpage
     */
    public function __construct($year = '', $month = '', $eventpage = '')
    {
        parent::__construct();
        $this->year = $year;
        $this->month = $month;
        $this->eventpage = $eventpage;
    }

    public function defaultAction()
    {
        if ($this->eventpage == '') {
            $this->eventpage = $this->lang['event_page'];
        }
        $eventfile = (new EventDataService)->getFilename();
        if (!is_file($eventfile)) {
            $handle = fopen($eventfile, 'w');
            fclose($handle);
        }

        if ($this->month == '') {
            $this->month = isset($_GET['month']) ? htmlspecialchars($_GET['month']) : date('m');
        }
        if ($this->year == '') {
            $this->year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : date('Y');
        }

        $t                = '';
        $event_day        = '';
        $event_today      = '';
        $event_title      = '';

        $events = (new EventDataService)->readEvents();
        foreach ($events as $entry) {
            if (isset($entry->dateend)) {
                list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $entry->datestart);
                $entry->starttimestamp = mktime(null, null, null, $event_month, $event_date, $event_year);
                list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $entry->dateend);
                $entry->endtimestamp = mktime(null, null, null, $event_month, $event_date, $event_year);
            } else {
                list($entry->day, $entry->month, $entry->year) = explode($this->dpSeperator(), $entry->datestart);
            }
        }

        $theevents = [];
        foreach ($events as $entry) {
            if (isset($entry->dateend)) {
                $txt = "{$entry->event} {$this->lang['event_date_till_date']} {$entry->dateend} {$entry->endtime}";
                if ($this->conf['show_days_between_dates']) {
                    $count = 86400;
                } else {
                    $count = $entry->endtimestamp - $entry->starttimestamp;
                }
                for ($i = $entry->starttimestamp; $i <= $entry->endtimestamp; $i += $count) {
                    $newentry = new stdClass;
                    $newentry->year = date('Y', $i);
                    $newentry->month = date('m', $i);
                    $newentry->day = date('d', $i);
                    $newentry->location = $entry->location;
                    if ($i == $entry->starttimestamp) {
                        $newentry->time = $entry->starttime;
                        $newentry->text = " {$txt}";
                    } else {
                        $newentry->time = '';
                        $newentry->text = $txt;
                    }
                    $theevents[] = $newentry;
                }
            } else {
                $newentry = new stdClass;
                $newentry->year = $entry->year;
                $newentry->month = $entry->month;
                $newentry->day = $entry->day;
                if ($entry->starttime != '') {
                    $newentry->text = " {$entry->event}";
                } else {
                    $newentry->text = $entry->event;
                }
                $newentry->location = $entry->location;
                $newentry->time = $entry->starttime;
                $theevents[] = $newentry;
            }
        }

        $this->month = (isset($this->month)) ? $this->month : date('n');
        $textmonth = date('F', mktime(1, 1, 1, $this->month, 1, $this->year));
    
        $monthnames = explode(',', $this->lang['monthnames_array']);

        $textmonth = $monthnames[$this->month - 1];

        $this->year  = (isset($this->year)) ? $this->year : date('Y');
        $today = (isset($today)) ? $today : date('j');
        $today = ($this->month == date('n') && $this->year == date('Y')) ? $today : 32;
        $days = date('t', mktime(1, 1, 1, $this->month, 1, $this->year));
        $dayone = date('w', mktime(1, 1, 1, $this->month, 1, $this->year));
        $daylast = date('w', mktime(1, 1, 1, $this->month, $days, $this->year));
        $dayarray = explode(',', $this->lang['daynames_array']);

        $rows = [];
        $row = [];
        for ($i = 0; $i <= 6; $i++) {
            if ($this->conf['week_starts_mon']) {
                $j = $i + 1;
            } else {
                $j = $i;
            }
            if ($j == 7) {
                $j = 0;
            }
            $row[] = (object) ['classname' => 'calendar_daynames', 'content' => $dayarray[$j]];
        }
        $rows[] = $row;
        //done printing the top row of days

        $span1 = $this->getSpan1($dayone);
        $span2 = $this->getSpan2($daylast);
        for ($i = 1; $i <= $days; $i++) {
            $dayofweek = date('w', mktime(1, 1, 1, $this->month, $i, $this->year));

            if ($this->conf['week_starts_mon']) {
                $dayofweek = $dayofweek - 1;
            }
            if ($dayofweek == -1) {
                $dayofweek = 6;
            }

            foreach ($theevents as $event) {
                if ($event->year == $this->year
                    && $event->month == $this->month
                    && $event->day == $i
                ) {
                    $event_day = $i;
                    $external_site ='';
                    if ($event_title) {
                        $event_title .= ' | ' . trim($event->time)
                            . strip_tags($event->text);
                    } else {
                        $event_title = trim($event->time) . strip_tags($event->text);
                    }
                }

                if (trim($event->location) == '###'
                    && $event->month == $this->month
                    && $event->day == $i
                ) {
                    $event_day = $i;
                    $age = $this->year - $event->year;
                    if ($age >= 5) {
                        $age .= " {$this->lang['age_plural2_text']}";
                    } elseif ($age >= 2 && $age < 5) {
                        $age .= " {$this->lang['age_plural1_text']}";
                    } else {
                         $age .= " {$this->lang['age_singular_text']}";
                    }

                    $external_site = '';

                    if ($event_title) {
                        $event_title .= "\r\n{$event->text} {$age}";
                    } else {
                        $event_title = "{$event->text} {$age}";
                    }
                }
            }

            $tableday = $i;
            if ($i == 1 || $dayofweek == 0) {
                $row = [];
                while ($span1-- && $i == 1) {
                    $row[] = (object) ['classname' => 'calendar_noday', 'content' => ''];
                }
            }

            if ($today == $event_day) {
                $event_today = $today;
            }

            switch ($i) {
                case $event_today:
                    if ($external_site) {
                        $row[] = (object) ['classname' => 'calendar_today', 'content' => $tableday,
                            'href' => "http://{$external_site}", 'title' => $event_title, 'target' => '_blank'];
                    } else {
                        $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                        $row[] = (object) ['classname' => 'calendar_today', 'content' => $tableday,
                            'href' => $url, 'title' => $event_title, 'target' => '_self'];
                        $event_title = '';
                    }
                    break;
                case $today:
                    $row[] = (object) ['classname' => 'calendar_today', 'content' => $tableday];
                    break;
                case $event_day:
                    if ($external_site) {
                        $row[] = (object) ['classname' => 'calendar_eventday', 'content' => $tableday,
                            'href' => "http://{$external_site}", 'title' => $event_title, 'target' => '_blank'];
                    } else {
                        $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                        $row[] = (object) ['classname' => 'calendar_eventday', 'content' => $tableday,
                            'href' => $url, 'title' => $event_title, 'target' => '_self'];
                        $event_title = '';
                    }
                    break;
                default:
                    if ($dayofweek == $this->conf['week-end_day_1'] || $dayofweek == $this->conf['week-end_day_2']) {
                        $row[] = (object) ['classname' => 'calendar_we', 'content' => $tableday];
                    } else {
                        $row[] = (object) ['classname' => 'calendar_day', 'content' => $tableday];
                    }
            }

            while ($i == $days && $span2--) {
                $row[] = (object) ['classname' => 'calendar_noday', 'content' => ''];
            }
            if ($dayofweek == 6 || $i == $days) {
                $rows[] = $row;
            }
        }

        $view = new View('calendar');
        $view->title = "$textmonth {$this->year}";
        $view->hasPrevNextButtons = $this->conf['prev_next_button'];
        $view->prevUrl = $this->getPrevUrl();
        $view->nextUrl = $this->getNextUrl();
        $view->rows = $rows;
        $view->render();
    }

    /**
     * @return string
     */
    private function getPrevUrl()
    {
        global $sn, $su;

        if ($this->month <= 1) {
            $month_prev = 12;
            $year_prev = $this->year - 1;
        } else {
            $month_prev = $this->month - 1;
            $year_prev = $this->year;
        }
        return "$sn?$su&month=$month_prev&year=$year_prev";
    }

    /**
     * @return string
     */
    private function getNextUrl()
    {
        global $sn, $su;

        if ($this->month >= 12) {
            $month_next = 1;
            $year_next = $this->year + 1;
        } else {
            $month_next = $this->month + 1;
            $year_next = $this->year;
        }
        return "$sn?$su&month=$month_next&year=$year_next";
    }

    /**
     * @return int
     */
    private function getSpan1($dayone)
    {
        if ($this->conf['week_starts_mon']) {
            $span1 = $dayone - 1;
        } else {
            $span1 = $dayone;
        }
        if ($span1 == -1) {
            $span1 = 6;
        }
        return $span1;
    }

    /**
     * @return int
     */
    private function getSpan2($daylast)
    {
        if ($this->conf['week_starts_mon']) {
            $span2 = 7 - $daylast;
        } else {
            $span2 = 6 - $daylast;
        }
        if ($span2 == 7) {
            $span2 = 0;
        }
        return $span2;
    }
}
