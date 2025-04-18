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

namespace Calendar\Model;

use Calendar\Html2Text;

/** @phpstan-consistent-constructor */
class Event
{
    /** @var LocalDateTime */
    private $start;

    /** @var LocalDateTime */
    private $end;

    /** @var string */
    private $summary;

    /** @var string */
    private $linkadr;

    /** @var string */
    private $linktxt;

    /** @var string */
    private $location;

    /** @var Recurrence */
    private $recurrence;

    public static function create(
        string $datestart,
        ?string $dateend,
        string $starttime,
        ?string $endtime,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location,
        string $recurrenceRule,
        string $until
    ): ?self {
        if (!$dateend) {
            if ($endtime) {
                return null;
            }
            $endtime = $starttime ? $starttime : "23:59";
            if (($end = LocalDateTime::fromIsoString("{$datestart}T{$endtime}")) === null) {
                return null;
            }
        } else {
            if (trim($location) === "###") {
                $endtime = "23:59";
            } elseif (!$endtime) {
                $endtime = $starttime ? $starttime : "23:59";
            }
            if (($end = LocalDateTime::fromIsoString("{$dateend}T{$endtime}")) === null) {
                return null;
            }
        }
        if (trim($location) === "###" || $starttime === '') {
            $starttime = "00:00";
        }
        if (($start = LocalDateTime::fromIsoString("{$datestart}T{$starttime}")) === null) {
            return null;
        }
        if (trim($location) === "###") {
            return new BirthdayEvent($start, $end, $summary, $linkadr, $linktxt, $location);
        }
        $recurrence = self::createRecurrence($recurrenceRule, $start, $end, $until);
        return new self($start, $end, $summary, $linkadr, $linktxt, $location, $recurrence);
    }

    private static function createRecurrence(
        string $recurrenceRule,
        LocalDateTime $start,
        LocalDateTime $end,
        string $until
    ): Recurrence {
        $until = LocalDateTime::fromIsoString("{$until}T23:59");
        if ($recurrenceRule === "yearly") {
            $recurrence = new YearlyRecurrence($start, $end, $until);
        } elseif ($recurrenceRule === "weekly") {
            $recurrence = new WeeklyRecurrence($start, $end, $until);
        } else {
            $recurrence = new NoRecurrence($start, $end);
        }
        return $recurrence;
    }

    protected function __construct(
        LocalDateTime $start,
        LocalDateTime $end,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location,
        Recurrence $recurrence
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->summary = $summary;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
        $this->recurrence = $recurrence;
    }

    public function start(): LocalDateTime
    {
        return $this->start;
    }

    public function end(): LocalDateTime
    {
        return $this->end;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function linkadr(): string
    {
        return $this->linkadr;
    }

    public function linktxt(): string
    {
        return $this->linktxt;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function recurrence(): Recurrence
    {
        return $this->recurrence;
    }

    public function getIsoStartDate(): string
    {
        return $this->start->getIsoDate();
    }

    public function getIsoStartTime(): string
    {
        return $this->start->getIsoTime();
    }

    public function getIsoEndDate(): string
    {
        return $this->end->getIsoDate();
    }

    public function getIsoEndTime(): string
    {
        return $this->end->getIsoTime();
    }

    public function isMultiDay(): bool
    {
        return $this->end->compareDate($this->start) > 0;
    }

    public function startsOn(LocalDateTime $day): bool
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        return $this->start()->compareDate($day) === 0
            && $this->end()->compareDate($day) !== 0;
    }

    public function endsOn(LocalDateTime $day): bool
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        return $this->end()->compareDate($day) === 0
            && $this->start()->compareDate($day) !== 0;
    }

    public function isFullDay(): bool
    {
        return $this->start->hour() === 0 && $this->start->minute() === 0
            && $this->end->hour() === 23 && $this->end->minute() === 59;
    }

    /** @return list<Event> */
    public function occurrencesDuring(int $year, int $month): array
    {
        $res = [];
        foreach ($this->recurrence->matchesInMonth($year, $month) as $match) {
            $res[] = $this->occurrenceStartingAt($match);
        }
        return $res;
    }

