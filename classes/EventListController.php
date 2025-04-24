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
use Calendar\Infra\DateTimeFormatter;
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Plib\DocumentStore;
use Plib\Request;
use Plib\View;

class EventListController
{
    /** @var array<string,string> */
    private $conf;

    /** @var DocumentStore */
    private $store;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        DocumentStore $store,
        DateTimeFormatter $dateTimeFormatter,
        View $view
    ) {
        $this->conf = $conf;
        $this->store = $store;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->view = $view;
    }

    public function defaultAction(int $month, int $year, int $futureMonths, int $pastMonths, Request $request): string
    {
        if ($pastMonths === 0) {
            $pastMonths = (int) $this->conf['show_number_of_previous_months'];
        }
        $pastMonths = max(0, $pastMonths);
        if ($futureMonths === 0) {
            $futureMonths = (int) $this->conf['show_number_of_future_months'];
        }
        $futureMonths = max(1, $futureMonths);
        $desiredMonth = $this->desiredMonth($request, $year, $month);
        $startDate = $desiredMonth->plusMonths(-$pastMonths);
        $endDate = $desiredMonth->plusMonths($futureMonths);
        $tablecols = $this->calcTablecols();
        $monthEvents = [];
        $calendar = Calendar::retrieveFrom($this->store);
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

    private function desiredMonth(Request $request, int $year, int $month): LocalDateTime
    {
        if ($month === 0) {
            $month = $request->get("month") !== null
                ? max(1, min(12, (int) $request->get("month")))
                : (int)idate("n", $request->time());
        }
        $month = max(1, min(12, $month));
        if ($year === 0) {
            $year = $request->get("year") !== null
                ? max(1, min(9000, (int) $request->get("year")))
                : (int) idate("Y", $request->time());
        }
        $year = max(1, min(9000, $year));
        return new LocalDateTime($year, $month, 1, 0, 0);
    }

    private function calcTablecols(): int
    {
        return 2 + (bool) $this->conf["show_event_time"]
            + (bool) $this->conf["show_event_location"]
            + (bool) $this->conf["show_event_link"];
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
        assert($event->isBirthday());
        return new BirthdayRow(
            $event->age(),
            $event->summary(),
            $event->location(),
            $event->start()->withYear($year)->getIsoDate(),
            $event->end()->withYear($year)->getIsoDate(),
            $this->dateTimeFormatter->formatDate($event->start()->withYear($year)),
            (bool) $this->conf['show_event_time'],
            (bool) $this->conf['show_event_location'],
            (bool) $this->conf['show_event_link'],
            $event->linkadr(),
            $event->linktxt()
        );
    }

    private function getEventRowView(Request $request, Event $event): EventRow
    {
        $now = LocalDateTime::fromIsoString(date("Y-m-d\TH:i", $request->time()));
        assert($now !== null);
        $startDate = $event->isFullDay()
            ? $event->getIsoStartDate()
            : $event->getIsoStartDate() . "T" . $event->getIsoStartTime() . "00";
        $endDate = $event->isFullDay()
            ? $event->getIsoEndDate()
            : $event->getIsoEndDate() . "T" . $event->getIsoEndTime() . "00";
        return new EventRow(
            $event->summary(),
            $event->location(),
            $startDate,
            $endDate,
            $this->renderDate($event),
            $this->renderTime($event),
            $this->renderDateTime($event),
            (bool) $this->conf['show_event_time'],
            (bool) $this->conf['show_event_location'],
            (bool) $this->conf['show_event_link'],
            $event->linkadr(),
            $event->linktxt(),
            $event->end()->compare($now) < 0 ? "past_event" : ""
        );
    }

    private function renderDate(Event $event): string
    {
        if (!$event->isMultiDay()) {
            return $this->view->esc($this->dateTimeFormatter->formatDate($event->start()));
        }
        return str_replace(
            ["\x06", "\x15"],
            ["<span>", "</span>"],
            $this->view->text(
                "format_date_interval",
                "\x06" . $this->dateTimeFormatter->formatDate($event->start()) . "\x15",
                "\x06" . $this->dateTimeFormatter->formatDate($event->end()) . "\x15"
            )
        );
    }

    private function renderTime(Event $event): string
    {
        if ($event->isFullDay() || $event->isBirthday()) {
            return "";
        }
        return str_replace(
            ["\x06", "\x15"],
            ["<span>", "</span>"],
            $this->view->text(
                "format_time_interval",
                "\x06" . $this->dateTimeFormatter->formatTime($event->start()) . "\x15",
                "\x06" . $this->dateTimeFormatter->formatTime($event->end()) . "\x15"
            )
        );
    }

    private function renderDateTime(Event $event): string
    {
        if (!$event->isMultiDay()) {
            if ($event->isFullDay() || $event->isBirthday()) {
                $dateTime = $this->view->esc("\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10");
            } else {
                $dateTime = $this->view->text(
                    "format_date-time",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                    $this->view->plain(
                        "format_time_interval",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->start()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->end()) . "\x10"
                    )
                );
            }
        } else {
            if ($event->isFullDay() || $event->isBirthday()) {
                $dateTime = $this->view->text(
                    "format_date_interval",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->end()) . "\x10"
                );
            } else {
                $dateTime = $this->view->text(
                    "format_date_interval",
                    $this->view->plain(
                        "format_date-time",
                        "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->start()) . "\x10"
                    ),
                    $this->view->plain(
                        "format_date-time",
                        "\x11" . $this->dateTimeFormatter->formatDate($event->end()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->end()) . "\x10"
                    )
                );
            }
        }
        return str_replace(
            ["\x11", "\x12", "\x10"],
            ['<span class="event_date">', '<span class="event_time">', "</span>"],
            $dateTime
        );
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
