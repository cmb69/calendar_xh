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
            if ($event->isBirthday()) {
                $ldt = $event->start()->withYear($now->year());
                if ($ldt->compare($now) < 0) {
                    $ldt = $event->start()->withYear($now->year() + 1);
                }
            } else {
                $ldt = $event->start();
                if ($ldt->compare($now) < 0) {
                    $ldt = $event->end();
                    if ($ldt->compare($now) < 0) {
                        continue;
                    }
                }
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

    public function delete(string $id): void
    {
        assert(array_key_exists($id, $this->events));
        unset($this->events[$id]);
    }
}
