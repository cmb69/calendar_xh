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
    /** @var int */
    private $age = 0;

    public function age(): int
    {
        return $this->age;
    }

    public function occurrenceDuring(int $year, int $month): ?self
    {
        if (
            $this->start()->month() === $month
            && $this->start()->year() <= $year
        ) {
            return $this->birthdayOccurrenceIn($year);
        }
        return null;
    }

    public function occurrenceOn(LocalDateTime $day, bool $daysBetween): ?self
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $start = $this->start();
        if ($start->year() <= $day->year() && $start->month() === $day->month() && $start->day() === $day->day()) {
            return $this->birthdayOccurrenceIn($day->year());
        }
        return null;
    }

    /** @return array{?self,?LocalDateTime} */
    public function earliestOccurrenceAfter(LocalDateTime $date): array
    {
        if ($this->start()->year() <= $date->year()) {
            $ldt = $this->start()->withYear($date->year());
            if ($ldt->compare($date) < 0) {
                $ldt = $this->end();
                if ($ldt->compare($date) < 0) {
                    $ldt = $this->start()->withYear($date->year() + 1);
                }
            }
        } else {
            $ldt = null;
        }
        return [$ldt === null ? null : $this->birthdayOccurrenceIn($ldt->year()), $ldt];
    }

    public function birthdayOccurrenceIn(int $year): self
    {
        $that = new self(
            $this->start()->withYear($year),
            $this->end()->withYear($year),
            $this->summary(),
            $this->linkadr(),
            $this->linktxt(),
            $this->location()
        );
        $that->age = $year - $this->start()->year();
        return $that;
    }

    protected function locationToICalendarString(): string
    {
        return "RRULE:FREQ=YEARLY\r\n";
    }
}
