<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2021 Christoph M. Becker
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

class Event
{
    /** @var string */
    private $datestart;

    /** @var string|null */
    public $dateend;

    /** @var string */
    private $starttime;

    /** @var string|null */
    public $endtime;

    /** @var string */
    public $event;

    /** @var string */
    public $linkadr;

    /** @var string|null */
    public $linktxt;

    /** @var string */
    public $location;

    /** @var int|null */
    public $timestamp = null;

    /** @var string|null */
    public $text = null;

    /** @var string|null */
    public $day = null;

    /** @var string|null */
    public $month = null;

    /** @var string|null */
    public $year = null;

    /** @var string|null */
    public $time = null;

    /** @var string|null */
    public $startday = null;

    /** @var string|null */
    public $startmonth = null;

    /** @var string|null */
    public $startyear = null;

    /** @var string|null */
    public $endday = null;

    /** @var string|null */
    public $endmonth = null;

    /** @var string|null */
    public $endyear = null;

    /** @var int|null */
    public $age = null;

    /**
     * @param string $datestart
     * @param string|null $dateend
     * @param string $starttime
     * @param string|null $endtime
     * @param string $event
     * @param string $linkadr
     * @param string|null $linktxt
     * @param string $location
     */
    public function __construct(
        $datestart,
        $dateend,
        $starttime,
        $endtime,
        $event,
        $linkadr,
        $linktxt,
        $location
    ) {
        $this->datestart = $datestart;
        $this->dateend = $dateend;
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->event = $event;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getDateStart()
    {
        return $this->datestart;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->starttime;
    }

    /**
     * @return int
     */
    public function getStartTimestamp()
    {
        list($year, $month, $day) = explode('-', $this->datestart);
        return mktime(0, 0, 0, (int) $month, (int) $day, (int) $year);
    }

    /**
     * @return int
     */
    public function getEndTimestamp()
    {
        assert($this->dateend !== null);
        list($year, $month, $day) = explode('-', $this->dateend);
        return mktime(0, 0, 0, (int) $month, (int) $day, (int) $year);
    }

    /**
     * @return bool
     */
    public function isBirthday()
    {
        return trim($this->location) === '###';
    }
}
