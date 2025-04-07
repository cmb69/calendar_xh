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

use Calendar\Dto\BirthdayRow;
use Calendar\Dto\EventRow;
use Calendar\Dto\HeaderRow;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Plib\Request;
use Plib\View;

class EventListController
{
    /** @var array<string,string> */
    private $conf;

    /** @var EventDataService */
    private $eventDataService;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        EventDataService $eventDataService,
        DateTimeFormatter $dateTimeFormatter,
        View $view
    ) {
        $this->conf = $conf;
        $this->eventDataService = $eventDataService;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->view = $view;
    }

    public function defaultAction(int $month, int $year, int $endMonth, int $pastMonth, Request $request): string
    {
        $this->determineYearAndMonth($request, $year, $month, $pastMonth);
        $this->determineEndMonth($endMonth);
        $endMonth = $endMonth + $pastMonth;

        $calendar = $this->eventDataService->readEvents();

        $startDate = new LocalDateTime($year, $month, 1, 0, 0);
        $endDate = $startDate->plusMonths($endMonth);

        $tablecols = $this->calcTablecols();

        $monthEvents = [];
        $currDate = $startDate;
        while ($currDate->compareDate($endDate) < 0) {
            $year = $currDate->year();
            $month = $currDate->month();
            $filteredEvents = $calendar->eventsDuring($year, $month);
            if (($oneMonthEvents = $this->getMonthEvents($request, $filteredEvents, $tablecols, $year, $month))) {
                $monthEvents[] = $oneMonthEvents;
            }
            $currDate = $currDate->plusMonths(1);
        }
        $start = $this->dateTimeFormatter->formatMonthYear($startDate->month(), $startDate->year());
        $end = $this->dateTimeFormatter->formatMonthYear($endDate->month(), $endDate->year());
        return $this->view->render($this->conf["eventlist_template"], [
            'showHeading' => (bool) $this->conf['show_period_of_events'],
            'heading' => str_replace(
                ["\x06", "\x15"],
                ["<span>", "</span>"],
                $this->view->text("event_list_heading", "\x06" . $start . "\x15", "\x06" . $end . "\x15")
            ),
            'events' => $monthEvents,
        ]);
    }

    private function determineYearAndMonth(Request $request, int &$year, int &$month, int &$pastMonth): void
    {
        $month_input = $request->get("month") !== null ? max(1, min(12, (int) $request->get("month"))) : 0;

        if ($month) {
            if ($month_input) {
                if ($month >= $month_input) {
                    $month = $month_input;
                }
            }
        } else {
            $month = $month_input;
        }

        $year = $request->get("year") !== null
            ? max(1, min(9000, (int) $request->get("year")))
            : idate("Y", $request->time());

        if ($month === 0) {
            $month = idate("n", $request->time());
        }

        if (!$pastMonth) {
            $pastMonth = (int) $this->conf['show_number_of_previous_months'];
        }

        $month = $month - $pastMonth;
        if ($month < 1) {
            $year = $year - 1;
            $month = 12 + $month;
        }
    }

    /** @param-out int $endMonth */
    private function determineEndMonth(int &$endMonth): void
    {
        if ($endMonth === 0) {
            if ($this->conf['show_number_of_future_months']) {
                $endMonth = (int) $this->conf['show_number_of_future_months'];
            } else {
                $endMonth = 1;
            }
        }
    }

    private function calcTablecols(): int
    {
        // the number of tablecolumns is calculated
        // starting with minimum number of columns (date + main entry)
        $tablecols = 2;
        // adding columns according to config settings
        if ($this->conf['show_event_time']) {
            $tablecols++;
        }
        if ($this->conf['show_event_location']) {
            $tablecols++;
        }
        if ($this->conf['show_event_link']) {
            $tablecols++;
        }
        return $tablecols;
    }

    /**
     * @param array<Event> $events
     * @return ?object{headline:HeaderRow,rows:list<BirthdayRow|EventRow>}
     */
    private function getMonthEvents(Request $request, array $events, int $tablecols, int $year, int $month): ?object
    {
        if (empty($events)) {
            return null;
        }
        $result = ['headline' => $this->getHeadline($tablecols, $year, $month), 'rows' => []];
        foreach ($events as $event) {
            if ($event->isBirthday()) {
                $result['rows'][] = $this->getBirthdayRowView($event, $year);
            } else {
                $result['rows'][] = $this->getEventRowView($request, $event);
            }
        }
        return (object) $result;
    }

    private function getBirthdayRowView(Event $event, int $year): BirthdayRow
    {
        return new BirthdayRow(
            $year - $event->start()->year(),
            $event->summary(),
            $event->location(),
            $this->dateTimeFormatter->formatDate($event->start()->withYear($year)),
            (bool) $this->conf['show_event_time'],
            (bool) $this->conf['show_event_location'],
            (bool) $this->conf['show_event_link'],
            $this->renderLink($event)
        );
    }

    private function getEventRowView(Request $request, Event $event): EventRow
    {
        if ($event->isFullDay() || $event->isBirthday()) {
            $time = "";
        } else {
            $time = $this->view->text(
                "format_time_interval",
                $this->dateTimeFormatter->formatTime($event->start()),
                $this->dateTimeFormatter->formatTime($event->end())
            );
        }
        $now = LocalDateTime::fromIsoString(date("Y-m-d\TH:i", $request->time()));
        assert($now !== null);
        return new EventRow(
            $event->summary(),
            $event->location(),
            $this->renderDate($event),
            $time,
            (bool) $this->conf['show_event_time'],
            (bool) $this->conf['show_event_location'],
            (bool) $this->conf['show_event_link'],
            $this->renderLink($event),
            $event->end()->compare($now) < 0 ? "past_event" : ""
        );
    }

    private function renderDate(Event $event): string
    {
        if ($event->isMultiDay()) {
            return $this->view->plain(
                "format_date_interval",
                $this->dateTimeFormatter->formatDate($event->start()),
                $this->dateTimeFormatter->formatDate($event->end())
            );
        } else {
            return $this->dateTimeFormatter->formatDate($event->start());
        }
    }

    private function renderLink(Event $event): string
    {
        if ($event->linkadr()) {
            $url = $event->linkadr();
            $target = (strpos($url, '://') === false) ? '_self' : '_blank';
            $title = $event->summary();
            $text = $event->linktxt() ?: $event->linkadr();
            return "<a href=\"{$url}\" target=\"{$target}\" title=\"{$title}\">"
                . "{$text}</a>";
        } else {
            return $event->linktxt();
        }
    }

    private function getHeadline(int $tablecols, int $year, int $month): HeaderRow
    {
        return new HeaderRow(
            $tablecols,
            $this->dateTimeFormatter->formatMonthYear($month, $year),
            (bool) $this->conf['show_event_time'],
            (bool) $this->conf['show_event_location'],
            (bool) $this->conf['show_event_link'],
        );
    }
}
