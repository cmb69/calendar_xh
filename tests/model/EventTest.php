<?php

/**
 * Copyright 2021-2023 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;

/** @small */
class EventTest extends TestCase
{
    public function testIsFullDay()
    {
        $start = new LocalDateTime(2021, 4, 4, 0, 0);
        $end = new LocalDateTime(2021, 4, 4, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        $sut = new Event("", $start, $end, "Easter", "", "", "", $recurrence);
        $this->assertTrue($sut->isFullDay());
    }

    /** @dataProvider occurrencesDuringData */
    public function testoccurrencesDuring(Event $sut, int $year, int $month, array $expected): void
    {
        $this->assertEquals($expected, $sut->occurrencesDuring($year, $month));
    }

    public function occurrencesDuringData(): array
    {
        return [
            [$this->cmb(), 2025, 3, [$this->cmb(2025)]],
            [$this->intfcb(), 2025, 4, [$this->intfcb()]],
            [$this->easter(), 2025, 4, [$this->easter()]],
            [$this->christmas(), 2025, 12, [$this->christmasIn(2025)]],
            [$this->turnOfTheYear(), 2025, 12, [$this->turnOfTheYear(2025)]],
            [$this->cards(), 2025, 3, []],
            [$this->cards(), 2025, 4, [$this->cardsOn(2025, 4, 17), $this->cardsOn(2025, 4, 24)]],
            [$this->cards(), 2025, 6, [$this->cardsOn(2025, 6, 5), $this->cardsOn(2025, 6, 12)]],
            [$this->lunchBreak(), 2025, 4,
                $this->lunchBreaks($this->ldt(2025, 4, 25, 0, 0), $this->ldt(2025, 4, 30, 0, 0))
            ],
            [$this->lunchBreak(), 2025, 5,
                $this->lunchBreaks($this->ldt(2025, 5, 1, 0, 0), $this->ldt(2025, 5, 3, 0, 0))
            ],
        ];
    }

    /** @dataProvider occurrenceOnData */
    public function testoccurrenceOn(Event $sut, LocalDateTime $day, bool $daysBetween, ?Event $expected): void
    {
        $this->assertEquals($expected, $sut->occurrenceOn($day, $daysBetween));
    }

    public function occurrenceOnData(): array
    {
        return [
            [$this->cmb(), $this->ldt(2025, 3, 24, 0, 0), true, $this->cmb(2025)],
            [$this->intfcb(), $this->ldt(2025, 4, 16, 0, 0), true, $this->intfcb()],
            [$this->easter(), $this->ldt(2025, 4, 20, 0, 0), true, $this->easter()],
            [$this->easter(), $this->ldt(2025, 4, 20, 0, 0), false, $this->easter()],
            [$this->christmas(), $this->ldt(2025, 12, 24, 0, 0), false, $this->christmasIn(2025)],
            [$this->christmas(), $this->ldt(2025, 12, 25, 0, 0), true, $this->christmasIn(2025)],
            [$this->christmas(), $this->ldt(2025, 12, 26, 0, 0), false, $this->christmasIn(2025)],
            [$this->turnOfTheYear(), $this->ldt(2025, 12, 31, 0, 0), false, $this->turnOfTheYear(2025)],
            [$this->turnOfTheYear(), $this->ldt(2026, 1, 1, 0, 0), false, $this->turnOfTheYear(2025)],
            [$this->cards(), $this->ldt(2025, 4, 1, 0, 0), false, null],
            [$this->cards(), $this->ldt(2025, 5, 1, 0, 0), false, $this->cardsOn(2025, 5, 1)],
            [$this->cards(), $this->ldt(2025, 4, 18, 0, 0), false, null],
            [$this->cards(), $this->ldt(2025, 6, 19, 0, 0), false, null],
            [$this->lunchBreak(), $this->ldt(2025, 4, 29, 0, 0), false, $this->lunchBreakOn(2025, 4, 29)],
            [$this->lunchBreak(), $this->ldt(2025, 4, 24, 0, 0), false, null],
            [$this->lunchBreak(), $this->ldt(2025, 5, 4, 0, 0), false, null],
        ];
    }

    /** @dataProvider earliestOccurrenceAfterData */
    public function testearliestOccurrenceAfter(Event $sut, LocalDateTime $date, array $expected): void
    {
        $this->assertEquals($expected, $sut->earliestOccurrenceAfter($date));
    }

    public function earliestOccurrenceAfterData(): array
    {
        return [
            [$this->cmb(), $this->ldt(2025, 3, 20, 0, 0), [$this->cmb(2025), $this->ldt(2025, 3, 24, 0, 0)]],
            [$this->cmb(), $this->ldt(2025, 3, 25, 0, 0), [$this->cmb(2026), $this->ldt(2026, 3, 24, 0, 0)]],
            [$this->easter(), $this->ldt(2025, 4, 20, 0, 0), [$this->easter(), $this->ldt(2025, 4, 20, 0, 0)]],
            [$this->easter(), $this->ldt(2025, 4, 21, 0, 0), [$this->easter(), $this->ldt(2025, 4, 21, 23, 59)]],
            [$this->easter(), $this->ldt(2025, 4, 22, 0, 0), [null, null]],
            [
                $this->christmas(),
                $this->ldt(2025, 12, 24, 0, 0),
                [$this->christmasIn(2025), $this->ldt(2025, 12, 24, 0, 0)],
            ], [
                $this->christmas(),
                $this->ldt(2025, 12, 26, 0, 0),
                [$this->christmasIn(2025), $this->ldt(2025, 12, 26, 23, 59)],
            ], [
                $this->christmas(),
                $this->ldt(2025, 12, 27, 0, 0),
                [$this->christmasIn(2026), $this->ldt(2026, 12, 24, 0, 0)],
            ], [
                $this->turnOfTheYear(),
                $this->ldt(2026, 1, 1, 0, 0),
                [$this->turnofTheYear(2025), $this->ldt(2026, 1, 1, 23, 59)],
            ], [
                $this->cards(),
                $this->ldt(2025, 4, 24, 0, 0),
                [$this->cardsOn(2025, 4, 24), $this->ldt(2025, 4, 24, 19, 45)],
            ], [
                $this->cards(),
                $this->ldt(2025, 4, 24, 21, 0),
                [$this->cardsOn(2025, 4, 24), $this->ldt(2025, 4, 24, 22, 15)],
            ],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 0, 0), [
                $this->lunchBreakOn(2025, 4, 25),
                $this->ldt(2025, 4, 25, 12, 0),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 12, 30), [
                $this->lunchBreakOn(2025, 4, 25),
                $this->ldt(2025, 4, 25, 13, 0),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 26, 0, 0), [
                $this->lunchBreakOn(2025, 4, 26),
                $this->ldt(2025, 4, 26, 12, 0),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 5, 5, 0, 0), [null, null]],
        ];
    }

    /** @dataProvider splitData */
    public function testSplit(Event $event, LocalDateTime $ldt, array $expected): void
    {
        $this->assertEquals($expected, $event->split($ldt, function () {
            return 111;
        }));
    }

    public function splitData(): array
    {
        return [
            [$this->christmas(), $this->ldt(2025, 12, 24, 0, 0), [
                $this->christmasIn(2000, "111", $this->ldt(2024, 12, 24, 23, 59)),
                $this->christmasIn(2025, "111"),
                $this->christmasIn(2026, "111", true),
            ]],
            [$this->christmas(), $this->ldt(2000, 12, 24, 0, 0), [
                null,
                $this->christmasIn(2000, "111"),
                $this->christmasIn(2001, "111", true),
            ]],
            [
                $this->christmasIn(2000, "", $this->ldt(2024, 12, 24, 23, 59)),
                $this->ldt(2024, 12, 24, 0, 0), [
                    $this->christmasIn(2000, "111", $this->ldt(2023, 12, 24, 23, 59)),
                $this->christmasIn(2024, "111"),
                null]
            ],
            [
                $this->christmasIn(2004, "", $this->ldt(2024, 12, 24, 23, 59)),
                $this->ldt(2025, 12, 24, 0, 0), [null, null, null,]
            ],
            [$this->christmas(), $this->ldt(2025, 12, 23, 0, 0), [null, null, null]],
            [$this->intfcb(), $this->ldt(2025, 4, 16, 22, 0), [null, null, null]],
            [$this->cards(), $this->ldt(2025, 5, 1, 0, 0), [
                $this->cardsOn(2025, 4, 17, "111", $this->ldt(2025, 4, 24, 23, 59)),
                $this->cardsOn(2025, 5, 1, "111"),
                $this->cardsOn(2025, 5, 8, "111", $this->ldt(2025, 6, 12, 23, 59)),
            ]],
            [$this->cards(), $this->ldt(2025, 4, 17, 0, 0), [
                null,
                $this->cardsOn(2025, 4, 17, "111"),
                $this->cardsOn(2025, 4, 24, "111", $this->ldt(2025, 6, 12, 23, 59)),
            ]],
            [$this->cards(), $this->ldt(2025, 6, 12, 0, 0), [
                $this->cardsOn(2025, 4, 17, "111", $this->ldt(2025, 6, 5, 23, 59)),
                $this->cardsOn(2025, 6, 12, "111"),
                null,
            ]],
            [$this->cards(), $this->ldt(2025, 4, 18, 0, 0), [null, null, null]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 30, 0, 0), [
                $this->lunchBreakOn(2025, 4, 25, "111", $this->ldt(2025, 4, 29, 23, 59)),
                $this->lunchBreakOn(2025, 4, 30, "111"),
                $this->lunchBreakOn(2025, 5, 1, "111", $this->ldt(2025, 5, 3, 23, 59)),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 0, 0), [
                null,
                $this->lunchBreakOn(2025, 4, 25, "111"),
                $this->lunchBreakOn(2025, 4, 26, "111", $this->ldt(2025, 5, 3, 23, 59)),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 5, 3, 0, 0), [
                $this->lunchBreakOn(2025, 4, 25, "111", $this->ldt(2025, 5, 2, 23, 59)),
                $this->lunchBreakOn(2025, 5, 3, "111"),
                null,
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 5, 4, 0, 0), [null, null, null]],
        ];
    }

    public function testGH98()
    {
        $start = new LocalDateTime(2026, 4, 16, 0, 0);
        $end = new LocalDateTime(2026, 4, 16, 23, 59);
        $sut = new BirthdayEvent("", $start, $end, "Someone not yet born", "", "");
        $now = $this->ldt(2025, 4, 16, 0, 0);
        $this->assertEmpty($sut->occurrencesDuring(2025, 4));
        $this->assertNull($sut->occurrenceOn($now, true));
        $this->assertEquals([null, null], $sut->earliestOccurrenceAfter($now));
    }

    private function cmb(int $year = 1969): Event
    {
        $start = new LocalDateTime(1969, 3, 24, 0, 0);
        $end = new LocalDateTime(1969, 3, 24, 23, 59);
        $recurrence = new YearlyRecurrence($start, $end, null);
        $event = new BirthdayEvent("", $start, $end, "cmb", "", "", "");
        if ($year !== 1969) {
            $event = $event->occurrenceStartingAt($this->ldt($year, 3, 24, 0, 0));
        }
        return $event;
    }

    private function intfcb(): Event
    {
        $start = new LocalDateTime(2025, 4, 16, 21, 0);
        $end = new LocalDateTime(2025, 4, 16, 22, 45);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "#INTFCB", "", "", "Guiseppe-Meazza-Stadion", $recurrence);
    }

    private function easter(): Event
    {
        $start = new LocalDateTime(2025, 4, 20, 0, 0);
        $end = new LocalDateTime(2025, 4, 21, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "easter", "", "", "", $recurrence);
    }

    private function christmas(): Event
    {
        $start = new LocalDateTime(2000, 12, 24, 0, 0);
        $end = new LocalDateTime(2000, 12, 26, 23, 59);
        $recurrence = new YearlyRecurrence($start, $end, null);
        return new Event("", $start, $end, "Christmas", "", "", "", $recurrence);
    }

    /** @param LocalDateTime|bool $until */
    private function christmasIn(int $year, string $id = "", $until = null): Event
    {
        $start = new LocalDateTime($year, 12, 24, 0, 0);
        $end = new LocalDateTime($year, 12, 26, 23, 59);
        if (!$until) {
            $recurrence = new NoRecurrence($start, $end);
        } else {
            $recurrence = new YearlyRecurrence($start, $end, $until === true ? null : $until);
        }
        return new Event($id, $start, $end, "Christmas", "", "", "", $recurrence);
    }

    private function turnOfTheYear(?int $year = null): Event
    {
        if ($year === null) {
            $start = new LocalDateTime(2000, 12, 31, 0, 0);
            $end = new LocalDateTime(2001, 1, 1, 23, 59);
            $recurrence = new YearlyRecurrence($start, $end, null);
            return new Event("", $start, $end, "Turn of the year", "", "", "", $recurrence);
        }
        $start = new LocalDateTime($year, 12, 31, 0, 0);
        $end = new LocalDateTime($year + 1, 1, 1, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Turn of the year", "", "", "", $recurrence);
    }

    private function cards(): Event
    {
        $start = new LocalDateTime(2025, 4, 17, 19, 45);
        $end = new LocalDateTime(2025, 4, 17, 22, 15);
        $recurrence = new WeeklyRecurrence($start, $end, new LocalDateTime(2025, 6, 12, 23, 59));
        return new Event("", $start, $end, "Cards", "", "", "", $recurrence);
    }

    private function cardsOn(int $year, int $month, int $day, string $id = "", ?LocalDateTime $until = null): Event
    {
        $start = new LocalDateTime($year, $month, $day, 19, 45);
        $end = new LocalDateTime($year, $month, $day, 22, 15);
        if ($until === null) {
            $recurrence = new NoRecurrence($start, $end);
        } else {
            $recurrence = new WeeklyRecurrence($start, $end, $until);
        }
        return new Event($id, $start, $end, "Cards", "", "", "", $recurrence);
    }

    private function lunchBreak(): Event
    {
        $start = new LocalDateTime(2025, 4, 25, 12, 0);
        $end = new LocalDateTime(2025, 4, 25, 13, 0);
        $recurrence = new DailyRecurrence($start, $end, new LocalDateTime(2025, 5, 3, 23, 59));
        return new Event("", $start, $end, "Lunch break", "", "", "", $recurrence);
    }

    private function lunchBreakOn(int $year, int $month, int $day, string $id = "", ?LocalDateTime $until = null): Event
    {
        $start = new LocalDateTime($year, $month, $day, 12, 0);
        $end = new LocalDateTime($year, $month, $day, 13, 0);
        if ($until === null) {
            $recurrence = new NoRecurrence($start, $end);
        } else {
            $recurrence = new DailyRecurrence($start, $end, $until);
        }
        return new Event($id, $start, $end, "Lunch break", "", "", "", $recurrence);
    }

    private function lunchBreaks(LocalDateTime $from, LocalDateTime $to): array
    {
        $res = [];
        $day = new Interval(1, 0, 0);
        while ($from->compareDate($to) <= 0) {
            $start = new LocalDateTime($from->year(), $from->month(), $from->day(), 12, 0);
            $end = new LocalDateTime($from->year(), $from->month(), $from->day(), 13, 0);
            $recurrence = new NoRecurrence($start, $end);
            $res[] = new Event("", $start, $end, "Lunch break", "", "", "", $recurrence);
            $from = $from->plus($day);
        }
        return $res;
    }

    private function ldt(int $year, int $month, int $day, int $hour, int $minute): LocalDateTime
    {
        return new LocalDateTime($year, $month, $day, $hour, $minute);
    }
}
