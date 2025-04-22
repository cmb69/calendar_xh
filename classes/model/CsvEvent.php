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

namespace Calendar\Model;

trait CsvEvent
{
    /** @param list<string> $record */
    public static function fromCsvRecord(array $record, bool $convertToHtml): ?self
    {
        $that = null;
        [$datestart, $starttime, $dateend, $endtime,  $event, $location, $linkadr, $linktxt] = $record;
        if (!$dateend) {
            $dateend = "";
        }
        if (!$endtime) {
            $endtime = "";
        }
        $recurrenceRule = count($record) > 8 ? $record[8] : "";
        $until = count($record) > 9 ? $record[9] : "";
        $uid = count($record) > 10 && trim($record[10]) !== "" ? $record[10] : "";
        if ($convertToHtml) {
            $linktxt = XH_hsc($linktxt);
            if ($linkadr) {
                $target = (strpos($linkadr, "://") === false) ? "_self" : "_blank";
                $title = XH_hsc($event);
                $text = $linktxt ?: XH_hsc($linkadr);
                $url = XH_hsc($linkadr);
                $linktxt = "<a href=\"{$url}\" target=\"{$target}\" title=\"{$title}\">"
                    . "{$text}</a>";
            }
        }
        if ($datestart != '' && $event != '') {
            [$start, $end] = self::dateTimes($datestart, $dateend, $starttime, $endtime, $location);
            if ($start === null || $end === null) {
                return null;
            }
            $recurrence = self::createRecurrence($recurrenceRule, $start, $end, $until, $location);
            $that = new self($uid, $start, $end, $event, $linkadr, $linktxt, $location, $recurrence);
        }
        return $that;
    }

    /** @param resource $stream */
    public function writeCsvRecord($stream): bool
    {
        $record = [
            $this->getIsoStartDate(),
            $this->isFullDay() ? "" : $this->getIsoStartTime(),
            $this->getIsoEndDate(),
            $this->isFullDay() ? "" : $this->getIsoEndTime(),
            $this->summary,
            $this->location,
            $this->linkadr,
            $this->linktxt,
            $this->recurrence() === "none" ? "" : $this->recurrence(),
            $this->recursUntil() !== null ? $this->recursUntil()->getIsoDate() : "",
            $this->id,
        ];
        return fputcsv($stream, $record, ';', '"', "\0") !== false;
    }
}
