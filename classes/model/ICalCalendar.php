<?php

/**
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

use Calendar\Infra\Html2Text;

trait ICalCalendar
{
    /**
     * @param list<string> $lines
     * @param mixed $count
     * @param-out int $count
     */
    public static function fromICalendar(array $lines, &$count): self
    {
        $that = new self([]);
        for ($i = count($lines) - 1; $i > 0; $i--) { // unfold lines
            if (in_array(substr($lines[$i], 0, 1), [' ', "\t"])) {
                $lines[$i - 1] .= substr($lines[$i], 1);
                unset($lines[$i]);
            }
        }
        $count = self::parse($that, $lines);
        return $that;
    }

    /** @param list<string> $lines */
    private static function parse(self $that, array $lines): int
    {
        $count = 0;
        $line = reset($lines);
        while ($line !== false) {
            if ($line === "BEGIN:VEVENT") {
                $count++;
                $event = Event::fromICalendar($lines);
                if ($event !== null) {
                    $id = $event->id() ?: sha1(serialize($event));
                    $that->events[$id] = $event;
                }
            }
            $line = next($lines);
        }
        return $count;
    }

    public function toICalendarString(Html2Text $converter, string $host): string
    {
        $res = "BEGIN:VCALENDAR\r\n"
            . "PRODID:-//3-magi.net//Calendar_XH//EN\r\n"
            . "VERSION:2.0\r\n";
        foreach ($this->events() as $id => $event) {
            $res .= $event->toICalendarString($id, $converter, $host);
        }
        $res .= "END:VCALENDAR\r\n";
        return $res;
    }
}
