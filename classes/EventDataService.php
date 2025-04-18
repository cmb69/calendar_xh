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

namespace Calendar;

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
        if (!file_exists($this->eventfile)) {
            touch($this->eventfile);
        }
        return $this->eventfile;
    }

    public function readEvents(): Calendar
    {
        $eventfile = dirname($this->eventfile) . "/" . basename($this->eventfile, ".2.6.csv");
        if (!is_file("{$eventfile}.2.6.csv")) {
            if (!is_file("{$eventfile}.csv")) {
                if (is_file("{$eventfile}.txt")) {
                    $events = $this->readOldEvents("{$eventfile}.txt");
                    $this->writeEvents($events);
                }
            } else {
                $events = $this->doReadEvents("{$eventfile}.csv", true);
                $this->writeEvents($events);
            }
        }
        $events = $this->doReadEvents($this->getFilename());
        return new Calendar($events);
    }

    /** @return array<string,Event> */
    private function doReadEvents(string $filename, bool $convertToHtml = false): array
    {
        $result = array();
        if ($stream = fopen($filename, "r")) {
            flock($stream, LOCK_SH);
            while (($record = fgetcsv($stream, 0, ";", '"', "\0")) !== false) {
                if (!$this->validateRecord($record)) {
                    continue;
                }
                $id = md5(serialize($record));
                list($datestart, $starttime, $dateend, $endtime,  $event, $location, $linkadr, $linktxt)
                    = $record;
                if (!$dateend) {
                    $dateend = null;
                }
                if (!$endtime) {
                    $endtime = null;
                }
                if ($convertToHtml) {
                    $linktxt = XH_hsc($linktxt);
                    if ($linkadr) {
                        $target = (strpos($linkadr, "://") === false) ? "_self" : "_blank";
                        $title = XH_hsc($event);
                        $text = $linktxt ?: XH_hsc($linkadr);
                        $url = XH_hsc($linkadr);
                        $linktxt = "<a href=\"{$url}\" target=\"{$target}\" title=\"{$title}\">"
                            . "{$text}</a>";
                    }
                }
                if ($datestart != '' && $event != '') {
                    $maybeEvent = Event::create(
                        $datestart,
                        $dateend,
                        $starttime,
                        $endtime,
                        $event,
                        $linkadr,
                        $linktxt,
                        $location
                    );
                    if ($maybeEvent !== null) {
                        $result[$id] = $maybeEvent;
                    }
                }
            }
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return $result;
    }
    /**
     * @param ?list<?string> $record
     * @phpstan-assert-if-true list<string> $record
     */
    private function validateRecord(?array $record): bool
    {
        if ($record === null) {
            return false;
        }
        foreach ($record as $field) {
            if (!is_string($field)) {
                return false;
            }
        }
        return true;
    }

    /** @return list<Event> */
    private function readOldEvents(string $eventfile): array
    {
        $result = array();
        if ($stream = fopen($eventfile, 'r')) {
            flock($stream, LOCK_SH);
            while (($line = fgets($stream)) !== false) {
                list($eventdates, $event, $location, $link, $starttime) = explode(';', rtrim($line));
                if (strpos($eventdates, ',') !== false) {
                    list($datestart, $dateend, $endtime) = explode(',', $eventdates);
                } else {
                    $datestart = $eventdates;
                    $dateend = null;
                    $endtime = null;
                }
                if ($datestart) {
                    list($day, $month, $year) = explode($this->separator, $datestart);
                    $datestart = "$year-$month-$day";
                }
                if ($dateend) {
                    list($day, $month, $year) = explode($this->separator, $dateend);
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
                    $maybeEvent = Event::create(
                        $datestart,
                        $dateend,
                        $starttime,
                        $endtime,
                        $event,
                        $linkadr,
                        $linktxt,
                        $location
                    );
                    if ($maybeEvent !== null) {
                        $result[] = $maybeEvent;
                    }
                }
            }
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return $result;
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
        flock($fp, LOCK_EX);
        foreach ($events as $event) {
            if (!$this->writeEventLine($fp, $event)) {
                flock($fp, LOCK_UN);
                fclose($fp);
                return false;
            }
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /** @param resource $fp */
    private function writeEventLine($fp, Event $event): bool
    {
        $record = [
            $event->getIsoStartDate(),
            $event->getIsoStartTime(),
            $event->getIsoEndDate(),
            $event->getIsoEndTime(),
            $event->summary(),
            $event->location(),
            $event->linkadr(),
            $event->linktxt()
        ];
        return fputcsv($fp, $record, ';', '"', "\0") !== false;
    }
}
