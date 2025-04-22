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

abstract class Recurrence
{
    public static function create(
        string $recurrenceRule,
        LocalDateTime $start,
        LocalDateTime $end,
        string $until
    ): Recurrence {
        $until = LocalDateTime::fromIsoString("{$until}T23:59");
        switch ($recurrenceRule) {
            case "yearly":
                return new YearlyRecurrence($start, $end, $until);
            case "weekly":
                return new WeeklyRecurrence($start, $end, $until);
            case "daily":
                return new DailyRecurrence($start, $end, $until);
            default:
                return new NoRecurrence($start, $end);
        }
    }

    abstract public function name(): string;

    abstract public function start(): LocalDateTime;

    abstract public function end(): LocalDateTime;

    abstract public function until(): ?LocalDateTime;

    /** @return list<LocalDateTime> */
    abstract public function matchesInMonth(int $year, int $month): array;

    abstract public function matchOnDay(LocalDateTime $day, bool $daysBetween): ?LocalDateTime;

    /** @return ?array{LocalDateTime,LocalDateTime} */
    abstract public function firstMatchAfter(LocalDateTime $date): ?array;

    /** @return array{?Recurrence,?NoRecurrence,?Recurrence} */
    abstract public function split(LocalDateTime $date): array;
}
