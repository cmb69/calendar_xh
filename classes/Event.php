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

    /** @var LocalDateTime */
    private $end;

    /** @var string */
    public $event;

    /** @var string */
    public $linkadr;

    /** @var string */
    public $linktxt;

    /** @var string */
    public $location;

    /**
     * @param string $datestart
     * @param string|null $dateend
     * @param string $starttime
     * @param string|null $endtime
     * @param string $event
     * @param string $linkadr
     * @param string $linktxt
     * @param string $location
     * @return self|null
     */
    public static function create(
        $datestart,
        $dateend,
        $starttime,
        $endtime,
        $event,
        $linkadr,
        $linktxt,
        $location
    ) {
        if (!$dateend) {
            if ($endtime) {
                return null;
            }
            $endtime = $starttime ? $starttime : "23:59";
            if (($end = LocalDateTime::fromIsoString("{$datestart}T{$endtime}")) === null) {
                return null;
            }
        } else {
            if (!$endtime) {
                $endtime = $starttime ? $starttime : "23:59";
            }
            if (($end = LocalDateTime::fromIsoString("{$dateend}T{$endtime}")) === null) {
                return null;
            }
        }
        if ($starttime === '') {
            $starttime = "00:00";
        }
        if (($start = LocalDateTime::fromIsoString("{$datestart}T{$starttime}")) === null) {
            return null;
        }
        return new self($start, $end, $event, $linkadr, $linktxt, $location);
    }

    /**
     * @param string $event
     * @param string $linkadr
     * @param string $linktxt
     * @param string $location
     */
    private function __construct(
        LocalDateTime $start,
        LocalDateTime $end,
        $event,
        $linkadr,
        $linktxt,
        $location
    ) {
        $this->start = $start;
        $this->end = $end;
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
     * @return LocalDateTime|null
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return string|null
     */
    public function getDateEnd()
    {
        return $this->end->getDate();
    }

    /**
     * @return string|null
     */
    public function getEndTime()
    {
        return $this->end->getTime();
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
