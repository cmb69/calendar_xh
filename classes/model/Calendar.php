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

use Calendar\Dto\Event as EventDto;
use Plib\Document;
use Plib\DocumentStore;

final class Calendar implements Document
{
    use CsvCalendar;
    use ICalCalendar;
    use TextCalendar;

    /** @var array<string,Event> */
    private $events;

    public static function retrieveFrom(DocumentStore $store): self
    {
        $that = $store->retrieve(self::filename($store), self::class);
        assert($that instanceof self);
        return $that;
    }

    public static function updateIn(DocumentStore $store): self
    {
        $filename = self::filename($store);
        $that = $store->update("calendar.2.6.csv", self::class);
        assert($that instanceof self);
        if ($filename !== "calendar.2.6.csv") {
            $old = $store->retrieve($filename, self::class);
            assert($old instanceof self);
            $that->import($old);
        }
        return $that;
    }

    private static function filename(DocumentStore $store): string
    {
        $files = $store->find('/[^\/]*calendar\.(2\.6\.csv|csv|txt)$/');
        if (in_array("calendar.2.6.csv", $files, true)) {
            return "calendar.2.6.csv";
        } elseif (in_array("calendar.csv", $files, true)) {
            return "calendar.csv";
        } elseif (in_array("calendar.txt", $files, true)) {
            return "calendar.txt";
        }
        return "calendar.2.6.csv";
    }

    public static function fromString(string $contents, string $key): self
    {
        if (preg_match('/\.(.*$)/', $key, $matches) === false) {
            return new self([]);
        }
        switch ($matches[1]) {
            case "2.6.csv":
                return Calendar::fromCsv($contents, false);
            case "csv":
                return Calendar::fromCsv($contents, true);
            case "txt":
                return Calendar::fromText($contents);
            default:
                return new self([]);
        }
    }

    /** @param array<string,Event> $events */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /** @return array<string,Event> */
    public function events(): array
    {
        return $this->events;
    }

    public function event(string $id): ?Event
    {
        return $this->events[$id] ?? null;
    }

    public function nextEvent(LocalDateTime $now): ?Event
    {
        $nextevent = null;
        $nextldt = null;
        foreach ($this->events as $event) {
            [$occurrence, $ldt] = $event->earliestOccurrenceAfter($now);
            if ($ldt === null) {
                continue;
            }
            if ($nextldt === null || $ldt->compare($nextldt) < 0) {
                $nextevent = $occurrence;
                $nextldt = $ldt;
            }
        }
        return $nextevent;
    }

    /** @return list<Event> */
    public function eventsDuring(int $year, int $month): array
    {
        $result = [];
        foreach ($this->events as $event) {
            if (($occurrences = $event->occurrencesDuring($year, $month)) !== null) {
                array_push($result, ...$occurrences);
            }
        }
        uasort($result, function (Event $a, Event $b): int {
            return $a->start()->compare($b->start());
        });
        return $result;
    }

    /** @return list<Event> */
    public function eventsOn(LocalDateTime $day, bool $daysBetween): array
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $result = [];
        foreach ($this->events as $event) {
            if (($occurrence = $event->occurrenceOn($day, $daysBetween)) !== null) {
                $result[] = $occurrence;
            }
        }
        return $result;
    }

    public function numberOfEventsWithoutId(): int
    {
        $res = 0;
        foreach ($this->events as $event) {
            if ($event->id() === "") {
                $res++;
            }
        }
        return $res;
    }

    public function addEvent(string $id, EventDto $dto): ?Event
    {
        [$start, $end] =
            Event::dateTimes($dto->datestart, $dto->dateend, $dto->starttime, $dto->endtime, $dto->location);
        if ($start === null || $end === null) {
            return null;
        }
        $recurrence = Event::createRecurrence($dto->recur, $start, $end, $dto->until, $dto->location);
        $event = new Event($id, $start, $end, $dto->event, $dto->linkadr, $dto->description, $dto->location, $recurrence);
        $this->events[$id] = $event;
        return $event;
    }

    private function sort(): void
    {
        uasort($this->events, function (Event $a, Event $b): int {
            return $a->start()->compare($b->start());
        });
    }

    /** @param callable():string $generateId */
    public function generateIds(callable $generateId): void
    {
        foreach ($this->events as $event) {
            if ($event->id() === "") {
                $event->setId($generateId());
            }
        }
    }

    /** @param callable():string $generateId */
    public function split(string $id, ?LocalDateTime $split, callable $generateId): ?string
    {
        if (!array_key_exists($id, $this->events) || $split === null) {
            return null;
        }
        $event = $this->events[$id];
        [$prevevent, $event, $nextevent] = $event->split($split, $generateId);
        if ($event === null) {
            return null;
        }
        unset($this->events[$id]);
        if ($prevevent !== null) {
            $this->events[$prevevent->id()] = $prevevent;
        }
        $this->events[$event->id()] = $event;
        if ($nextevent !== null) {
            $this->events[$nextevent->id()] = $nextevent;
        }
        return $event->id();
    }

    public function delete(string $id): void
    {
        assert(array_key_exists($id, $this->events));
        unset($this->events[$id]);
    }

    public function import(Calendar $other): void
    {
        $this->events = array_replace($this->events, $other->events);
    }

    public function toString(): string
    {
        return $this->toCsvString();
    }
}
