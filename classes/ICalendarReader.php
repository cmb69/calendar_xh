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
     * @var string[]
     */
    private $lines = [];

    /**
     * @var Event[]
     */
    private $events = [];

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return Event[]
     */
    public function read(): array
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
        /** @var array<string,string> $event */
        $event = [];
        $isInEvent = false;
        foreach ($this->lines as $line) {
            if ($isInEvent) {
                if ($line === 'END:VEVENT') {
                    $isInEvent = false;
                    $maybeEvent = Event::create(
                        $event['datestart'] ?? "",
                        $event['dateend'] ?? "",
                        $event['starttime'] ?? "",
                        $event['endtime'] ?? "",
                        $event['event'] ?? "",
                        '',
                        '',
                        $event['location'] ?? ""
                    );
                    if ($maybeEvent !== null) {
                        $this->events[] = $maybeEvent;
                    }
                } else {
                    list($property, $value) = $this->parseLine($line);
                    switch ($property) {
                        case 'SUMMARY':
                            $event['event'] = $value;
                            break;
                        case 'LOCATION':
                            $event['location'] = $value;
                            break;
                        case 'DTSTART':
                            if (($datetime = $this->parseDateTime($value))) {
                                list($event['datestart'], $event['starttime']) = $datetime;
                            }
                            break;
                        case 'DTEND':
                            if (($datetime = $this->parseDateTime($value))) {
                                list($event['dateend'], $event['endtime']) = $datetime;
                            }
                            break;
                    }
                }
            } else {
                if ($line === 'BEGIN:VEVENT') {
                    $isInEvent = true;
                    $event = [];
                }
            }
        }
    }

    /**
     * ignores property parameters
     *
     * @return string[]
     */
    private function parseLine(string $line): array
    {
        list($property, $value) = explode(':', $line);
        list($property) = explode(';', $property);
        $value = preg_replace('/\\\\(?!\\\\)/', '', $value);
        return [$property, $value];
    }

    /**
     * ignores the timezone
     *
     * @return string[]|false
     */
    private function parseDateTime(string $value)
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})/', $value, $matches)) {
            return ["$matches[1]-$matches[2]-$matches[3]", "$matches[4]:$matches[5]"];
        }
        return false;
    }
}
