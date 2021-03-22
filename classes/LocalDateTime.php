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

class LocalDateTime
{
    /** @var int|null */
    private $year = null;

    /** @var int|null */
    private $month = null;

    /** @var int|null */
    private $day = null;

    /** @var int|null */
    private $hour = null;

    /** @var int|null */
    private $minute = null;

    /**
     * @param string|null $date
     * @param string|null $time
     */
    public function __construct($date, $time)
    {
        if ($date !== null) {
            list($year, $month, $day) = explode('-', $date);
            $this->year = (int) $year;
            $this->month = (int) $month;
            $this->day = (int) $day;
        }
        if ($time !== null && $time !== '') {
            list($hour, $minute) = explode(':', $time);
            $this->hour = (int) $hour;
            $this->minute = (int) $minute;
        }
    }

    /**
     * @return int
     */
    public function getYear()
    {
        assert($this->year !== null);
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        assert($this->month !== null);
        return $this->month;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        assert($this->day !== null);
        return $this->day;
    }

    /**
     * @return int
     */
    public function getHour()
    {
        assert($this->hour !== null);
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getMinute()
    {
        assert($this->minute !== null);
        return $this->minute;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if ($this->year === null || $this->month === null || $this->day === null) {
            return '';
        }
        return sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);
    }

    /**
     * @return string
     */
    public function getTime()
    {
        if ($this->hour === null || $this->minute === null) {
            return '';
        }
        return sprintf("%02d:%02d", $this->hour, $this->minute);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return mktime((int) $this->hour, (int) $this->minute, 0, (int) $this->month, (int) $this->day, (int) $this->year);
    }

    /**
     * @return int
     */
    public function compare(LocalDateTime $other)
    {
        return strcmp("{$this->getDate()}T{$this->getTime()}", "{$other->getDate()}T{$other->getTime()}");
    }
}
