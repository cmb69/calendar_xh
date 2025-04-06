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
        $this->eventfile = "{$dataFolder}calendar.csv";
    }

    public function getFilename(): string
    {
        return $this->eventfile;
    }

    /** @return array<string,Event> */
    public function readEvents(): array
    {
        $eventfile = dirname($this->eventfile) . "/" . basename($this->eventfile, ".csv");
        if (!is_file("{$eventfile}.csv")) {
            if (is_file("{$eventfile}.txt")) {
                $this->eventfile = "{$eventfile}.txt";
                $events = $this->readOldEvents();
                $this->eventfile = "{$eventfile}.csv";
                $this->writeEvents($events);
            } else {
                if (!is_dir($this->dataFolder) && mkdir($this->dataFolder, 0777)) {
                    chmod($this->dataFolder, 0777);
                }
                touch("{$eventfile}.csv");
            }
        }
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
            flock($stream, LOCK_SH);
            while (($record = fgetcsv($stream, 0, ';', '"', "\0")) !== false) {
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

    /**
     * @param array<string,Event> $events
     * @return list<Event>
     */
    public function filterByMonth(array $events, int $year, int $month): array
    {
        $result = [];
        foreach ($events as $event) {
            if ($event->start->month === $month) {
                if ($event->start->year === $year || ($event->isBirthday() && $event->start->year < $year)) {
                    $result[] = $event;
                }
            }
        }
        uasort($result, function (Event $a, Event $b) use ($year): int {
            $dt1 = $a->isBirthday() ? $a->start->withYear($year) : $a->start;
            $dt2 = $b->isBirthday() ? $b->start->withYear($year) : $b->start;
            return $dt1->compare($dt2);
        });
        return $result;
    }

    /** @param array<string,Event> $events */
    public function findNextEvent(array $events, LocalDateTime $now): ?Event
    {
        $nextevent = null;
        $nextldt = null;
        foreach ($events as $event) {
            if ($event->isBirthday()) {
                $ldt = $event->start->withYear($now->year);
                if ($ldt->compare($now) < 0) {
                    $ldt = $event->start->withYear($now->year + 1);
                }
            } else {
                $ldt = $event->start;
                if ($ldt->compare($now) < 0) {
                    $ldt = $event->end;
                    if ($ldt->compare($now) < 0) {
                        continue;
                    }
                }
            }
            if ($nextldt === null || $ldt->compare($nextldt) < 0) {
                $nextevent = $event;
                $nextldt = $ldt;
            }
        }
        return $nextevent;
    }

    /** @return list<Event> */
    private function readOldEvents(): array
    {
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
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
        $eventfile = $this->eventfile;

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
            $event->summary,
            $event->location,
            $event->linkadr,
            $event->linktxt
        ];
        return fputcsv($fp, $record, ';', '"', "\0") !== false;
    }
}
