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
    /** @var LocalDateTime */
    private $start;

    /** @var LocalDateTime|null */
    private $end;

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
    public $endday = null;

    /** @var string|null */
    public $endmonth = null;

    /** @var string|null */
    public $endyear = null;

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
        $this->start = new LocalDateTime($datestart ?: null, $starttime ?: null);
        $this->end = $dateend !== null && $dateend !== '' ? new LocalDateTime($dateend, $endtime) : null;
        $this->event = $event;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
    }

    /**
     * @return LocalDateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return string
     */
    public function getDateStart()
    {
        return $this->start->getDate();
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->start->getTime();
    }

    /**
     * @return string|null
     */
    public function getDateEnd()
    {
        return $this->end !== null ? $this->end->getDate() : null;
    }

    /**
     * @return string|null
     */
    public function getEndTime()
    {
        return $this->end !== null ? $this->end->getTime() : null;
    }

    /**
     * @return int
     */
    public function getStartTimestamp()
    {
        return mktime(0, 0, 0, $this->start->getMonth(), $this->start->getDay(), $this->start->getYear());
    }

    /**
     * @return int
     */
    public function getEndTimestamp()
    {
        assert($this->end !== null);
        return mktime(0, 0, 0, $this->end->getMonth(), $this->end->getDay(), $this->end->getYear());
    }

    /**
     * @return bool
     */
    public function isBirthday()
    {
        return trim($this->location) === '###';
    }
}
