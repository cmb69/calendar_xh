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

namespace Calendar\Model;

use Calendar\Dto\Event as EventDto;
use Calendar\Infra\Html2Text;

trait ICalendar
{
    /**
     * @param list<string> $lines
     * @param mixed $count
     * @param-out int $count
     */
    public static function fromICalendar(array $lines, &$count): self
    {
        $that = new self([]);
        for ($i = count($lines) - 1; $i > 0; $i--) { // unfold lines
            if (in_array(substr($lines[$i], 0, 1), [' ', "\t"])) {
                $lines[$i - 1] .= substr($lines[$i], 1);
                unset($lines[$i]);
            }
        }
        $count = self::parse($that, $lines);
        return $that;
    }

    /** @param list<string> $lines */
    private static function parse(self $that, array $lines): int
    {
        $count = 0;
        $event = new EventDto();
        $isInEvent = false;
        foreach ($lines as $currentLine) {
            if ($isInEvent) {
                if ($currentLine === 'END:VEVENT') {
                    $isInEvent = false;
                    $maybeEvent = Event::create(
                        $event->datestart,
                        $event->dateend,
                        $event->starttime,
                        $event->endtime,
                        $event->event,
                        $event->linkadr,
                        $event->description,
                        $event->location,
                        $event->recur,
                        $event->until,
                        $event->id
                    );
                    if ($maybeEvent !== null) {
                        $id = $event->id ?: sha1(serialize($event));
                        $that->events[$id] = $maybeEvent;
                    }
                } else {
                    self::processPropertyLine($currentLine, $event);
                }
            } else {
                if ($currentLine === 'BEGIN:VEVENT') {
                    $count++;
                    $isInEvent = true;
                    $event = new EventDto();
                }
            }
        }
        return $count;
    }

    private static function processPropertyLine(string $line, EventDto $event): void
    {
        ["property" => $property, "params" => $params, "value" => $value] = self::parseLine($line);
        assert($property !== null);
        assert($value !== null);
        switch ($property) {
            case 'UID':
                $event->id = $value;
                return;
            case 'SUMMARY':
                $event->event = $value;
                return;
            case 'LOCATION':
                $event->location = $value;
                return;
            case 'DESCRIPTION':
                $event->description = $value;
                return;
            case 'URL':
                $event->linkadr = $value;
                return;
            case 'DTSTART':
                self::processDtStart($params, $value, $event);
                return;
            case 'DTEND':
                self::processDtEnd($params, $value, $event);
                return;
            case 'RRULE':
                self::processRrule($value, $event);
                return;
        }
    }

    /** @param array<string,string> $params */
    private static function processDtStart(array $params, string $value, EventDto $event): void
    {
        switch ($params["VALUE"] ?? "DATE-TIME") {
            case "DATE-TIME":
                if (($datetime = self::parseDateTime($value))) {
                    list($event->datestart, $event->starttime) = $datetime;
                }
                break;
            case "DATE":
                if (($date = self::parseDate($value))) {
                    $event->datestart = $date;
                }
                break;
        }
    }

    /** @param array<string,string> $params */
    private static function processDtEnd(array $params, string $value, EventDto $event): void
    {
        switch ($params["VALUE"] ?? "DATE-TIME") {
            case "DATE-TIME":
                if (($datetime = self::parseDateTime($value))) {
                    list($event->dateend, $event->endtime) = $datetime;
                }
                break;
            case "DATE":
                if (($date = self::parseDate($value))) {
                    $event->dateend = $date;
                }
                break;
        }
    }

    private static function processRrule(string $value, EventDto $event): void
    {
        $parts = explode(";", $value);
        $ps = [];
        foreach ($parts as $part) {
            [$key, $value] = explode("=", $part, 2);
            $ps[$key] = $value;
        }
        switch ($ps["FREQ"] ?? "") {
            case "YEARLY":
                $event->recur = "yearly";
                break;
            case "WEEKLY":
                $event->recur = "weekly";
                break;
            case "DAILY":
                $event->recur = "daily";
                break;
            default:
                $event->recur = "";
        }
        if (array_key_exists("UNTIL", $ps)) {
            $event->until = self::parseDate($ps["UNTIL"]) ?? "";
        }
    }

    /** @return array{property:string,params:array<string,string>,value:string} */
    private static function parseLine(string $line): array
    {
        list($property, $value) = explode(':', $line, 2);
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
    private static function parseDateTime(string $value): ?array
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})/', $value, $matches)) {
            return ["$matches[1]-$matches[2]-$matches[3]", "$matches[4]:$matches[5]"];
        }
        return null;
    }

    /**
     * ignores the timezone
     */
    private static function parseDate(string $value): ?string
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $value, $matches)) {
            return "$matches[1]-$matches[2]-$matches[3]";
        }
        return null;
    }

    public function toICalendarString(Html2Text $converter, string $host): string
    {
        $res = "BEGIN:VCALENDAR\r\n"
            . "PRODID:-//3-magi.net//Calendar_XH//EN\r\n"
            . "VERSION:2.0\r\n";
        foreach ($this->events() as $id => $event) {
            $res .= $event->toICalendarString($id, $converter, $host);
        }
        $res .= "END:VCALENDAR\r\n";
        return $res;
    }
}
