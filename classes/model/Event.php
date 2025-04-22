<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
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

/** @phpstan-consistent-constructor */
class Event
{
    use CsvEvent;
    use ICalendarEvent;
    use TextEvent;

    /** @var string */
    private $id = "";

    /** @var LocalDateTime */
    private $start;

    /** @var LocalDateTime */
    private $end;

    /** @var string */
    private $summary;

    /** @var string */
    private $linkadr;

    /** @var string */
    private $linktxt;

    /** @var string */
    private $location;

    /** @var Recurrence */
    private $recurrence;

    /** @var ?int */
    private $age = null;

    private static function create(
        string $datestart,
        string $dateend,
        string $starttime,
        string $endtime,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location,
        string $recurrenceRule,
        string $until,
        string $id
    ): ?self {
        [$start, $end] = self::dateTimes($datestart, $dateend, $starttime, $endtime, $location);
        if ($start === null || $end === null) {
            return null;
        }
        $recurrence = self::createRecurrence($recurrenceRule, $start, $end, $until, $location);
        return new self($id, $start, $end, $summary, $linkadr, $linktxt, $location, $recurrence);
    }

    /** @return array{?LocalDateTime,?LocalDateTime} */
    public static function dateTimes(
        string $datestart,
        string $dateend,
        string $starttime,
        string $endtime,
        string $location
    ): array {
        if ($dateend === "") {
            if ($endtime !== "") {
                return [null, null];
            }
            $endtime = $starttime !== "" ? $starttime : "23:59";
            if (($end = LocalDateTime::fromIsoString("{$datestart}T{$endtime}")) === null) {
                return [null, null];
            }
        } else {
            if (trim($location) === "###") {
                $endtime = "23:59";
            } elseif ($endtime === "") {
                $endtime = $starttime !== "" ? $starttime : "23:59";
            }
            if (($end = LocalDateTime::fromIsoString("{$dateend}T{$endtime}")) === null) {
                return [null, null];
            }
        }
        if (trim($location) === "###" || $starttime === '') {
            $starttime = "00:00";
        }
        if (($start = LocalDateTime::fromIsoString("{$datestart}T{$starttime}")) === null) {
            return [null, null];
        }
        return [$start, $end];
    }

    public static function createRecurrence(
        string $recurrenceRule,
        LocalDateTime $start,
        LocalDateTime $end,
        string $until,
        string $location
    ): Recurrence {
        if (trim($location) === "###") {
            return new YearlyRecurrence($start, $end, null);
        }
        return Recurrence::create($recurrenceRule, $start, $end, $until);
    }

    public static function fromDto(EventDto $dto): ?self
    {
        return self::create(
            $dto->datestart,
            $dto->dateend,
            $dto->starttime,
            $dto->endtime,
            $dto->event,
            $dto->linkadr,
            $dto->description,
            $dto->location,
            $dto->recur,
            $dto->until,
            $dto->id
        );
    }

    public function __construct(
        string $id,
        LocalDateTime $start,
        LocalDateTime $end,
        string $summary,
        string $linkadr,
        string $linktxt,
        string $location,
        Recurrence $recurrence
    ) {
        $this->id = $id;
        $this->start = $start;
        $this->end = $end;
        $this->summary = $summary;
        $this->linkadr = $linkadr;
        $this->linktxt = $linktxt;
        $this->location = $location;
        $this->recurrence = $recurrence;
        if (trim($location) === "###") {
            $this->age = 0;
        }
    }

    public function update(EventDto $dto): bool
    {
        [$start, $end] =
            self::dateTimes($dto->datestart, $dto->dateend, $dto->starttime, $dto->endtime, $dto->location);
        if ($start === null || $end === null) {
            return false;
        }
        $this->start = $start;
        $this->end = $end;
        $this->summary = $dto->event;
        $this->linkadr = $dto->linkadr;
        $this->linktxt = $dto->description;
        $this->location = $dto->location;
        $this->recurrence = self::createRecurrence($dto->recur, $start, $end, $dto->until, $dto->location);
        $this->age = trim($dto->location) === "###" ? 0 : null;
        return true;
    }

