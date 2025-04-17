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

class YearlyRecurrence
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

    /** @return list<LocalDateTime> */
    public function matchesInMonth(int $year, int $month): array
    {
        if ($this->start->month() !== $month || $this->start->year() > $year) {
            return [];
        }
        return [$this->start->withYear($year)];
    }

    public function matchOnDay(int $year, int $month, int $day): ?LocalDateTime
    {
        if ($this->start->year() > $year || $this->start->month() !== $month || $this->start->day() !== $day) {
            return null;
        }
        return $this->start->withYear($year);
    }

    public function firstMatchAfter(LocalDateTime $date): ?LocalDateTime
    {
        if ($this->start->year() <= $date->year()) {
            $ldt = $this->start->withYear($date->year());
            if ($ldt->compare($date) < 0) {
                $ldt = $this->end;
                if ($ldt->compare($date) < 0) {
                    $ldt = $this->start->withYear($date->year() + 1);
                }
            }
        } else {
            $ldt = null;
        }
        return $ldt;
    }
}
