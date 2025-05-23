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

trait TextCalendar
{
    public static function fromText(string $contents): self
    {
        $that = new self([]);
        $lines = explode("\n", $contents);
        foreach ($lines as $line) {
            $line = rtrim($line);
            $event = Event::fromText($line);
            if ($event !== null) {
                $id = sha1($line);
                $that->events[$id] = $event;
            }
        }
        return $that;
    }
}