    public function toDto(): EventDto
    {
        $dto = new EventDto();
        $dto->id = $this->id();
        $dto->datestart = $this->getIsoStartDate();
        $dto->dateend = $this->getIsoEndDate();
        $dto->starttime = $this->getIsoStartTime();
        $dto->endtime = $this->getIsoEndTime();
        $dto->event = $this->summary;
        $dto->linkadr = $this->linkadr;
        $dto->description = $this->linktxt;
        $dto->location = $this->location;
        $dto->recur = $this->recurrence();
        $dto->until = $this->recursUntil() !== null ? $this->recursUntil()->getIsoDate() : "";
        return $dto;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function start(): LocalDateTime
    {
        return $this->start;
    }

    public function end(): LocalDateTime
    {
        return $this->end;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function linkadr(): string
    {
        return $this->linkadr;
    }

    public function linktxt(): string
    {
        return $this->linktxt;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function recurrence(): string
    {
        return $this->recurrence->name();
    }

    public function recursUntil(): ?LocalDateTime
    {
        return $this->recurrence->until();
    }

    public function isBirthday(): bool
    {
        return $this->age !== null;
    }

    public function age(): int
    {
        assert($this->age !== null);
        return $this->age;
    }

    public function getIsoStartDate(): string
    {
        return $this->start->getIsoDate();
    }

    public function getIsoStartTime(): string
    {
        return $this->start->getIsoTime();
    }

    public function getIsoEndDate(): string
    {
        return $this->end->getIsoDate();
    }

    public function getIsoEndTime(): string
    {
        return $this->end->getIsoTime();
    }

    public function isMultiDay(): bool
    {
        return $this->end->compareDate($this->start) > 0;
    }

    public function startsOn(LocalDateTime $day): bool
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        return $this->start()->compareDate($day) === 0
            && $this->end()->compareDate($day) !== 0;
    }

    public function endsOn(LocalDateTime $day): bool
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        return $this->end()->compareDate($day) === 0
            && $this->start()->compareDate($day) !== 0;
    }

    public function isFullDay(): bool
    {
        return $this->start->hour() === 0 && $this->start->minute() === 0
            && $this->end->hour() === 23 && $this->end->minute() === 59;
    }

    /** @return list<Event> */
    public function occurrencesDuring(int $year, int $month): array
    {
        $res = [];
        foreach ($this->recurrence->matchesInMonth($year, $month) as $match) {
            $res[] = $this->occurrenceStartingAt($match);
        }
        return $res;
    }

    public function occurrenceOn(LocalDateTime $day, bool $daysBetween): ?self
    {
        assert($day->hour() === 0 && $day->minute() === 0);
        $match = $this->recurrence->matchOnDay($day, $daysBetween);
        if ($match === null) {
            return null;
        }
        return $this->occurrenceStartingAt($match);
    }

    /** @return array{?self,?LocalDateTime} */
    public function earliestOccurrenceAfter(LocalDateTime $date): array
    {
        $match = $this->recurrence->firstMatchAfter($date);
        if ($match === null) {
            return [null, null];
        }
        return [$this->occurrenceStartingAt($match[0]), $match[1]];
    }

    /** @return static */
    public function occurrenceStartingAt(LocalDateTime $start)
    {
        $duration = $this->end()->diff($this->start());
        $end = $start->plus($duration);
        $that = new static(
            "",
            $start,
            $end,
            $this->summary,
            $this->linkadr,
            $this->linktxt,
            $this->location,
            new NoRecurrence($start, $end)
        );
        if ($this->isBirthday()) {
            $that->age = $start->year() - $this->start()->year();
        }
        return $that;
    }

    /**
     * @param callable():string $generateId
     * @return array{?Event,?Event,?Event}
     */
    public function split(LocalDateTime $date, callable $generateId): array
    {
        [$prevrec, $rec, $nextrec] = $this->recurrence->split($date);
        if ($prevrec !== null) {
            $prevevent = clone $this;
            $prevevent->id = $generateId();
            $prevevent->start = $prevrec->start();
            $prevevent->end = $prevrec->end();
            $prevevent->recurrence = $prevrec;
        } else {
            $prevevent = null;
        }
        if ($rec !== null) {
            $event = clone $this;
            $event->id = $generateId();
            $event->start = $rec->start();
            $event->end = $rec->end();
            $event->recurrence = $rec;
        } else {
            $event = null;
        }
        if ($nextrec !== null) {
            $nextevent = clone $this;
            $nextevent->id = $generateId();
            $nextevent->start = $nextrec->start();
            $nextevent->end = $nextrec->end();
            $nextevent->recurrence = $nextrec;
        } else {
            $nextevent = null;
        }
        return [$prevevent, $event, $nextevent];
    }
}
