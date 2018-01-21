<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2018 Christoph M. Becker
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

use stdClass;

class EventDataService
{
    /**
     * @var string
     */
    private $eventfile;

    /**
     * @param string $separator
     */
    public function __construct($separator)
    {
        global $pth, $sl, $cf, $plugin_cf;

        $datapath = $pth['folder']['content'];
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages'] && $sl != $cf['language']['default']) {
            $datapath = dirname($datapath) . '/';
        }
        $eventfile = "{$datapath}calendar";
        if (!file_exists("{$eventfile}.csv")) {
            if (file_exists("{$eventfile}.txt")) {
                $this->eventfile = "{$eventfile}.txt";
                $events = $this->readOldEvents($separator);
                $this->eventfile = "{$eventfile}.csv";
                $this->writeEvents($events);
            } else {
                touch("{$eventfile}.csv");
            }
        }
        $this->eventfile = "{$eventfile}.csv";
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->eventfile;
    }

    /**
     * @return stdClass[]
     */
    public function readEvents()
    {
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
            while (($record = fgetcsv($stream, 0, ';', '"', "\0")) !== false) {
                list($datestart, $starttime, $dateend, $endtime,  $event, $location, $linkadr, $linktxt)
                    = $record;
                if (!$dateend) {
                    $dateend = null;
                }
                if (!$endtime) {
                    $endtime = null;
                }
                if (!$linktxt) {
                    $linktxt = null;
                }
                if ($datestart != '' && $event != '') {
                    $result[] = (object) compact(
                        'datestart',
                        'dateend',
                        'starttime',
                        'endtime',
                        'event',
                        'linkadr',
                        'linktxt',
                        'location'
                    );
                }
            }
            fclose($stream);
        }
        return $result;
    }

    /**
     * @param stdClass[] $events
     * @param string $month
     * @return stdClass[]
     */
    public function filterByMonth(array $events, $month)
    {
        $result = [];
        foreach ($events as $event) {
            $isBirthday = trim($event->location) === '###';
            if (!$isBirthday && strpos($event->datestart, $month) === 0) {
                $result[] = $event;
            } elseif ($isBirthday && substr($month, 0, 4) >= substr($event->datestart, 0, 4) && strpos($event->datestart, substr($month, 5), 5) === 5) {
                $newevent = clone $event;
                $newevent->age = substr($month, 0, 4) - substr($newevent->datestart, 0, 4);
                $newevent->datestart = $month . substr($newevent->datestart, 7);
                $result[] = $newevent;
            }
        }
        usort($result, function ($a, $b) {
            return strcmp("{$a->datestart}T{$a->starttime}", "{$b->datestart}T{$b->starttime}");
        });
        return $result;
    }

    /**
     * @param string $separator
     * @return stdClass[]
     */
    private function readOldEvents($separator)
    {
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
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
                    $linktxt = null;
                }
                if (strpos($linkadr, 'ext:') === 0) {
                    $linkadr = 'http://' . substr($linkadr, 4);
                } elseif (strpos($linkadr, 'int:') === 0) {
                    $linkadr = '?' . substr($linkadr, 4);
                } elseif ($linkadr) {
                    $linktxt = "{$linkadr};{$linktxt}";
                }
                if ($datestart != '' && $event != '') {
                    $result[] = (object) compact(
                        'datestart',
                        'dateend',
                        'starttime',
                        'endtime',
                        'event',
                        'linkadr',
                        'linktxt',
                        'location'
                    );
                }
            }
            fclose($stream);
        }
        return $result;
    }

    /**
     * @param stdClass[] $events
     * @return bool
     */
    public function writeEvents(array $events)
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
    
        $fp = fopen($eventfile, "w");
        if ($fp === false) {
            return false;
        }
        foreach ($events as $entry) {
            if (!$this->writeEventLine($fp, $entry)) {
                fclose($fp);
                return false;
            }
        }
        fclose($fp);
        return true;
    }

    /**
     * @param resource $fp
     * @return bool
     */
    private function writeEventLine($fp, stdClass $entry)
    {
        $record = [
            $entry->datestart,
            $entry->starttime,
            $entry->dateend,
            $entry->endtime,
            $entry->event,
            $entry->location,
            $entry->linkadr,
            $entry->linktxt
        ];
        return fputcsv($fp, $record, ';', '"', "\0") !== false;
    }
}
