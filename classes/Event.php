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
    /**
     * @readonly
     * @var LocalDateTime
     */
    public $start;

    /**
     * @readonly
     * @var LocalDateTime
     */
    public $end;

    /**
     * @readonly
     * @var string
     */
    public $summary;

    /**
     * @readonly
     * @var string
     */
    public $linkadr;

    /**
     * @readonly
     * @var string
     */
    public $linktxt;

    /**
     * @readonly
     * @var string
     */
    public $location;

    /**
     * @param string|null $dateend
     * @param string|null $endtime
     * @return self|null
     */
    public static function create(
        string $datestart,
        $dateend,
        string $starttime,
        $endtime,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location
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
        return new self($start, $end, $summary, $linkadr, $linktxt, $location);
    }

    private function __construct(
        LocalDateTime $start,
        LocalDateTime $end,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->summary = $summary;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
    }

    public function getIsoStartDate(): string
    {
        return $this->start->getIsoDate();
    }

    public function getIsoStartTime(): string
    {
        return $this->start->getIsoTime();
    }

    public function getIsoEndDate(): string
    {
        return $this->end->getIsoDate();
    }

    public function getIsoEndTime(): string
    {
        return $this->end->getIsoTime();
    }

    public function isFullDay(): bool
    {
        return $this->start->hour === 0 && $this->start->minute === 0
            && $this->end->hour === 23 && $this->end->minute === 59;
    }

    public function isBirthday(): bool
    {
        return trim($this->location) === '###';
    }
}
