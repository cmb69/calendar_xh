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

use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Plib\Request;
use Plib\Response;
use Plib\View;

class CalendarController
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var EventDataService */
    private $eventDataService;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var int */
    private $widgetNum;

    /** @var Counter */
    private $counter;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        array $conf,
        EventDataService $eventDataService,
        DateTimeFormatter $dateTimeFormatter,
        int $widgetNum,
        Counter $counter,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->conf = $conf;
        $this->eventDataService = $eventDataService;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->widgetNum = $widgetNum;
        $this->counter = $counter;
        $this->view = $view;
    }

    public function defaultAction(int $year, int $month, string $eventpage, Request $request): Response
    {
        if ($this->xhr($request) === false) {
            return Response::create();
        }
        if ($eventpage == '') {
            $eventpage = $this->view->plain("event_page");
        }
        $this->determineYearAndMonth($request, $year, $month);
        $calendar = $this->eventDataService->readEvents();
        $calendarService = new CalendarService((bool) $this->conf['week_starts_mon']);
        $rows = [];
        foreach ($calendarService->getMonthMatrix($year, $month) as $columns) {
            $rows[] = $this->getRowData($request, $calendar, $columns, $year, $month, $eventpage);
        }
        $js = $this->pluginFolder . "js/calendar.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "js/calendar.js";
        }
        $currMonth = new LocalDateTime($year, $month, 1, 0, 0);
        $prevMonth = $currMonth->plusMonths(-1);
        $nextMonth = $currMonth->plusMonths(1);
        $data = [
            'caption' => $this->dateTimeFormatter->formatMonthYear($month, $year),
            'hasPrevNextButtons' => (bool) $this->conf['prev_next_button'],
            'prevUrl' => $request->url()->with("month", (string) $prevMonth->month())
                ->with("year", (string) $prevMonth->year())->relative(),
            'nextUrl' => $request->url()->with("month", (string) $nextMonth->month())
                ->with("year", (string) $nextMonth->year())->relative(),
            'prevId' => "calendar_id_" . $this->counter->next(),
            'nextId' => "calendar_id_" . $this->counter->next(),
            'headRow' => $this->getDaynamesRow(),
            'rows' => $rows,
            'jsUrl' => $request->url()->path($js)->with("v", CALENDAR_VERSION)->relative(),
        ];
        if ($this->xhr($request)) {
            return Response::create($this->view->render('calendar', $data))->withContentType("text/html");
        }
        $output = "<div class=\"calendar_calendar\" data-num=\"$this->widgetNum\">"
            . $this->view->render('calendar', $data)
            . "</div>";
        return Response::create($output);
    }

    private function determineYearAndMonth(Request $request, int &$year, int &$month): void
    {
        if ($month === 0) {
            $month = $request->get("month") !== null
                ? max(1, min(12, (int) $request->get("month")))
                : idate("n", $request->time());
        }
        if ($year === 0) {
            $year = $request->get("year") !== null
                ? max(1, min(9000, (int) $request->get("year")))
                : idate("Y", $request->time());
        }
    }

    /**
     * @param array<int|null> $columns
     * @return list<array{classname:string,content:string,href?:string,title?:string}>
     */
    private function getRowData(
        Request $request,
        Calendar $calendar,
        array $columns,
        int $year,
        int $month,
        string $eventpage
    ): array {
        $today = ($month === idate("n", $request->time()) && $year === idate("Y", $request->time()))
            ? idate("j", $request->time())
            : 32;
        $row = [];
        foreach ($columns as $day) {
            if ($day === null) {
                $row[] = ['classname' => 'calendar_noday', 'content' => ''];
                continue;
            }
            $currentDay = new LocalDateTime($year, $month, $day, 0, 0);
            $dayEvents = $calendar->eventsOn($currentDay, (bool) $this->conf['show_days_between_dates']);
            $field = [];
            $classes = [];
            $field['content'] = (string) $day;
            if (!empty($dayEvents)) {
                $field['id'] = "calendar_id_" . $this->counter->next();
                $field['href'] = $request->url()->page($eventpage)
                    ->with("month", (string) $month)->with("year", (string) $year)
                    ->relative();
                $field['title'] = $this->getEventsTitle($dayEvents, $year);
                $classes[] = "calendar_eventday";
                foreach ($dayEvents as $dayEvent) {
                    if ($dayEvent->startsOn($currentDay)) {
                        $classes[] = "calendar_eventstart";
                        break;
                    }
                }
                foreach ($dayEvents as $dayEvent) {
                    if ($dayEvent->endsOn($currentDay)) {
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
            $row[] = $field;
        }
        return $row;
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
                    $event->summary(),
                    $this->view->plain("event_date_till_date"),
                    $this->dateTimeFormatter->formatDateTime($event->end())
                );
            } else {
                $text = $event->summary();
            }
            $text = $this->view->esc($text);
            if (!$event->isBirthday()) {
                if (!$event->isFullDay() && !$event->isMultiDay()) {
                    $time = $this->view->text(
                        "format_time_interval",
                        $this->dateTimeFormatter->formatTime($event->start()),
                        $this->dateTimeFormatter->formatTime($event->end())
                    );
                } elseif ($event->isFullDay()) {
                    $time = "";
                } else {
                    $time = $this->view->esc($this->dateTimeFormatter->formatTime($event->start()));
                }
                $titles[] = $time . " " . $text;
            } else {
                $age = $year - $event->start()->year();
                $age = $this->view->plural("age", $age);
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
     * @return list<array{classname:string,content:string}>
     */
    private function getDaynamesRow(): array
    {
        $dayarray = explode(',', $this->view->plain("daynames_array"));
        $dayarrayfull = explode(',', $this->view->plain("daynames_array_full"));
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
            $row[] = [
                'classname' => 'calendar_daynames ' . ($this->isWeekEnd($i) ? 'calendar_we' : 'calendar_day'),
                'content' => $dayarray[$j],
                'full_name' => $dayarrayfull[$j],
            ];
        }
        return $row;
    }

    private function xhr(Request $request): ?bool
    {
        $header = $request->header("X-CMSimple-XH-Request");
        if ($header === null) {
            return null;
        }
        if (strncmp($header, "calendar-", strlen("calendar-")) !== 0) {
            return null;
        }
        return (int) substr($header, strlen("calendar-")) === $this->widgetNum;
    }
}
