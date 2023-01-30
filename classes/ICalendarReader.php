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

    /** @var string */
    private $currentLine = "";

    /**
     * @var Event[]
     */
    private $events = [];

    /** @var array<string,string> */
    private $currentEvent = [];

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
        $this->currentEvent = [];
        $isInEvent = false;
        foreach ($this->lines as $this->currentLine) {
            if ($isInEvent) {
                if ($this->currentLine === 'END:VEVENT') {
                    $isInEvent = false;
                    $maybeEvent = Event::create(
                        $this->currentEvent['datestart'] ?? "", // @phpstan-ignore-line
                        $this->currentEvent['dateend'] ?? "", // @phpstan-ignore-line
                        $this->currentEvent['starttime'] ?? "", // @phpstan-ignore-line
                        $this->currentEvent['endtime'] ?? "", // @phpstan-ignore-line
                        $this->currentEvent['event'] ?? "", // @phpstan-ignore-line
                        '',
                        '',
                        $this->currentEvent['location'] ?? "" // @phpstan-ignore-line
                    );
                    if ($maybeEvent !== null) {
                        $this->events[] = $maybeEvent;
                    }
                } else {
                    $this->processPropertyLine();
                }
            } else {
                if ($this->currentLine === 'BEGIN:VEVENT') {
                    $isInEvent = true;
                    $this->currentEvent = [];
                }
            }
        }
    }

    /**
     * @return void
     */
    private function processPropertyLine()
    {
        list($property, $param, $value) = $this->parseLine();
        assert($property !== null);
        assert($value !== null);
        switch ($property) {
            case 'SUMMARY':
                $this->currentEvent['event'] = $value;
                return;
            case 'LOCATION':
                $this->currentEvent['location'] = $value;
                return;
            case 'DTSTART':
                $this->processDtStart($param, $value);
                return;
            case 'DTEND':
                $this->processDtEnd($param, $value);
                return;
        }
    }

    /**
     * @param string|null $param
     * @return void
     */
    private function processDtStart($param, string $value)
    {
        if ($param === null) {
            if (($datetime = $this->parseDateTime($value))) {
                list($this->currentEvent['datestart'], $this->currentEvent['starttime']) = $datetime;
            }
        } elseif ($param === "VALUE=DATE") {
            if (($date = $this->parseDate($value))) {
                $this->currentEvent['datestart'] = $date;
            }
        }
    }

    /**
     * @param string|null $param
     * @return void
     */
    private function processDtEnd($param, string $value)
    {
        if ($param === null) {
            if (($datetime = $this->parseDateTime($value))) {
                list($this->currentEvent['dateend'], $this->currentEvent['endtime']) = $datetime;
            }
        } elseif ($param === "VALUE=DATE") {
            if (($date = $this->parseDate($value))) {
                $this->currentEvent['dateend'] = $date;
            }
        }
    }

    /**
     * ignores property parameters
     *
     * @return (string|null)[]
     */
    private function parseLine(): array
    {
        list($property, $value) = explode(':', $this->currentLine);
        $parts = explode(';', $property);
        $property = $parts[0];
        if (count($parts) > 1) {
            $param = $parts[1];
        } else {
            $param = null;
        }
        $value = preg_replace('/\\\\(?!\\\\)/', '', $value);
        return [$property, $param, $value];
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

    /**
     * ignores the timezone
     *
     * @return string|false
     */
    private function parseDate(string $value)
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $value, $matches)) {
            return "$matches[1]-$matches[2]-$matches[3]";
        }
        return false;
    }
}
