<?php

/**
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

use stdClass;

/**
 * Read all events from an iCalendar file
 *
 * @see <https://tools.ietf.org/html/rfc2445>
 */
class ICalendarReader
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string[]
     */
    private $lines = [];

    /**
     * @var array
     */
    private $events = [];

    /**
     * @param string $filename
     * @param string $separator
     */
    public function __construct($filename, $separator)
    {
        $this->filename = $filename;
        $this->separator = $separator;
    }

    /**
     * @return stdClass[]
     */
    public function read()
    {
        $this->lines = file($this->filename, FILE_IGNORE_NEW_LINES);
        $this->unfold();
        $this->parse();
        return $this->events;
    }

    /**
     * @return void
     */
    private function unfold()
    {
        for ($i = count($this->lines) - 1; $i > 0; $i--) {
            if (in_array(substr($this->lines[$i], 0, 1), [' ', "\t"])) {
                $this->lines[$i - 1] .= substr($this->lines[$i], 1);
                unset($this->lines[$i]);
            }
        }
    }

    /**
     * @return void
     */
    private function parse()
    {
        $isInEvent = false;
        foreach ($this->lines as $line) {
            if ($isInEvent) {
                assert(isset($event));
                if ($line === 'END:VEVENT') {
                    $isInEvent = false;
                    $this->events[] = (object) $event;
                } else {
                    list($property, $value) = $this->parseLine($line);
                    switch ($property) {
                        case 'SUMMARY':
                            $event->event = $value;
                            break;
                        case 'LOCATION':
                            $event->location = $value;
                            break;
                        case 'DTSTART':
                            if (($datetime = $this->parseDateTime($value))) {
                                list($event->datestart, $event->starttime) = $datetime;
                            }
                            break;
                        case 'DTEND':
                            if (($datetime = $this->parseDateTime($value))) {
                                list($event->dateend, $event->endtime) = $datetime;
                            }
                            break;
                    }
                }
            } else {
                if ($line === 'BEGIN:VEVENT') {
                    $isInEvent = true;
                    $event = $this->createEvent();
                }
            }
        }
    }

    /**
     * ignores property parameters
     *
     * @param string $line
     * @return string[]
     */
    private function parseLine($line)
    {
        list($property, $value) = explode(':', $line);
        list($property) = explode(';', $property);
        $value = preg_replace('/\\\\(?!\\\\)/', '', $value);
        return [$property, $value];
    }

    /**
     * ignores the timezone
     *
     * @param string $value
     * @return string[]|false
     */
    private function parseDateTime($value)
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})/', $value, $matches)) {
            return ["$matches[3]{$this->separator}$matches[2]{$this->separator}$matches[1]", "$matches[4]:$matches[5]"];
        }
        return false;
    }

    /**
     * @return stdClass
     */
    private function createEvent()
    {
        return (object) ['datestart' => '', 'starttime' => '', 'dateend' => '',
        'endtime' => '', 'event' => '', 'location' => '', 'linkadr' => '', 'linktxt' => ''];
    }
}
