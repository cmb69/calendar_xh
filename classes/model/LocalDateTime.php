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

namespace Calendar\Model;

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

    public function plus(Duration $duration): self
    {
        $that = clone $this;
        $that->day += $duration->days();
        $that->hour += $duration->hours();
        $that->minute += $duration->minutes();
        if ($that->minute >= 60) {
            $that->minute -= 60;
            $that->hour += 1;
        }
        if ($that->hour >= 24) {
            $that->hour -= 24;
            $that->day += 1;
        }
        while ($that->day > $this->daysPerMonth($that->year, $that->month)) {
            $that->day -= $this->daysPerMonth($that->year, $that->month);
            $that->month += 1;
            if ($that->month > 12) {
                $that->month -= 12;
                $that->year += 1;
            }
        }
        return $that;
    }

    public function minus(LocalDateTime $other): Duration
    {
        assert($this->compare($other) >= 0);
        $month = new self($this->year, $this->month, 1, 0, 0);
        $minutes = $this->minute - $other->minute;
        $hours = $this->hour - $other->hour;
        $days = $this->day - $other->day;
        if ($minutes < 0) {
            $minutes += 60;
            $hours -= 1;
        }
        if ($hours < 0) {
            $hours += 24;
            $days -= 1;
        }
        if ($days < 0) {
            $month = $month->plusMonths(-1);
            $days += $this->daysPerMonth($month->year, $month->month);
        }
        while ($month->compareDate($other) > 0) {
            $month = $month->plusMonths(-1);
            $days += $this->daysPerMonth($month->year, $month->month);
        }
        return new Duration($days, $hours, $minutes);
    }

    private function daysPerMonth(int $year, int $month): int
    {
        assert($month >= 1 && $month <= 12);
        $res = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][$month - 1];
        if ($month === 2 && ($year % 400 === 0 || ($year % 4 === 0 && $year % 100 !== 0))) {
            $res++; // leap day
        }
        return $res;
    }

    public function plusMonths(int $months): self
    {
        assert($this->day === 1 && $this->hour === 0 && $this->minute === 0);
        if ($months === 0) {
            return $this;
        }
        $year = $this->year;
        $month = $this->month + $months;
        while ($month < 1) {
            $year--;
            $month += 12;
        }
        while ($month > 12) {
            $year++;
            $month -= 12;
        }
        return new self($year, $month, 1, 0, 0);
    }
}
