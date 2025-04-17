<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2023 Christoph M. Becker
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

class BirthdayEvent extends Event
{
    /** @var YearlyRecurrence */
    private $recurrence;

    /** @var int */
    private $age = 0;

    public function __construct(
        LocalDateTime $start,
        LocalDateTime $end,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location
    ) {
        parent::__construct($start, $end, $summary, $linkadr, $linktxt, $location);
        $this->recurrence = new YearlyRecurrence($start, $end);
    }

    public function age(): int
    {
        return $this->age;
    }

    public function occurrenceDuring(int $year, int $month): ?self
    {
        $matches = $this->recurrence->matchesInMonth($year, $month);
        if (empty($matches)) {
            return null;
        }
        assert(count($matches) === 1);
        return $this->occurrenceStartingAt($matches[0]);
    }

    public function occurrenceOn(LocalDateTime $day, bool $daysBetween): ?self
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $match = $this->recurrence->matchOnDay($day->year(), $day->month(), $day->day());
        if ($match === null) {
            return null;
        }
        return $this->occurrenceStartingAt($match);
    }

    /** @return array{?self,?LocalDateTime} */
    public function earliestOccurrenceAfter(LocalDateTime $date): array
    {
        $match = $this->recurrence->firstMatchAfter($date);
        if ($match === null) {
            return [null, null];
        }
        return [$this->occurrenceStartingAt($match), $match];
    }

    public function occurrenceStartingAt(LocalDateTime $start): self
    {
        $that = parent::occurrenceStartingAt($start);
        $that->age = $start->year() - $this->start()->year();
        return $that;
    }

    protected function locationToICalendarString(): string
    {
        return "RRULE:FREQ=YEARLY\r\n";
    }
}
