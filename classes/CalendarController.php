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
        $this->determineYearAndMonth();
        $calendar = new Calendar((bool) $this->conf['week_starts_mon']);
        $rows = [];
        $rows[] = $this->getDaynamesRow();
        foreach ($calendar->getMonthMatrix($this->year, $this->month) as $columns) {
            $rows[] = $this->getRowData($columns);
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
     * @return void
     */
    private function determineYearAndMonth()
    {
        if ($this->month == '') {
            $this->month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');
        }
        if ($this->year == '') {
            $this->year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        }
    }

    /**
     * @param (int|null)[] $columns
     * @return stdClass[]
     */
    private function getRowData(array $columns)
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        $today = ($this->month == date('n') && $this->year == date('Y')) ? date('j') : 32;
        $row = [];
        foreach ($columns as $day) {
            if ($day === null) {
                $row[] = (object) ['classname' => 'calendar_noday', 'content' => ''];
                continue;
            }
            $dayEvents = $this->filterEventsByDay($events, $day);
            if ($day == $today && !empty($dayEvents)) {
                $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                $row[] = (object) ['classname' => 'calendar_today', 'content' => $day,
                    'href' => $url, 'title' => $this->getEventsTitle($dayEvents)];
            } elseif ($day == $today) {
                $row[] = (object) ['classname' => 'calendar_today', 'content' => $day];
            } elseif (!empty($dayEvents)) {
                $url = "?{$this->eventpage}&month={$this->month}&year={$this->year}";
                $row[] = (object) ['classname' => 'calendar_eventday', 'content' => $day,
                    'href' => $url, 'title' => $this->getEventsTitle($dayEvents)];
            } elseif ($this->isWeekEnd(count($row))) {
                $row[] = (object) ['classname' => 'calendar_we', 'content' => $day];
            } else {
                $row[] = (object) ['classname' => 'calendar_day', 'content' => $day];
            }
        }
        return $row;
    }

    /**
     * @param Event[] $events
     * @param int $day
     * @return Event[]
     */
    private function filterEventsByDay(array $events, $day)
    {
        $result = [];
        foreach ($events as $event) {
            if ($this->isEventOn($event, $day)) {
                $result[] = $event;
            } elseif ($this->isBirthdayOn($event, $day)) {
                $result[] = $event;
            }
        }
        return $result;
    }

    /**
     * @param int $day
     * @return bool
     */
    private function isEventOn(Event $event, $day)
    {
        if ($event->isBirthday()) {
            return false;
        }
        $today = mktime(0, 0, 0, $this->month, $day, $this->year);
        $endDate = $event->getDateEnd();
        if ($endDate === null) {
            return $event->getStartTimestamp() === $today;
        }
        if ($this->conf['show_days_between_dates']) {
            return $today >= $event->getStartTimestamp() && $today <= $event->getEndTimestamp();
        }
        return $today === $event->getStartTimestamp() && $today === $event->getEndTimestamp();
    }

    /**
     * @param int $day
     * @return bool
     */
    private function isBirthdayOn(Event $event, $day)
    {
        $date = $event->getStart();
        return $event->isBirthday() && $date->getMonth() == $this->month && $date->getDay() == $day;
    }

    /**
     * @param Event[] $events
     * @return string
     */
    private function getEventsTitle(array $events)
    {
        $titles = [];
        foreach ($events as $event) {
            if ($event->getDateEnd() !== null) {
                $text = sprintf(
                    "%s %s %s %s",
                    $event->event,
                    $this->lang['event_date_till_date'],
                    (string) $event->getDateEnd(),
                    (string) $event->getEndTime()
                );
            } else {
                $text = $event->event;
            }
            if (!$event->isBirthday()) {
                $titles[] = trim($event->getStartTime()) . " " . strip_tags($text);
            } else {
                $age = $this->year - $event->getStart()->getYear();
                $age = sprintf($this->lang['age' . XH_numberSuffix($age)], $age);
                $titles[] = "{$text} {$age}";
            }
        }
        return implode(" | ", $titles);
    }

    /**
     * @param int $dayOfWeek
     * @return bool
     */
    private function isWeekEnd($dayOfWeek)
    {
        return $dayOfWeek === (int) $this->conf['week-end_day_1']
            || $dayOfWeek === (int) $this->conf['week-end_day_2'];
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
}
