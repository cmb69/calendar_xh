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

interface Recurrence
{
    public function name(): string;

    public function start(): LocalDateTime;

    public function end(): LocalDateTime;

    public function until(): ?LocalDateTime;

    /** @return list<LocalDateTime> */
    public function matchesInMonth(int $year, int $month): array;

    public function matchOnDay(LocalDateTime $day, bool $daysBetween): ?LocalDateTime;

    /** @return ?array{LocalDateTime,LocalDateTime} */
    public function firstMatchAfter(LocalDateTime $date): ?array;

    /** @return array{?Recurrence,?NoRecurrence,?Recurrence} */
    public function split(LocalDateTime $date): array;
}
