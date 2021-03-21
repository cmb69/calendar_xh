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

class CalendarController extends Controller
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var string
     */
    private $eventpage;

    /**
     * @param int $year
     * @param int $month
     * @param string $eventpage
     */
    public function __construct($year = 0, $month = 0, $eventpage = '')
    {
        parent::__construct();
        $this->year = $year;
        $this->month = $month;
        $this->eventpage = $eventpage;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $pth, $bjs;

        $bjs .= '<script src="' . $pth['folder']['plugins'] . 'calendar/calendar.min.js"></script>';
        if ($this->eventpage == '') {
            $this->eventpage = $this->lang['event_page'];
        }

        if ($this->month == '') {
            $this->month = isset($_GET['month']) ? $_GET['month'] : date('m');
        }
        if ($this->year == '') {
            $this->year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        }

        $event_day        = '';
        $event_today      = '';
        $event_titles = [];


        $events = $this->fetchEvents();

        $today = ($this->month == date('n') && $this->year == date('Y')) ? date('j') : 32;
        $days = (int) date('t', mktime(1, 1, 1, $this->month, 1, $this->year));
        $dayone = (int) date('w', mktime(1, 1, 1, $this->month, 1, $this->year));
        $daylast = (int) date('w', mktime(1, 1, 1, $this->month, $days, $this->year));

        $rows = [];
        $rows[] = $this->getDaynamesRow();
        //done printing the top row of days

        $span1 = $this->getSpan1($dayone);
        $span2 = $this->getSpan2($daylast);
        $row = [];
        for ($i = 1; $i <= $days; $i++) {
            $dayofweek = $this->getDayOfWeek($i);

            foreach ($events as $event) {
                if ($this->isEventOn($event, $i)) {
                    $event_day = $i;
                    assert($event->time !== null);
                    assert($event->text !== null);
                    $event_titles[] = trim($event->time) . strip_tags($event->text);
                }

                if ($this->isBirthdayOn($event, $i)) {
                    $event_day = $i;
                    $age = $this->year - $event->year;
                    $age = sprintf($this->lang['age' . XH_numberSuffix($age)], $age);
                    $event_titles[] = "{$event->text} {$age}";
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
                    $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                    $row[] = (object) ['classname' => 'calendar_today', 'content' => $tableday,
                        'href' => $url, 'title' => implode(' | ', $event_titles)];
                    $event_titles = [];
                    break;
                case $today:
                    $row[] = (object) ['classname' => 'calendar_today', 'content' => $tableday];
                    break;
                case $event_day:
                    $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                    $row[] = (object) ['classname' => 'calendar_eventday', 'content' => $tableday,
                        'href' => $url, 'title' => implode(' | ', $event_titles)];
                    $event_titles = [];
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
        $view->data = [
            'caption' => $this->formatMonthYear($this->month, $this->year),
            'hasPrevNextButtons' => $this->conf['prev_next_button'],
            'prevUrl' => $this->getPrevUrl(),
            'nextUrl' => $this->getNextUrl(),
            'rows' => $rows,
        ];
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('X-Location: ' . CMSIMPLE_URL . "?{$_SERVER['QUERY_STRING']}");
            $view->render();
            exit;
        } else {
            echo '<div class="calendar_calendar">';
            $view->render();
            echo '</div>';
        }
    }

    /**
     * @return Event[]
     */
    private function fetchEvents()
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        $newevents = [];
        foreach ($events as $event) {
            if (isset($event->dateend)) {
                $txt = "{$event->event} {$this->lang['event_date_till_date']} {$event->dateend} {$event->endtime}";
                if ($this->conf['show_days_between_dates']) {
                    $count = 86400;
                } else {
                    $count = $event->getEndTimestamp() - $event->getStartTimestamp();
                }
                for ($i = $event->getStartTimestamp(); $i <= $event->getEndTimestamp(); $i += $count) {
                    $newevent = new Event('', '', '', '', '', '', '', $event->location);
                    $newevent->year = date('Y', $i);
                    $newevent->month = date('m', $i);
                    $newevent->day = date('d', $i);
                    if ($i == $event->getStartTimestamp()) {
                        $newevent->time = $event->starttime;
                        $newevent->text = " {$txt}";
                    } else {
                        $newevent->time = '';
                        $newevent->text = $txt;
                    }
                    $newevents[] = $newevent;
                }
            } else {
                list($event->year, $event->month, $event->day) = explode('-', $event->datestart);
                $newevent = new Event('', '', '', '', '', '', '', $event->location);
                $newevent->year = $event->year;
                $newevent->month = $event->month;
                $newevent->day = $event->day;
                if ($event->starttime != '') {
                    $newevent->text = " {$event->event}";
                } else {
                    $newevent->text = $event->event;
                }
                $newevent->time = $event->starttime;
                $newevents[] = $newevent;
            }
        }
        return $newevents;
    }

    /**
     * @param int $i
     * @return int
     */
    private function getDayOfWeek($i)
    {
        $dayofweek = (int) date('w', mktime(1, 1, 1, $this->month, $i, $this->year));
        if ($this->conf['week_starts_mon']) {
            $dayofweek = $dayofweek - 1;
        }
        if ($dayofweek == -1) {
            $dayofweek = 6;
        }
        return $dayofweek;
    }

    /**
     * @param int $day
     * @return bool
     */
    private function isEventOn(Event $event, $day)
    {
        return !$event->isBirthday() && $event->year == $this->year && $event->month == $this->month && $event->day == $day;
    }

    /**
     * @param int $day
     * @return bool
     */
    private function isBirthdayOn(Event $event, $day)
    {
        return $event->isBirthday() && $event->month == $this->month && $event->day == $day;
    }

    /**
     * @return stdClass[]
     */
    private function getDaynamesRow()
    {
        $dayarray = explode(',', $this->lang['daynames_array']);
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
        return $row;
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
     * @param int $dayone
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
     * @param int $daylast
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
