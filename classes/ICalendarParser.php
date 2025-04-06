<?php

/**
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

/**
 * Read all events from an iCalendar file
 *
 * @see <https://tools.ietf.org/html/rfc2445>
 */
class ICalendarParser
{
    /** @var list<string> */
    private $lines = [];

    /** @var string */
    private $currentLine = "";

    /** @var array<Event> */
    private $events = [];

    /** @var array<string,string> */
    private $currentEvent = [];

    /**
     * @param list<string> $lines
     * @return array<Event>
     */
    public function parse(array $lines): array
    {
        $this->lines = $lines;
        $this->unfold();
        $this->doParse();
        return $this->events;
    }

    private function unfold(): void
    {
        for ($i = count($this->lines) - 1; $i > 0; $i--) {
            if (in_array(substr($this->lines[$i], 0, 1), [' ', "\t"])) {
                $this->lines[$i - 1] .= substr($this->lines[$i], 1);
                unset($this->lines[$i]);
            }
        }
    }

    private function doParse(): void
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
                        $this->currentEvent['linkadr'] ?? "", // @phpstan-ignore-line
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

    private function processPropertyLine(): void
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
            case 'URL':
                $this->currentEvent['linkadr'] = $value;
                return;
            case 'DTSTART':
                $this->processDtStart($param, $value);
                return;
            case 'DTEND':
                $this->processDtEnd($param, $value);
                return;
        }
    }

    private function processDtStart(?string $param, string $value): void
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

    private function processDtEnd(?string $param, string $value): void
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
     * @return array<?string>
     */
    private function parseLine(): array
    {
        list($property, $value) = explode(':', $this->currentLine, 2);
        $parts = explode(';', $property);
        $property = $parts[0];
        if (count($parts) > 1) {
            $param = $parts[1];
        } else {
            $param = null;
        }
        $value = str_replace(["\\\\", "\\;", "\\,", "\\N", "\\n"], ["\\", ";", ",", "\n", "\n"], $value);
        // $value = preg_replace('/\\\\(?!\\\\)/', '', $value);
        return [$property, $param, $value];
    }

    /**
     * ignores the timezone
     *
     * @return ?array{string,string}
     */
    private function parseDateTime(string $value): ?array
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})/', $value, $matches)) {
            return ["$matches[1]-$matches[2]-$matches[3]", "$matches[4]:$matches[5]"];
        }
        return null;
    }

    /**
     * ignores the timezone
     */
    private function parseDate(string $value): ?string
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $value, $matches)) {
            return "$matches[1]-$matches[2]-$matches[3]";
        }
        return null;
    }
}
