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

class Calendar
{
    use CsvCalendar;
    use ICalendar;
    use TextCalendar;

    /** @var array<string,Event> */
    private $events;

    /** @param array<string,Event> $events */
    public static function fromEvents(array $events): self
    {
        return new Calendar($events);
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
}
