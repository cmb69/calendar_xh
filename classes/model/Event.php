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
            if (!$endtime) {
                $endtime = $starttime ? $starttime : "23:59";
            }
            if (($end = LocalDateTime::fromIsoString("{$dateend}T{$endtime}")) === null) {
                return null;
            }
        }
        if ($starttime === '') {
            $starttime = "00:00";
        }
        if (($start = LocalDateTime::fromIsoString("{$datestart}T{$starttime}")) === null) {
            return null;
        }
        return new self($start, $end, $summary, $linkadr, $linktxt, $location);
    }

    private function __construct(
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

    public function occursDuring(int $year, int $month): bool
    {
        return ($this->start->month() === $month)
            && ($this->start->year() === $year
            || $this->isBirthday() && $this->start->year() < $year);
    }

    public function occursOn(LocalDateTime $day, bool $daysBetween): bool
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        if ($this->isBirthday()) {
            return $this->start->month() === $day->month()
                && $this->start->day() === $day->day();
        }
        if (!$this->isMultiDay()) {
            return $this->start->compareDate($day) === 0;
        }
        if ($daysBetween) {
            return $this->start->compareDate($day) <= 0
                && $this->end->compareDate($day) >= 0;
        }
        return $this->start->compareDate($day) === 0
            || $this->end->compareDate($day) === 0;
    }
}
