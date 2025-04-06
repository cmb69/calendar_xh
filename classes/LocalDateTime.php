<?php

/**
 * Copyright 2021-2023 Christoph M. Becker
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

use Exception;

class LocalDateTime
{
    /** @var int */
    private $year;

    /** @var int */
    private $month;

    /** @var int */
    private $day;

    /** @var int */
    private $hour;

    /** @var int */
    private $minute;

    public static function fromIsoString(string $string): ?LocalDateTime
    {
        $pattern = '/^(\d{4})(?:-)(\d{2})(?:-)(\d{2})(?:T)(\d{2})(?::)(\d{2})$/';
        if (!preg_match($pattern, $string, $matches)) {
            return null;
        }
        try {
            return new self(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[4],
                (int) $matches[5]
            );
        } catch (Exception $ex) {
            return null;
        }
    }

    public function __construct(int $year, int $month, int $day, int $hour, int $minute)
    {
        if (!checkdate($month, $day, $year)) {
            throw new Exception("Invalid date");
        }
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            throw new Exception("Invalid time");
        }
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hour = $hour;
        $this->minute = $minute;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function month(): int
    {
        return $this->month;
    }

    public function day(): int
    {
        return $this->day;
    }

    public function hour(): int
    {
        return $this->hour;
    }

    public function minute(): int
    {
        return $this->minute;
    }

    public function withYear(int $year): self
    {
        $localDateTime = clone $this;
        $localDateTime->year = $year;
        if (
            $localDateTime->month === 2 && $localDateTime->day === 29
            && !checkdate($localDateTime->month, $localDateTime->day, $localDateTime->year)
        ) {
            $localDateTime->month = 3;
            $localDateTime->day = 1;
        }
        assert(checkdate($localDateTime->month, $localDateTime->day, $localDateTime->year));
        return $localDateTime;
    }

    public function getIsoDate(): string
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    public function getIsoTime(): string
    {
        return sprintf("%02d:%02d", $this->hour, $this->minute);
    }

    public function compare(LocalDateTime $other): int
    {
        $result = $this->compareDate($other);
        if ($result !== 0) {
            return $result;
        }
        if ($this->hour !== $other->hour) {
            return $this->hour - $other->hour;
        }
        return $this->minute - $other->minute;
    }

    public function compareDate(LocalDateTime $other): int
    {
        if ($this->year !== $other->year) {
            return $this->year - $other->year;
        }
        if ($this->month !== $other->month) {
            return $this->month - $other->month;
        }
        return $this->day - $other->day;
    }
}
