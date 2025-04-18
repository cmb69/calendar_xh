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

class WeeklyRecurrence implements Recurrence
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
        return "weekly";
    }

    /** @return list<LocalDateTime> */
    public function matchesInMonth(int $year, int $month): array
    {
        $res = [];
        $firstDayOfMonth = new LocalDateTime($year, $month, 1, 0, 0);
        $nextMonth = $firstDayOfMonth->plusMonths(1);
        $interval = $firstDayOfMonth->diff($this->start);
        $week = new Interval(7, 0, 0);
        if ($interval->negative()) {
            $start = $this->start;
        } else {
            $days = 7 * (int) ceil($interval->days() / 7);
            $start = $this->start->plus(new Interval($days, 0, 0));
        }
        while ($start->compare($nextMonth) < 0) {
            $res[] = $start;
            $start = $start->plus($week);
        }
        return $res;
    }

    public function matchOnDay(LocalDateTime $day, bool $daysBetween): ?LocalDateTime
    {
        $end = $day->plus(new Interval(0, 23, 59));
        $interval = $end->diff($this->start);
        if ($interval->negative()) {
            return null;
        }
        if ($interval->days() % 7 === 0) {
            return $this->start->plus(new Interval($interval->days(), 0, 0));
        }
        return null;
    }

    /** @return ?array{LocalDateTime,LocalDateTime} */
    public function firstMatchAfter(LocalDateTime $date): ?array
    {
        $week = new Interval(7, 0, 0);
        $start = $this->start;
        while ($start->compare($date) < 0) {
            $start = $start->plus($week);
        }
        $end = $this->end;
        while ($end->compare($date) < 0) {
            $end = $end->plus($week);
        }
        if ($start->compare($end) <= 0) {
            return [$start, $start];
        }
        return [$start->plus($week->negate()), $end];
    }
}
