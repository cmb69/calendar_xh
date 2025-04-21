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

namespace Calendar\Infra;

use Calendar\Model\Calendar;
use Calendar\Model\Event;

class EventDataService
{
    /** @var string */
    private $dataFolder;

    /** @var non-empty-string */
    private $separator;

    /** @var string */
    private $eventfile;

    /** @param non-empty-string $separator */
    public function __construct(string $dataFolder, string $separator)
    {
        $this->dataFolder = $dataFolder;
        $this->separator = $separator;
        $this->eventfile = "{$dataFolder}calendar.2.6.csv";
    }

    public function getFilename(): string
    {
        if (!is_dir($this->dataFolder) && mkdir($this->dataFolder, 0777)) {
            chmod($this->dataFolder, 0777);
        }
        return $this->eventfile;
    }

    /** @return array<string,Event> */
    public function readEvents(): array
    {
        $eventfile = dirname($this->eventfile) . "/" . basename($this->eventfile, ".2.6.csv");
        if (!is_file("{$eventfile}.2.6.csv")) {
            if (!is_file("{$eventfile}.csv")) {
                if (!is_file("{$eventfile}.txt")) {
                    $events = [];
                } else {
                    $events = $this->readOldEvents("{$eventfile}.txt");
                }
            } else {
                $events = $this->doReadEvents("{$eventfile}.csv", true);
            }
        } else {
            $events = $this->doReadEvents($this->getFilename());
        }
        return $events;
    }

    /** @return array<string,Event> */
    private function doReadEvents(string $filename, bool $convertToHtml = false): array
    {
        $contents = "";
        if (is_readable($filename) && $stream = fopen($filename, "r")) {
            flock($stream, LOCK_SH);
            $contents = (string) stream_get_contents($stream);
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return Calendar::fromCsv($contents, $convertToHtml)->events();
    }

    /** @return array<string,Event> */
    private function readOldEvents(string $eventfile): array
    {
        $contents = "";
        if ($stream = fopen($eventfile, 'r')) {
            flock($stream, LOCK_SH);
            $contents = (string) stream_get_contents($stream);
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return Calendar::fromText($contents, $this->separator)->events();
    }

    /** @param array<Event> $events */
    public function writeEvents(array $events): bool
    {
        $eventfile = $this->getFilename();

        // remove old backup
        if (is_file("{$eventfile}.bak")) {
            unlink("{$eventfile}.bak");
        }
        // create new backup
        if (is_file($eventfile)) {
            rename($eventfile, "{$eventfile}.bak");
        }

        $fp = fopen($eventfile, "c");
        if ($fp === false) {
            return false;
        }
        $contents = Calendar::fromEvents($events)->toCsvString();
        flock($fp, LOCK_EX);
        $ok = fwrite($fp, $contents) === strlen($contents);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $ok;
    }
}
