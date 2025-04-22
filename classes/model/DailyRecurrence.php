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

class DailyRecurrence extends Recurrence
{
    /** @var LocalDateTime */
    private $start;

    /** @var LocalDateTime */
    private $end;

    /** @var ?LocalDateTime */
    private $until;

    public function __construct(LocalDateTime $start, LocalDateTime $end, ?LocalDateTime $until)
    {
        $this->start = $start;
        $this->end = $end;
        $this->until = $until;
    }

    public function name(): string
    {
        return "daily";
    }

    public function start(): LocalDateTime
    {
        return $this->start;
    }

    public function end(): LocalDateTime
    {
        return $this->end;
    }

    public function until(): ?LocalDateTime
    {
        return $this->until;
    }

    /** @return list<LocalDateTime> */
    public function matchesInMonth(int $year, int $month): array
    {
        $res = [];
        $firstDayOfMonth = new LocalDateTime($year, $month, 1, 0, 0);
        $nextMonth = $firstDayOfMonth->plusMonths(1);
        $until = $this->until !== null && $this->until->compare($nextMonth) <= 0 ? $this->until : $nextMonth;
        $interval = $firstDayOfMonth->diff($this->start);
        $day = new Interval(1, 0, 0);
        if ($interval->negative()) {
            $start = $this->start;
        } else {
            $start = $this->start->plus(new Interval($interval->days() + 1, 0, 0));
        }
        while ($start->compare($until) < 0) {
            $res[] = $start;
            $start = $start->plus($day);
        }
        return $res;
    }

    public function matchOnDay(LocalDateTime $day, bool $daysBetween): ?LocalDateTime
    {
        $start = $this->start;
        $day = new LocalDateTime($day->year(), $day->month(), $day->day(), $start->hour(), $start->minute());
        $interval = $day->diff($start);
        if ($interval->negative()) {
            return null;
        }
        $start = $start->plus($interval);
        if ($this->until !== null && $start->compare($this->until) > 0) {
            return null;
        }
        $duration = $this->end->date()->diff($this->start->date());
        $end = $start->plus($duration);
        $candidate = new NoRecurrence($start, $end);
        return $candidate->matchOnDay($day, $daysBetween);
    }

    /** @return ?array{LocalDateTime,LocalDateTime} */
    public function firstMatchAfter(LocalDateTime $date): ?array
    {
        $day = new Interval(1, 0, 0);
        $start = $this->start;
        while ($start->compare($date) < 0) {
            $start = $start->plus($day);
        }
        $end = $this->end;
        while ($end->compare($date) < 0) {
            $end = $end->plus($day);
        }
        if (($this->until === null || $start->compare($this->until) <= 0) && $start->compare($end) <= 0) {
            return [$start, $start];
        }
        $start = $start->minus($day);
        if ($this->until !== null && $start->compare($this->until) > 0) {
            return null;
        }
        return [$start, $end];
    }

    /** @return array{?Recurrence,?NoRecurrence,?Recurrence} */
    public function split(LocalDateTime $date): array
    {
        if (
            $date->compareDate($this->start) < 0
            || $this->until !== null && $date->compareDate($this->until) > 0
        ) {
            return [null, null, null];
        }
        $day = new Interval(1, 0, 0);
        $duration = $this->end->diff($this->start);
        $start = new LocalDateTime(
            $date->year(),
            $date->month(),
            $date->day(),
            $this->start->hour(),
            $this->start->minute()
        );
        $rec = new NoRecurrence($start, $start->plus($duration));
        if ($this->start->compare($start) >= 0) {
            $prev = null;
        } else {
            $prev = clone $this;
            $prev->until = $start->minus($day)->endOfDay();
        }
        $nextstart = $start->plus($day);
        if ($this->until !== null && $nextstart->compareDate($this->until) > 0) {
            $next = null;
        } else {
            $next = clone $this;
            $next->start = $nextstart;
            $next->end = $next->start->plus($duration);
        }
        return [$prev, $rec, $next];
    }
}
