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

    /** @var ?int */
    private $age = null;

    public static function create(
        string $datestart,
        ?string $dateend,
        string $starttime,
        ?string $endtime,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location
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
        return new self($start, $end, $summary, $linkadr, $linktxt, $location);
    }

    public function __construct(
        LocalDateTime $start,
        LocalDateTime $end,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->summary = $summary;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
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

    public function age(): ?int
    {
        return $this->age;
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

    public function isBirthday(): bool
    {
        return trim($this->location) === '###';
    }

    public function occurrenceDuring(int $year, int $month): ?Event
    {
        if (
            $this->isBirthday()
            && $this->start->month() === $month
            && $this->start->year() <= $year
        ) {
            return $this->birthdayOccurrenceIn($year);
        } elseif (
            $this->start->month() === $month
            && $this->start->year() === $year
        ) {
            return $this;
        }
        return null;
    }

    public function occurrenceOn(LocalDateTime $day, bool $daysBetween): ?Event
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        if (
            $this->isBirthday()
            && $this->start->year() <= $day->year()
            && $this->start->month() === $day->month()
            && $this->start->day() === $day->day()
        ) {
            return $this->birthdayOccurrenceIn($day->year());
        }
        if (!$this->isMultiDay() && $this->start->compareDate($day) === 0) {
            return $this;
        }
        if (
            $daysBetween
            && $this->start->compareDate($day) <= 0
            && $this->end->compareDate($day) >= 0
        ) {
            return $this;
        }
        if (
            $this->start->compareDate($day) === 0
            || $this->end->compareDate($day) === 0
        ) {
            return $this;
        }
        return null;
    }

    /** @return array{?Event,?LocalDateTime} */
    public function earliestOccurrenceAfter(LocalDateTime $date): array
    {
        if ($this->isBirthday()) {
            if ($this->start->year() <= $date->year()) {
                $ldt = $this->start()->withYear($date->year());
                if ($ldt->compare($date) < 0) {
                    $ldt = $this->end();
                    if ($ldt->compare($date) < 0) {
                        $ldt = $this->start()->withYear($date->year() + 1);
                    }
                }
            } else {
                $ldt = null;
            }
            return [$ldt === null ? null : $this->birthdayOccurrenceIn($ldt->year()), $ldt];
        } else {
            $ldt = $this->start();
            if ($ldt->compare($date) < 0) {
                $ldt = $this->end();
                if ($ldt->compare($date) < 0) {
                    return [null, null];
                }
            }
            return [$this, $ldt];
        }
    }

    public function birthdayOccurrenceIn(int $year): Event
    {
        assert($this->isBirthday());
        $that = clone $this;
        $that->age = $year - $this->start->year();
        $that->start = $that->start->withYear($year);
        $that->end = $that->end->withYear($year);
        return $that;
    }

    public function toICalendarString(string $id, Html2Text $converter, string $host): string
    {
        $res = "BEGIN:VEVENT\r\n"
            . "UID:$id@$host\r\n";
        $res .= $this->getDtstart() . "\r\n";
        $res .= $this->getDtend() . "\r\n";
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
        if ($this->isBirthday()) {
            $res .= "RRULE:FREQ=YEARLY\r\n";
        } elseif ($this->location() !== "") {
            $res .= "LOCATION:" . $this->location . "\r\n";
        }
        $res .= "END:VEVENT\r\n";
        return $res;
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
