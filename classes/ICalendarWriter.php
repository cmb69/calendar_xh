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

namespace Calendar;

use Calendar\Model\Calendar;

class ICalendarWriter
{
    /** @var string */
    private $folder;

    public function __construct(string $folder)
    {
        $this->folder = $folder;
    }

    public function write(Calendar $calendar): bool
    {
        $stream = fopen($this->folder . "calendar.ics", "c");
        if ($stream === false) {
            return false;
        }
        $written = fwrite($stream, $calendar->toICalendarString());
        fclose($stream);
        return $written !== false;
    }
}
