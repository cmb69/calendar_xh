<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
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

    public function __construct()
    {
        global $pth, $sl, $plugin_cf;

        if (!$plugin_cf['calendar']['filepath_data']) {
            $datapath = "{$pth['folder']['plugins']}calendar/content/";
        } else {
            $datapath = $plugin_cf['calendar']['filepath_data'];
        }
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages']) {
            $this->eventfile = "{$datapath}eventcalendar.txt";
        } else {
            $this->eventfile = "{$datapath}eventcalendar_{$sl}.txt";
        }
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
            while (($line = fgets($stream)) !== false) {
                list($eventdates, $event, $location, $link, $starttime) = explode(';', rtrim($line));
                if (strpos($eventdates, ',') !== false) {
                    list($datestart, $dateend, $endtime) = explode(',', $eventdates);
                } else {
                    $datestart = $eventdates;
                    $dateend = null;
                    $endtime = null;
                }
                if (strpos($link, ',') !== false) {
                    list($linkadr, $linktxt) = explode(',', $link);
                } else {
                    $linkadr = $link;
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
            if (!fwrite($fp, $this->assembleEventLine($entry))) {
                fclose($fp);
                return false;
            }
        }
        fclose($fp);
        return true;
    }

    /**
     * @return string
     */
    private function assembleEventLine(stdClass $entry)
    {
        if ($entry->dateend != '') {
            $eventdates = "{$entry->datestart},{$entry->dateend},{$entry->endtime}";
        } else {
            $eventdates = $entry->datestart;
        }
        $event_time_start = $entry->starttime;
        $event = $entry->event;
        $location = $entry->location;
        if ($entry->linkadr != '' || $entry->linktxt != '') {
            $link = "{$entry->linkadr},{$entry->linktxt}";
        } else {
            $link = '';
        }
        return "$eventdates;$event;$location;$link;$event_time_start\n";
    }
}
