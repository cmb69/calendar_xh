<?php

/**
 * Copyright 2023 Christoph M. Becker
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

namespace Calendar\Infra;

use Calendar\Model\Calendar;
use DirectoryIterator;

class ICalendarRepo
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

    /** @return list<string> */
    public function all(): array
    {
        $result = [];
        foreach (new DirectoryIterator($this->folder) as $file) {
            if ($file->isFile() && $file->getExtension() === 'ics') {
                $result[] = $file->getFilename();
            }
        }
        return $result;
    }

    /**
     * @param mixed $count
     * @phpstan-param-out int $count
     */
    public function find(string $name, &$count): Calendar
    {
        return Calendar::fromICalendar($this->read($name), $count);
    }

    /** @return list<string> */
    private function read(string $filename): array
    {
        $lines = file("{$this->folder}$filename", FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }
        return $lines;
    }

    public function write(string $name, Calendar $calendar): bool
    {
        $stream = fopen($this->folder . "$name.ics", "w");
        if ($stream === false) {
            return false;
        }
        $written = fwrite($stream, $calendar->toICalendarString($this->converter, $this->host));
        fclose($stream);
        return $written !== false;
    }
}
