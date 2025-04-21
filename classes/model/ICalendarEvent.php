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

use Calendar\Infra\Html2Text;

trait ICalendarEvent
{
    public function toICalendarString(string $id, Html2Text $converter, string $host): string
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
        if ($this->linkadr !== "") {
            $res .= "URL:" . $this->linkadr . "\r\n";
        }
        if ($this->linktxt !== "") {
            $converter->setHtml($this->linktxt);
            $text = $converter->getText();
            $text = str_replace(["\\", ";", ",", "\r", "\n"], ["\\\\", "\\;", "\\,", "", "\\n\r\n "], $text);
            $res .= "DESCRIPTION:" . rtrim($text) . "\r\n";
        }
        $res .= $this->locationToICalendarString();
        $res .= "END:VEVENT\r\n";
        return $res;
    }

    protected function locationToICalendarString(): string
    {
        if ($this->location === "") {
            return "";
        }
        return "LOCATION:" . $this->location . "\r\n";
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
