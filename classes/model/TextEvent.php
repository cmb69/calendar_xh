<?php

/**
 * Copyright (c) Christoph M. Becker
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

trait TextEvent
{
    /** @param non-empty-string $separator */
    public static function fromText(string $line, string $separator): ?self
    {
        list($eventdates, $event, $location, $link, $starttime) = explode(';', rtrim($line));
        if (strpos($eventdates, ',') !== false) {
            list($datestart, $dateend, $endtime) = explode(',', $eventdates);
        } else {
            $datestart = $eventdates;
            $dateend = "";
            $endtime = "";
        }
        if ($datestart) {
            list($day, $month, $year) = explode($separator, $datestart);
            $datestart = "$year-$month-$day";
        }
        if ($dateend) {
            list($day, $month, $year) = explode($separator, $dateend);
            $dateend = "$year-$month-$day";
        }
        if (strpos($link, ',') !== false) {
            list($linkadr, $linktxt) = explode(',', $link);
        } else {
            $linkadr = $link;
            $linktxt = '';
        }
        if (strpos($linkadr, 'ext:') === 0) {
            $linkadr = 'http://' . substr($linkadr, 4);
        } elseif (strpos($linkadr, 'int:') === 0) {
            $linkadr = '?' . substr($linkadr, 4);
        } elseif ($linkadr) {
            $linktxt = "{$linkadr};{$linktxt}";
        }
        if ($datestart != '' && $event != '') {
            [$start, $end] = self::dateTimes($datestart, $dateend, $starttime, $endtime, $location);
            if ($start === null || $end === null) {
                return null;
            }
            $recurrence = self::createRecurrence("", $start, $end, "", $location);
            return new self("", $start, $end, $event, $linkadr, $linktxt, $location, $recurrence);
        }
        return null;
    }
}
