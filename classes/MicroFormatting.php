<?php

/**
 * Copyright (c) Christoph M. Becker
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
use Plib\Request;

trait MicroFormatting
{
    /** @return array{string,string} */
    private function renderIsoStartAndEndDateTime(Event $event): array
    {
        $startDate = $event->isFullDay()
            ? $event->getIsoStartDate()
            : $event->getIsoStartDate() . "T" . $event->getIsoStartTime() . "00";
        $endDate = $event->isFullDay()
            ? $event->getIsoEndDate()
            : $event->getIsoEndDate() . "T" . $event->getIsoEndTime() . "00";
        return [$startDate, $endDate];
    }

    /** @return array<mixed> */
    private function eventData(Request $request, Event $event): array
    {
        $startDate = $event->isFullDay()
            ? $event->getIsoStartDate()
            : $event->getIsoStartDate() . "T" . $event->getIsoStartTime() . "00";
        $data = [
            "@context" => "https://schema.org",
            "@type" => "Event",
            "name" => $event->summary(),
            "startDate" => $startDate, // TODO remove if schedule
            "duration" => $event->isoDuration(),
            "url" => $this->eventUrl($request, $event),
            "location" => $event->location(),
        ];
        $description = $this->plainEventDescription($event);
        if ($description !== null) {
            $data["description"] = $description;
        }
        if ($event->recurrence() !== "none") {
            $schedule = [
                "@type" => "Schedule",
                "startDate" => $startDate,
                "repeatFrequency" => $this->repeatFrequency($event),
            ];
            $until = $event->recursUntil();
            if ($until !== null) {
                $schedule["endDate"] = $until->getIsoDate();
            }
            unset($data["startDate"]);
            $data["eventSchedule"] = $schedule;
        }
        return $data;
    }

    private function eventUrl(Request $request, Event $event): string
    {
        if ($event->linkadr() !== "") {
            return $event->linkadr();
        }
        if (!$this->conf["event_allow_single"]) {
            return "";
        }
        $url = $request->url()->page("")->with("function", "calendar_event")
            ->with("event_id", $event->id());
        if ($event->isOccurrence()) {
            $url = $url->with("calendar_occurrence", $event->getIsoStartDate());
        }
        return $url->absolute();
    }

    private function plainEventDescription(Event $event): ?string
    {
        $plain = html_entity_decode(strip_tags($event->linktxt()), ENT_QUOTES, "UTF-8");
        $plain = (string) preg_replace(['/^\s*|\s*$/u', '/\s+/'], ["", " "], $plain);
        $ok = preg_match('/^.{0,160}(?=\b|$)/us', $plain, $matches);
        if (!$ok) {
            return null;
        }
        $res = $matches[0];
        if (strlen($res) < strlen($plain)) {
            $res .= "…";
        }
        return $res;
    }

    private function repeatFrequency(Event $event): string
    {
        $freqs = [
            "daily" => "P1D",
            "weekly" => "P1W",
            "yearly" => "P1Y",
        ];
        return $freqs[$event->recurrence()];
    }
}
