<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2023 Christoph M. Becker
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

class CalendarController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var LocalDateTime */
    private $now;

    /** @var EventDataService */
    private $eventDataService;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    /** @var string */
    private $url;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     */
    public function __construct(
        string $pluginFolder,
        array $conf,
        array $lang,
        LocalDateTime $now,
        EventDataService $eventDataService,
        DateTimeFormatter $dateTimeFormatter,
        View $view,
        string $url
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->lang = $lang;
        $this->now = $now;
        $this->eventDataService = $eventDataService;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->view = $view;
        $this->url = $url;
    }

    public function defaultAction(int $year, int $month, string $eventpage): Response
    {
        if ($eventpage == '') {
            $eventpage = $this->lang['event_page'];
        }
        $this->determineYearAndMonth($year, $month);
        $calendar = new Calendar((bool) $this->conf['week_starts_mon']);
        $rows = [];
        foreach ($calendar->getMonthMatrix($year, $month) as $columns) {
            $rows[] = $this->getRowData($columns, $year, $month, $eventpage);
        }
        $data = [
            'caption' => $this->dateTimeFormatter->formatMonthYear($month, $year),
            'hasPrevNextButtons' => $this->conf['prev_next_button'],
            'prevUrl' => $this->getPrevUrl($year, $month),
            'nextUrl' => $this->getNextUrl($year, $month),
            'headRow' => $this->getDaynamesRow(),
            'rows' => $rows,
            'jsUrl' => "{$this->pluginFolder}js/calendar.min.js",
        ];
        if (isset($_GET['calendar_ajax'])) {
            return Response::createAjax($this->view->render('calendar', $data));
        }
        $output = '<div class="calendar_calendar">'
            . $this->view->render('calendar', $data)
            . '</div>';
        return Response::create($output);
    }

    /** @return void */
    private function determineYearAndMonth(int &$year, int &$month)
    {
        if ($month === 0) {
            $month = isset($_GET['month']) ? max(1, min(12, (int) $_GET['month'])) : $this->now->month;
        }
        if ($year === 0) {
            $year = isset($_GET['year']) ? max(1, min(9000, (int) $_GET['year'])) : $this->now->year;
        }
    }

    /**
     * @param (int|null)[] $columns
     * @return stdClass[]
     */
    private function getRowData(array $columns, int $year, int $month, string $eventpage): array
    {
        $events = $this->eventDataService->readEvents();
        $today = ($month === $this->now->month && $year === $this->now->year)
            ? $this->now->day
            : 32;
        $row = [];
        foreach ($columns as $day) {
            if ($day === null) {
                $row[] = (object) ['classname' => 'calendar_noday', 'content' => ''];
                continue;
            }
            $dayEvents = $this->filterEventsByDay($events, $year, $month, $day);
            $field = [];
            $classes = [];
            $field['content'] = $day;
            if (!empty($dayEvents)) {
                $field['href'] = "?{$eventpage}&month={$month}&year={$year}";
                $field['title'] = $this->getEventsTitle($dayEvents, $year);
                $classes[] = "calendar_eventday";
                $currentDay = new LocalDateTime($year, $month, $day, 0, 0);
                foreach ($dayEvents as $dayEvent) {
                    if (
                        $dayEvent->start->compareDate($currentDay) === 0
                        && $dayEvent->end->compareDate($currentDay) !== 0
                    ) {
                        $classes[] = "calendar_eventstart";
                        break;
                    }
                }
                foreach ($dayEvents as $dayEvent) {
                    if (
                        $dayEvent->end->compareDate($currentDay) === 0
                        && $dayEvent->start->compareDate($currentDay) !== 0
                    ) {
                        $classes[] = "calendar_eventend";
                        break;
                    }
                }
            }
            if ($day == $today) {
                $classes[] = "calendar_today";
            }
            if ($this->isWeekEnd(count($row))) {
                $classes[] = "calendar_we";
            } else {
                $classes[] = "calendar_day";
            }
            $field['classname'] = implode(" ", $classes);
            $row[] = (object) $field;
        }
        return $row;
    }

    /**
     * @param Event[] $events
     * @return Event[]
     */
    private function filterEventsByDay(array $events, int $year, int $month, int $day): array
    {
        $result = [];
        foreach ($events as $event) {
            if ($this->isEventOn($event, $year, $month, $day)) {
                $result[] = $event;
            } elseif ($this->isBirthdayOn($event, $month, $day)) {
                $result[] = $event;
            }
        }
        return $result;
    }

    private function isEventOn(Event $event, int $year, int $month, int $day): bool
    {
        if ($event->isBirthday()) {
            return false;
        }
        $today = new LocalDateTime($year, $month, $day, 0, 0);
        if (!$event->isMultiDay()) {
            return $event->start->compareDate($today) === 0;
        }
        if ($this->conf['show_days_between_dates']) {
            return $event->start->compareDate($today) <= 0
                && $event->end->compareDate($today) >= 0;
        }
        return $event->start->compareDate($today) === 0
            || $event->end->compareDate($today) === 0;
    }

    private function isBirthdayOn(Event $event, int $month, int $day): bool
    {
        $date = $event->start;
        return $event->isBirthday() && $date->month == $month && $date->day == $day;
    }

    /**
     * @param Event[] $events
     */
    private function getEventsTitle(array $events, int $year): string
    {
        $titles = [];
        foreach ($events as $event) {
            if ($event->isMultiDay()) {
                $text = sprintf(
                    "%s %s %s",
                    $event->summary,
                    $this->lang['event_date_till_date'],
                    $this->dateTimeFormatter->formatDateTime($event->end)
                );
            } else {
                $text = $event->summary;
            }
            if (!$event->isBirthday()) {
                $titles[] = $this->dateTimeFormatter->formatTime($event->start) . " " . $text;
            } else {
                $age = $year - $event->start->year;
                $age = sprintf($this->lang['age' . XH_numberSuffix($age)], $age);
                $titles[] = "{$text} {$age}";
            }
        }
        return implode(" | ", $titles);
    }

    private function isWeekEnd(int $dayOfWeek): bool
    {
        return $dayOfWeek === (int) $this->conf['week-end_day_1']
            || $dayOfWeek === (int) $this->conf['week-end_day_2'];
    }

    /**
     * @return stdClass[]
     */
    private function getDaynamesRow(): array
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
            $row[] = (object) [
                'classname' => 'calendar_daynames ' . ($this->isWeekEnd($i) ? 'calendar_we' : 'calendar_day'),
                'content' => $dayarray[$j],
            ];
        }
        return $row;
    }

    private function getPrevUrl(int $year, int $month): string
    {
        if ($month <= 1) {
            $month_prev = 12;
            $year_prev = $year - 1;
        } else {
            $month_prev = $month - 1;
            $year_prev = $year;
        }
        return "{$this->url}&month=$month_prev&year=$year_prev";
    }

    private function getNextUrl(int $year, int $month): string
    {
        if ($month >= 12) {
            $month_next = 1;
            $year_next = $year + 1;
        } else {
            $month_next = $month + 1;
            $year_next = $year;
        }
        return "{$this->url}&month=$month_next&year=$year_next";
    }
}
