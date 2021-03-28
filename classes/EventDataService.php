<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2021 Christoph M. Becker
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
    private $separator;

    /** @var string */
    private $eventfile;

    /**
     * @param string $separator
     */
    public function __construct($separator)
    {
        global $pth, $sl, $cf, $plugin_cf;

        $this->separator = $separator;
        $datapath = $pth['folder']['content'];
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages'] && $sl != $cf['language']['default']) {
            $datapath = dirname($datapath) . '/';
        }
        $this->eventfile = "{$datapath}calendar.csv";
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->eventfile;
    }

    /**
     * @return Event[]
     */
    public function readEvents()
    {
        $eventfile = dirname($this->eventfile) . "/" . basename($this->eventfile, ".csv");
        if (!is_file("{$eventfile}.csv")) {
            if (is_file("{$eventfile}.txt")) {
                $this->eventfile = "{$eventfile}.txt";
                $events = $this->readOldEvents();
                $this->eventfile = "{$eventfile}.csv";
                $this->writeEvents($events);
            } else {
                touch("{$eventfile}.csv");
            }
        }
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
            flock($stream, LOCK_SH);
            while (($record = fgetcsv($stream, 0, ';', '"', "\0")) !== false) {
                assert(is_array($record));
                list($datestart, $starttime, $dateend, $endtime,  $event, $location, $linkadr, $linktxt)
                    = $record;
                if (!$dateend) {
                    $dateend = null;
                }
                if (!$endtime) {
                    $endtime = null;
                }
                if ($datestart != '' && $event != '') {
                    $result[] = new Event(
                        $datestart,
                        $dateend,
                        $starttime,
                        $endtime,
                        $event,
                        $linkadr,
                        $linktxt,
                        $location
                    );
                }
            }
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return $result;
    }

    /**
     * @param Event[] $events
     * @param string $month
     * @return Event[]
     */
    public function filterByMonth(array $events, $month)
    {
        $result = [];
        foreach ($events as $event) {
            if (!$event->isBirthday() && strpos($event->getDateStart(), $month) === 0) {
                $result[] = $event;
            } elseif ($event->isBirthday() && substr($month, 0, 4) >= substr($event->getDateStart(), 0, 4) && strpos($event->getDateStart(), substr($month, 5), 5) === 5) {
                $result[] = $event;
            }
        }
        usort($result, /** @return int */ function (Event $a, Event $b) use ($month) {
            $dt1 = $a->isBirthday() ? new LocalDateTime($month . substr($a->getDateStart(), 7), null) : $a->getStart();
            $dt2 = $b->isBirthday() ? new LocalDateTime($month . substr($a->getDateStart(), 7), null) : $b->getStart();
            return $dt1->compare($dt2);
        });
        /** @var Event[] $result */
        return $result;
    }

    /**
     * @return Event[]
     */
    private function readOldEvents()
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
                    $result[] = new Event(
                        $datestart,
                        $dateend,
                        $starttime,
                        $endtime,
                        $event,
                        $linkadr,
                        $linktxt,
                        $location
                    );
                }
            }
            flock($stream, LOCK_UN);
            fclose($stream);
        }
        return $result;
    }

    /**
     * @param Event[] $events
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

    /**
     * @param resource $fp
     * @return bool
     */
    private function writeEventLine($fp, Event $event)
    {
        $record = [
            $event->getDateStart(),
            $event->getStartTime(),
            $event->getDateEnd(),
            $event->getEndTime(),
            $event->event,
            $event->location,
            $event->linkadr,
            $event->linktxt
        ];
        return fputcsv($fp, $record, ';', '"', "\0") !== false;
    }
}
