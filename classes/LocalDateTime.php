<?php

/**
 * Copyright 2021 Christoph M. Becker
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
    /**
     * @readonly
     * @var int
     */
    public $year;

    /**
     * @readonly
     * @var int
     */
    public $month;

    /**
     * @readonly
     * @var int
     */
    public $day;

    /**
     * @readonly
     * @var int
     */
    public $hour;

    /**
     * @readonly
     * @var int
     */
    public $minute;

    /**
     * @param string $string
     * @return LocalDateTime|null
     */
    public static function fromIsoString($string)
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

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     */
    public function __construct($year, $month, $day, $hour, $minute)
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

    /**
     * @param int $year
     * @return self
     */
    public function withYear($year)
    {
        $localDateTime = clone $this;
        $localDateTime->year = $year;
        if ($localDateTime->month === 2 && $localDateTime->day === 29
            && !checkdate($localDateTime->month, $localDateTime->day, $localDateTime->year)
        ) {
            $localDateTime->month = 3;
            $localDateTime->day = 1;
        }
        assert(checkdate($localDateTime->month, $localDateTime->day, $localDateTime->year));
        return $localDateTime;
    }

    /**
     * @return string
     */
    public function getIsoDate()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    /**
     * @return string
     */
    public function getIsoTime()
    {
        return sprintf("%02d:%02d", $this->hour, $this->minute);
    }

    /**
     * @return int
     */
    public function compare(LocalDateTime $other)
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

    /**
     * @return int
     */
    public function compareDate(LocalDateTime $other)
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
