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

use Calendar\Html2Text;

class Calendar
{
    /** @var array<string,Event> */
    private $events;

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
            $ldt = $event->after($now);
            if ($ldt === null) {
                continue;
            }
            if ($nextldt === null || $ldt->compare($nextldt) < 0) {
                $nextevent = $event;
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
            if ($event->occursDuring($year, $month)) {
                $result[] = $event;
            }
        }
        uasort($result, function (Event $a, Event $b) use ($year): int {
            $dt1 = $a->isBirthday() ? $a->start()->withYear($year) : $a->start();
            $dt2 = $b->isBirthday() ? $b->start()->withYear($year) : $b->start();
            return $dt1->compare($dt2);
        });
        return $result;
    }

    /** @return list<Event> */
    public function eventsOn(LocalDateTime $day, bool $daysBetween): array
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $result = [];
        foreach ($this->events as $event) {
            if ($event->occursOn($day, $daysBetween)) {
                $result[] = $event;
            }
        }
        return $result;
    }

    public function delete(string $id): void
    {
        assert(array_key_exists($id, $this->events));
        unset($this->events[$id]);
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
