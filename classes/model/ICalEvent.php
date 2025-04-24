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

trait ICalEvent
{
    /** @param list<string> $lines */
    public static function fromICalendar(array $lines): ?Event
    {
        $dto = new EventDto();
        while (($line = next($lines)) !== false) {
            if ($line === "END:VEVENT") {
                $loc = $dto->location;
                [$start, $end] = self::dateTimes($dto->datestart, $dto->dateend, $dto->starttime, $dto->endtime, $loc);
                if ($start === null || $end === null) {
                    return null;
                }
                $recurrence = self::createRecurrence($dto->recur, $start, $end, $dto->until, $loc);
                $description = $dto->description;
                return new self($dto->id, $start, $end, $dto->event, $dto->linkadr, $description, $loc, $recurrence);
            }
            self::processPropertyLine($line, $dto);
        }
        return null;
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

    /** @param callable(Event):string $genUrl */
    public function toICalendarString(string $id, Html2Text $converter, string $host, callable $genUrl): string
    {
        $id = $this->id !== "" ? $this->id : "$id@$host";
        $res = "BEGIN:VEVENT\r\n"
            . "UID:$id\r\n";
        $res .= $this->getDtstart() . "\r\n";
        $res .= $this->getDtend() . "\r\n";
        if (!($this->recurrence() === "none")) {
            $freq = strtoupper($this->recurrence());
            $res .= "RRULE:FREQ={$freq}";
            $until = $this->recursUntil();
            if ($until !== null) {
                if ($this->isFullDay()) {
                    $until = sprintf("%04d%02d%02d", $until->year(), $until->month(), $until->day());
                } else {
                    $until = sprintf(
                        "%04d%02d%02dT%02d%02d00",
                        $until->year(),
                        $until->month(),
                        $until->day(),
                        $this->start->hour(),
                        $this->start->minute()
                    );
                }
                $res .= ";UNTIL=" . $until;
            }
            $res .= "\r\n";
        }
        if ($this->summary !== "") {
            $res .= "SUMMARY:" . $this->summary . "\r\n";
        }
        $url = $genUrl($this);
        if ($url !== "") {
            $res .= "URL:" . $url . "\r\n";
        }
        if ($this->linktxt !== "") {
            $converter->setHtml($this->linktxt);
            $text = $converter->getText();
            $text = str_replace(["\\", ";", ",", "\r", "\n"], ["\\\\", "\\;", "\\,", "", "\\n\r\n "], $text);
            $res .= "DESCRIPTION:" . rtrim($text) . "\r\n";
        }
        if (!$this->isBirthday() && $this->location !== "") {
            $res .= "LOCATION:" . $this->location . "\r\n";
        }
        $res .= "END:VEVENT\r\n";
        return $res;
    }

    private function getDtstart(): string
    {
        if ($this->isFullDay()) {
            return sprintf(
                "DTSTART;VALUE=DATE:%04d%02d%02d",
                $this->start->year(),
                $this->start->month(),
                $this->start->day()
            );
        } else {
            return sprintf(
                "DTSTART:%04d%02d%02dT%02d%02d00",
                $this->start->year(),
                $this->start->month(),
                $this->start->day(),
                $this->start->hour(),
                $this->start->minute()
            );
        }
    }

    private function getDtend(): string
    {
        if ($this->isFullDay()) {
            return sprintf(
                "DTEND;VALUE=DATE:%04d%02d%02d",
                $this->end->year(),
                $this->end->month(),
                $this->end->day()
            );
        } else {
            return sprintf(
                "DTEND:%04d%02d%02dT%02d%02d00",
                $this->end->year(),
                $this->end->month(),
                $this->end->day(),
                $this->end->hour(),
                $this->end->minute()
            );
        }
    }
}
