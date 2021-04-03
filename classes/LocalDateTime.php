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
        return $localDateTime;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return int
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return sprintf("%02d:%02d", $this->hour, $this->minute);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return mktime($this->hour, $this->minute, 0, $this->month, $this->day, $this->year);
    }

    /**
     * @return int
     */
    public function compare(LocalDateTime $other)
    {
        return strcmp("{$this->getDate()}T{$this->getTime()}", "{$other->getDate()}T{$other->getTime()}");
    }
}