    public function occurrenceOn(LocalDateTime $day, bool $daysBetween): ?self
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $match = $this->recurrence->matchOnDay($day, $daysBetween);
        if ($match === null) {
            return null;
        }
        return $this->occurrenceStartingAt($match);
    }

    /** @return array{?self,?LocalDateTime} */
    public function earliestOccurrenceAfter(LocalDateTime $date): array
    {
        $match = $this->recurrence->firstMatchAfter($date);
        if ($match === null) {
            return [null, null];
        }
        return [$this->occurrenceStartingAt($match[0]), $match[1]];
    }

    /** @return static */
    public function occurrenceStartingAt(LocalDateTime $start)
    {
        $duration = $this->end()->diff($this->start());
        $end = $start->plus($duration);
        return new static(
            $start,
            $end,
            $this->summary,
            $this->linkadr,
            $this->linktxt,
            $this->location,
            new NoRecurrence($start, $end)
        );
    }

    public function toICalendarString(string $id, Html2Text $converter, string $host): string
    {
        $res = "BEGIN:VEVENT\r\n"
            . "UID:$id@$host\r\n";
        $res .= $this->getDtstart() . "\r\n";
        $res .= $this->getDtend() . "\r\n";
        if (!($this->recurrence() instanceof NoRecurrence)) {
            $freq = strtoupper($this->recurrence()->name());
            $res .= "RRULE:FREQ={$freq}";
            $until = $this->recurrence()->until();
            if ($until !== null) {
                if ($this->isFullDay()) {
                    $until = sprintf("%04d%02d%02d", $until->year(), $until->month(), $until->day());
                } else {
                    $until = sprintf(
                        "%04d%02d%02dT%02d%02d00",
                        $until->year(),
                        $until->month(),
                        $until->day(),
                        $this->start->hour(),
                        $this->start->minute()
                    );
                }
                $res .= ";UNTIL=" . $until;
            }
            $res .= "\r\n";
        }
        if ($this->summary !== "") {
            $res .= "SUMMARY:" . $this->summary . "\r\n";
        }
        if ($this->linkadr !== "") {
            $res .= "URL:" . $this->linkadr . "\r\n";
        }
        if ($this->linktxt !== "") {
            $converter->setHtml($this->linktxt);
            $text = $converter->getText();
            $text = str_replace(["\\", ";", ",", "\r", "\n"], ["\\\\", "\\;", "\\,", "", "\\n\r\n "], $text);
            $res .= "DESCRIPTION:" . rtrim($text) . "\r\n";
        }
        $res .= $this->locationToICalendarString();
        $res .= "END:VEVENT\r\n";
        return $res;
    }

    protected function locationToICalendarString(): string
    {
        if ($this->location === "") {
            return "";
        }
        return "LOCATION:" . $this->location . "\r\n";
    }

    private function getDtstart(): string
    {
        if ($this->start->hour() === 23 && $this->start->minute() === 59) {
            return sprintf(
                "DTSTART;VALUE=DATE:%04d%02d%02d",
                $this->start->year(),
                $this->start->month(),
                $this->start->day()
            );
        } else {
            return sprintf(
                "DTSTART:%04d%02d%02dT%02d%02d00",
                $this->start->year(),
                $this->start->month(),
                $this->start->day(),
                $this->start->hour(),
                $this->start->minute()
            );
        }
    }

    private function getDtend(): string
    {
        if ($this->end->hour() === 23 && $this->end->minute() === 59) {
            return sprintf(
                "DTEND;VALUE=DATE:%04d%02d%02d",
                $this->end->year(),
                $this->end->month(),
                $this->end->day()
            );
        } else {
            return sprintf(
                "DTEND:%04d%02d%02dT%02d%02d00",
                $this->end->year(),
                $this->end->month(),
                $this->end->day(),
                $this->end->hour(),
                $this->end->minute()
            );
        }
    }
}
