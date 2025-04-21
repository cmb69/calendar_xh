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

trait CsvCalendar
{
    public static function fromCsv(string $contents, bool $convertToHtml): self
    {
        $that = new Calendar([]);
        $stream = fopen("php://memory", "w+");
        if ($stream === false) {
            return $that;
        }
        if (fwrite($stream, $contents) !== strlen($contents)) {
            fclose($stream);
            return $that;
        }
        if (!rewind($stream)) {
            fclose($stream);
            return $that;
        }
        while (($record = fgetcsv($stream, 0, ";", '"', "\0")) !== false) {
            if (!self::validateRecord($record)) {
                continue;
            }
            $event = Event::fromCsvRecord($record, $convertToHtml);
            if ($event !== null) {
                $id = $event->id() ?: sha1(serialize($record));
                $that->events[$id] = $event;
            }
        }
        fclose($stream);
        return $that;
    }

    /**
     * @param ?list<?string> $record
     * @phpstan-assert-if-true list<string> $record
     */
    private static function validateRecord(?array $record): bool
    {
        if ($record === null) {
            return false;
        }
        foreach ($record as $field) {
            if (!is_string($field)) {
                return false;
            }
        }
        return true;
    }

    public function toCsvString(): string
    {
        $contents = "";
        $stream = fopen("php://memory", "w+");
        if ($stream === false) {
            return $contents;
        }
        foreach ($this->events as $event) {
            if (!$event->writeCsvRecord($stream)) {
                fclose($stream);
                return $contents;
            }
        }
        if (!rewind($stream)) {
            fclose($stream);
            return $contents;
        }
        $contents = (string) stream_get_contents($stream);
        fclose($stream);
        return $contents;
    }
}
