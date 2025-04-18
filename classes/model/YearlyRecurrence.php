<?php

/**
 * Copyright (c) Christoph M. Becker
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

class YearlyRecurrence implements Recurrence
{
    /** @var LocalDateTime */
    private $start;

    /** @var LocalDateTime */
    private $end;

    public function __construct(LocalDateTime $start, LocalDateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function name(): string
    {
        return "yearly";
    }

    /** @return list<LocalDateTime> */
    public function matchesInMonth(int $year, int $month): array
    {
        if ($this->start->month() !== $month || $this->start->year() > $year) {
            return [];
        }
        return [$this->start->withYear($year)];
    }

    public function matchOnDay(LocalDateTime $day, bool $daysBetween): ?LocalDateTime
    {
        if ($this->start->compareDate($day) > 0) {
            return null;
        }
        $start = $this->start->withYear($day->year());
        $duration = $this->end->diff($this->start);
        $end = $start->plus($duration);
        $candidate = new NoRecurrence($start, $end);
        $match = $candidate->matchOnDay($day, $daysBetween);
        if ($match !== null) {
            return $match;
        }
        $end = $this->end->withYear($day->year());
        $start = $end->minus($duration);
        $candidate = new NoRecurrence($start, $end);
        $match = $candidate->matchOnDay($day, $daysBetween);
        return $match;
    }

    /** @return ?array{LocalDateTime,LocalDateTime} */
    public function firstMatchAfter(LocalDateTime $date): ?array
    {
        if ($this->start->year() > $date->year()) {
            return null;
        }
        $duration = $this->end->diff($this->start);
        $end = $this->end->withYear($date->year());
        $start = $end->minus($duration);
        $candidate = new NoRecurrence($start, $end);
        $match = $candidate->firstMatchAfter($date);
        if ($match !== null) {
            return $match;
        }
        $end = $this->end->withYear($date->year() + 1);
        $start = $end->minus($duration);
        $candidate = new NoRecurrence($start, $end);
        $candidate = new NoRecurrence($start, $end);
        return $candidate->firstMatchAfter($date);
    }
}
