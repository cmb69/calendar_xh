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

    /** @var string */
    private $host;

    /** @var Html2Text */
    private $converter;

    public function __construct(string $folder, string $host, Html2Text $converter)
    {
        $this->folder = $folder;
        $this->host = $host;
        $this->converter = $converter;
    }

    public function write(Calendar $calendar): bool
    {
        $stream = fopen($this->folder . "calendar.ics", "w");
        if ($stream === false) {
            return false;
        }
        $written = fwrite($stream, $calendar->toICalendarString($this->converter, $this->host));
        fclose($stream);
        return $written !== false;
    }
}
