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

use Calendar\Model\Event;

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

    /** @var int */
    private $eventCount;

    /**
     * @param list<string> $lines
     * @return array<Event>
     */
    public function parse(array $lines): array
    {
        $this->lines = $lines;
        $this->eventCount = 0;
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
                        $this->currentEvent['location'] ?? "", // @phpstan-ignore-line,
                        $this->currentEvent['recur'] ?? "", // @phpstan-ignore-line
                        $this->currentEvent['until'] ?? "", // @phpstan-ignore-line
                    );
                    if ($maybeEvent !== null) {
                        $this->events[] = $maybeEvent;
                    }
                } else {
                    $this->processPropertyLine();
                }
            } else {
                if ($this->currentLine === 'BEGIN:VEVENT') {
                    $this->eventCount++;
                    $isInEvent = true;
                    $this->currentEvent = [];
                }
            }
        }
    }

    private function processPropertyLine(): void
    {
        ["property" => $property, "params" => $params, "value" => $value] = $this->parseLine();
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
                $this->processDtStart($params, $value);
                return;
            case 'DTEND':
                $this->processDtEnd($params, $value);
                return;
            case 'RRULE':
                $this->processRrule($value);
                return;
        }
    }

    /** @param array<string,string> $params */
    private function processDtStart(array $params, string $value): void
    {
        switch ($params["VALUE"] ?? "DATE-TIME") {
            case "DATE-TIME":
                if (($datetime = $this->parseDateTime($value))) {
                    list($this->currentEvent['datestart'], $this->currentEvent['starttime']) = $datetime;
                }
                break;
            case "DATE":
                if (($date = $this->parseDate($value))) {
                    $this->currentEvent['datestart'] = $date;
                }
                break;
        }
    }

    /** @param array<string,string> $params */
    private function processDtEnd(array $params, string $value): void
    {
        switch ($params["VALUE"] ?? "DATE-TIME") {
            case "DATE-TIME":
                if (($datetime = $this->parseDateTime($value))) {
                    list($this->currentEvent['dateend'], $this->currentEvent['endtime']) = $datetime;
                }
                break;
            case "DATE":
                if (($date = $this->parseDate($value))) {
                    $this->currentEvent['dateend'] = $date;
                }
                break;
        }
    }

    private function processRrule(string $value): void
    {
        $parts = explode(";", $value);
        $ps = [];
        foreach ($parts as $part) {
            [$key, $value] = explode("=", $part, 2);
            $ps[$key] = $value;
        }
        switch ($ps["FREQ"] ?? "") {
            case "YEARLY":
                $this->currentEvent["recur"] = "yearly";
                break;
            case "WEEKLY":
                $this->currentEvent["recur"] = "weekly";
                break;
            default:
                $this->currentEvent["recur"] = "";
        }
        if (array_key_exists("UNTIL", $ps)) {
            $this->currentEvent["until"] = $this->parseDate($ps["UNTIL"]) ?? "";
        }
    }

    /** @return array{property:string,params:array<string,string>,value:string} */
    private function parseLine(): array
    {
        list($property, $value) = explode(':', $this->currentLine, 2);
        $parts = explode(';', $property);
        $property = $parts[0];
        $params = [];
        if (count($parts) > 1) {
            foreach (array_splice($parts, 1) as $part) {
                [$name, $val] = explode("=", $part);
                $params[$name] = $val;
            }
        }
        $value = str_replace(["\\\\", "\\;", "\\,", "\\N", "\\n"], ["\\", ";", ",", "\n", "\n"], $value);
        return ["property" => $property, "params" => $params, "value" => $value];
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

    public function eventCount(): int
    {
        return $this->eventCount;
    }
}
