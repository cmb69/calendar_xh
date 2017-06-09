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
        global $plugin;

        $plugin = basename(dirname(__DIR__), '/');
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

        $event_year_array           = array();
        $event_month_array          = array();
        $event_yearmonth_array      = array();
        $event_date_array           = array();
        $event_array                = array();
        $event_location_array       = array();
        $event_time_array           = array();

        $t                = '';
        $eventdates       = '';
        $event_date       = '';
        $event_date_start = '';
        $event_end_date   = '';
        $event_year       = '';
        $event_month      = '';
        $event_yearmonth  = '';
        $event            = '';
        $event_day        = '';
        $location         = '';
        $event_today      = '';
        $event_title      = '';
        $event_time       = '';
        $event_end_time   = '';

        if (is_file($eventfile)) {
            $fp = fopen($eventfile, 'r');
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if (strpos($line, ';') !== false) {
                    list($eventdates,$event,$location,,$event_time) = explode(';', $line);
                    if (strpos($eventdates, ',') !== false) {
                        list($event_date_start, $event_end_date, $event_end_time) = explode(',', $eventdates);
                        list($event_date1, $event_month1, $event_year1)
                            = explode($this->dpSeperator(), $event_end_date);
                        list($event_date, $event_month, $event_year) = explode($this->dpSeperator(), $event_date_start);
                        $event_end = mktime(null, null, null, $event_month1, $event_date1, $event_year1);
                        $event_start = mktime(null, null, null, $event_month, $event_date, $event_year);
                    } else {
                         $event_date_start = $eventdates;
                         $event_end_date = '';
                         $event_end_time = '';
                         list($event_date, $event_month, $event_year)
                            = explode($this->dpSeperator(), $event_date_start);
                    }
                }
                if ($event_end_date) {
                    $txt = "{$event} {$this->lang['event_date_till_date']} {$event_end_date} {$event_end_time}";
                    if ($this->conf['show_days_between_dates']) {
                        $count = 86400;
                    } else {
                        $count = $event_end - $event_start;
                    }
                    for ($i=$event_start; $i <= $event_end; $i+=$count) {
                        array_push($event_year_array, date('Y', $i));
                        array_push($event_month_array, date('m', $i));
                        array_push($event_yearmonth_array, date('Y.m', $i));
                        array_push($event_date_array, date('d', $i));
                        array_push($event_location_array, $location);
                        if ($i == $event_start) {
                            array_push($event_time_array, $event_time);
                            array_push($event_array, " {$txt}");
                        } else {
                            array_push($event_time_array, '');
                            array_push($event_array, $txt);
                        }
                    }
                } else {
                    array_push($event_year_array, $event_year);
                    array_push($event_month_array, $event_month);
                    array_push($event_yearmonth_array, $event_yearmonth);
                    array_push($event_date_array, $event_date);
                    if ($event_time != '') {
                        array_push($event_array, " {$event}");
                    } else {
                        array_push($event_array, $event);
                    }
                    array_push($event_location_array, $location);
                    array_push($event_time_array, $event_time);
                }
            }
            fclose($fp);
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

        $t .= "<table class=\"calendar_main\">\n<tr>\n";
        $t .= "<td colspan=\"7\">\n";

        if ($this->conf['prev_next_button']) {
            $prevUrl = XH_hsc($this->getPrevUrl());
            $nextUrl = XH_hsc($this->getNextUrl());
            $t .= "<div class=\"calendar_monthyear\">\n<a href=\"$prevUrl\" rel=\"nofollow\" title=\""
                . $this->lang['prev_button_text']
                . "\">&lt;&lt;</a>&nbsp;$textmonth {$this->year}&nbsp;<a href=\"$nextUrl\" rel=\"nofollow\" title=\""
                . $this->lang['next_button_text'] . "\">&gt;&gt;</a></div>\n";
        } else {
            $t .= "<div class=\"calendar_monthyear\">$textmonth {$this->year}</div>\n";
        }

        $t .= "</td>\n";
        $t .= "</tr>\n<tr>\n";

        for ($i = 0; $i <= 6; $i++) {
            if ($this->conf['week_starts_mon']) {
                $j = $i + 1;
            } else {
                $j = $i;
            }
            if ($j == 7) {
                $j = 0;
            }

            $t .= "<td class=\"calendar_daynames\">$dayarray[$j]</td>\n";
        }
        $t .= "</tr>\n";
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

            foreach (array_keys($event_year_array) as $keys) {
                if ($event_year_array[$keys] == $this->year
                    && $event_month_array[$keys] == $this->month
                    && $event_date_array[$keys] == $i
                ) {
                    $event_day = $i;
                    $external_site ='';
                    if ($event_title) {
                        $event_title .= ' &nbsp;|&nbsp; ' . trim($event_time_array[$keys])
                            . strip_tags($event_array[$keys]);
                    } else {
                        $event_title = trim($event_time_array[$keys]) . strip_tags($event_array[$keys]);
                    }
                }

                if (trim($event_location_array[$keys]) == '###'
                    && $event_month_array[$keys] == $this->month
                    && $event_date_array[$keys] == $i
                ) {
                    $event_day = $i;
                    $age = $this->year - $event_year_array[$keys];
                    if ($age >= 5) {
                        $age .= " {$this->lang['age_plural2_text']}";
                    } elseif ($age >= 2 && $age < 5) {
                        $age .= " {$this->lang['age_plural1_text']}";
                    } else {
                         $age .= " {$this->lang['age_singular_text']}";
                    }

                    $external_site = '';

                    if ($event_title) {
                        $event_title .= "\r\n{$event_array[$keys]} {$age}";
                    } else {
                        $event_title = "{$event_array[$keys]} {$age}";
                    }
                }
            }

            $tableday = $i;
            if ($i == 1 || $dayofweek == 0) {
                $t .= "<tr>\n";
                if ($span1 > 0 && $i == 1) {
                    $t .= "<td class=\"calendar_noday\" colspan=\"$span1\">&nbsp;</td>\n";
                }
            }

            if ($today == $event_day) {
                $event_today = $today;
            }

            switch ($i) {
                case $event_today:
                    if ($external_site) {
                        $t .= "<td class=\"calendar_today\"><a href=\"http://{$external_site}\""
                            . " target=\"_blank\" title=\"$event_title\">$tableday</a></td>\n";
                    } else {
                        $url = "?{$this->eventpage}&amp;month={$this->month}&amp;year={$this->year}";
                        $t .= "<td class=\"calendar_today\"><a href=\"$url\" title=\"$event_title\">"
                            . "$tableday</a></td>\n";
                        $event_title = '';
                    }
                    break;
                case $today:
                    $t .= "<td class=\"calendar_today\">$tableday</td>\n";
                    break;
                case $event_day:
                    if ($external_site) {
                        $t .= "<td class=\"calendar_eventday\"><a href=\"http://{$external_site}\""
                            . " target=\"_blank\" title=\"$event_title\">$tableday</a></td>\n";
                    } else {
                        $url = "?{$this->eventpage}&amp;month={$this->month}&amp;year={$this->year}";
                        $t .= "<td class=\"calendar_eventday\"><a href=\"$url\" title=\"$event_title\">"
                            . "$tableday</a></td>\n";
                        $event_title = '';
                    }
                    break;
                default:
                    if ($dayofweek == $this->conf['week-end_day_1'] || $dayofweek == $this->conf['week-end_day_2']) {
                        $t .= "<td class=\"calendar_we\">$tableday</td>\n";
                    } else {
                        $t .= "<td class=\"calendar_day\">$tableday</td>\n";
                    }
            }

            if ($i == $days && $span2 > 0) {
                $t .= "<td class=\"calendar_noday\" colspan=\"$span2\">&nbsp;</td>\n";
            }
            if ($dayofweek == 6 || $i == $days) {
                $t .= "</tr>\n";
            }
        }
        $t .= "</table>\n";

        echo $t;
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
