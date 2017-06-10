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
     * @return array[]
     */
    public function readEvents()
    {
        $result = array();
        if ($stream = fopen($this->eventfile, 'r')) {
            while (($line = fgets($stream)) !== false) {
                list($eventdates, $event, $location, $link, $starttime) = explode(';', rtrim($line));
                list($datestart, $dateend, $endtime) = explode(',', $eventdates);
                list($linkadr, $linktxt) = explode(',', $link);
                if ($datestart != '' && $event != '') {
                    $result[] = compact(
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
     * @param array[] $events
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
        $permissions = false;
        $owner = false;
        $group = false;
        if (is_file($eventfile)) {
            $owner = fileowner($eventfile);
            $group = filegroup($eventfile);
            $permissions = fileperms($eventfile);
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
        // change owner, group and permissions of new file to same as backup file
        if ($owner !== false) {
            chown($eventfile, $owner);
        }
        if ($group !== false) {
            chgrp($eventfile, $group);
        }
        if ($permissions !== false) {
            chmod($eventfile, $permissions);
        }
        return true;
    }

    /**
     * @return string
     */
    private function assembleEventLine(array $entry)
    {
        if ($entry['dateend'] != '') {
            $eventdates = "{$entry['datestart']},{$entry['dateend']},{$entry['endtime']}";
        } else {
            $eventdates = $entry['datestart'];
        }
        $event_time_start = $entry['starttime'];
        $event = $entry['event'];
        $location = $entry['location'];
        if ($entry['linkadr'] != '' || $entry['linktxt'] != '') {
            $link = "{$entry['linkadr']},{$entry['linktxt']}";
        } else {
            $link = '';
        }
        return "$eventdates;$event;$location;$link;$event_time_start\n";
    }
}
