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

use Calendar\Html2Text;

trait ICalendar
{
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
