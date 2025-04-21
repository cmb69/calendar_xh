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
        $subject = Event::create("2021-04-04", "", "", "", "Easter", "", "", "", "", "", "");
        $this->assertTrue($subject->isFullDay());
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
            [$this->christmas(), 2025, 12, [$this->christmas(2025)]],
            [$this->turnOfTheYear(), 2025, 12, [$this->turnOfTheYear(2025)]],
            [$this->cards(), 2025, 3, []],
            [$this->cards(), 2025, 4, [$this->cards(2025, 4, 17), $this->cards(2025, 4, 24)]],
            [$this->cards(), 2025, 6, [$this->cards(2025, 6, 5), $this->cards(2025, 6, 12)]],
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
            [$this->christmas(), $this->ldt(2025, 12, 24, 0, 0), false, $this->christmas(2025)],
            [$this->christmas(), $this->ldt(2025, 12, 25, 0, 0), true, $this->christmas(2025)],
            [$this->christmas(), $this->ldt(2025, 12, 26, 0, 0), false, $this->christmas(2025)],
            [$this->turnOfTheYear(), $this->ldt(2025, 12, 31, 0, 0), false, $this->turnOfTheYear(2025)],
            [$this->turnOfTheYear(), $this->ldt(2026, 1, 1, 0, 0), false, $this->turnOfTheYear(2025)],
            [$this->cards(), $this->ldt(2025, 4, 1, 0, 0), false, null],
            [$this->cards(), $this->ldt(2025, 5, 1, 0, 0), false, $this->cards(2025, 5, 1, 19, 45)],
            [$this->cards(), $this->ldt(2025, 4, 18, 0, 0), false, null],
            [$this->cards(), $this->ldt(2025, 6, 19, 0, 0), false, null],
            [$this->lunchBreak(), $this->ldt(2025, 4, 29, 0, 0), false,
                Event::create("2025-04-29", "2025-04-29", "12:00", "13:00", "Lunch break", "", "", "", "", "", "")],
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
                [$this->christmas(2025), $this->ldt(2025, 12, 24, 0, 0)],
            ], [
                $this->christmas(),
                $this->ldt(2025, 12, 26, 0, 0),
                [$this->christmas(2025), $this->ldt(2025, 12, 26, 23, 59)],
            ], [
                $this->christmas(),
                $this->ldt(2025, 12, 27, 0, 0),
                [$this->christmas(2026), $this->ldt(2026, 12, 24, 0, 0)],
            ], [
                $this->turnOfTheYear(),
                $this->ldt(2026, 1, 1, 0, 0),
                [$this->turnofTheYear(2025), $this->ldt(2026, 1, 1, 23, 59)],
            ], [
                $this->cards(),
                $this->ldt(2025, 4, 24, 0, 0),
                [$this->cards(2025, 4, 24), $this->ldt(2025, 4, 24, 19, 45)],
            ], [
                $this->cards(),
                $this->ldt(2025, 4, 24, 21, 0),
                [$this->cards(2025, 4, 24), $this->ldt(2025, 4, 24, 22, 15)],
            ],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 0, 0), [
                Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "", "", ""),
                $this->ldt(2025, 4, 25, 12, 0),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 12, 30), [
                Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "", "", ""),
                $this->ldt(2025, 4, 25, 13, 0),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 26, 0, 0), [
                Event::create("2025-04-26", "2025-04-26", "12:00", "13:00", "Lunch break", "", "", "", "", "", "", ""),
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
                Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "yearly", "2024-12-24", "111"),
                Event::create("2025-12-24", "2025-12-26", "", "", "Christmas", "", "", "", "", "", "111"),
                Event::create("2026-12-24", "2026-12-26", "", "", "Christmas", "", "", "", "yearly", "", "111"),
            ]],
            [$this->christmas(), $this->ldt(2000, 12, 24, 0, 0), [
                null,
                Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "", "", "111"),
                Event::create("2001-12-24", "2001-12-26", "", "", "Christmas", "", "", "", "yearly", "", "111"),
            ]],
            [
                Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "yearly", "2024-12-24", ""),
                $this->ldt(2024, 12, 24, 0, 0), [
                Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "yearly", "2023-12-24", "111"),
                Event::create("2024-12-24", "2024-12-26", "", "", "Christmas", "", "", "", "", "", "111"),
                null]
            ],
            [
                Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "yearly", "2024-12-24", ""),
                $this->ldt(2025, 12, 24, 0, 0), [null, null, null,]
            ],
            [$this->christmas(), $this->ldt(2025, 12, 23, 0, 0), [null, null, null]],
            [$this->intfcb(), $this->ldt(2025, 4, 16, 22, 0), [null, null, null]],
            [$this->cards(), $this->ldt(2025, 5, 1, 0, 0), [
                Event::create("2025-04-17", "2025-04-17", "19:45", "22:15", "Cards", "", "", "", "weekly", "2025-04-24", "111"),
                Event::create("2025-05-01", "2025-05-01", "19:45", "22:15", "Cards", "", "", "", "", "", "111"),
                Event::create("2025-05-08", "2025-05-08", "19:45", "22:15", "Cards", "", "", "", "weekly", "2025-06-12", "111"),
            ]],
            [$this->cards(), $this->ldt(2025, 4, 17, 0, 0), [
                null,
                Event::create("2025-04-17", "2025-04-17", "19:45", "22:15", "Cards", "", "", "", "", "", "111"),
                Event::create("2025-04-24", "2025-04-24", "19:45", "22:15", "Cards", "", "", "", "weekly", "2025-06-12", "111"),
            ]],
            [$this->cards(), $this->ldt(2025, 6, 12, 0, 0), [
                Event::create("2025-04-17", "2025-04-17", "19:45", "22:15", "Cards", "", "", "", "weekly", "2025-06-05", "111"),
                Event::create("2025-06-12", "2025-06-12", "19:45", "22:15", "Cards", "", "", "", "", "", "111"),
                null,
            ]],
            [$this->cards(), $this->ldt(2025, 4, 18, 0, 0), [null, null, null]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 30, 0, 0), [
                Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "daily", "2025-04-29", "111"),
                Event::create("2025-04-30", "2025-04-30", "12:00", "13:00", "Lunch break", "", "", "", "", "", "111"),
                Event::create("2025-05-01", "2025-05-01", "12:00", "13:00", "Lunch break", "", "", "", "daily", "2025-05-03", "111"),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 4, 25, 0, 0), [
                null,
                Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "", "", "111"),
                Event::create("2025-04-26", "2025-04-26", "12:00", "13:00", "Lunch break", "", "", "", "daily", "2025-05-03", "111"),
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 5, 3, 0, 0), [
                Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "daily", "2025-05-02", "111"),
                Event::create("2025-05-03", "2025-05-03", "12:00", "13:00", "Lunch break", "", "", "", "", "", "111"),
                null,
            ]],
            [$this->lunchBreak(), $this->ldt(2025, 5, 4, 0, 0), [null, null, null]],
        ];
    }

    public function testGH98()
    {
        $sut = Event::create("2026-04-16", "", "", "", "Someone not yet born", "", "", "###", "", "", "");
        $now = $this->ldt(2025, 4, 16, 0, 0);
        $this->assertEmpty($sut->occurrencesDuring(2025, 4));
        $this->assertNull($sut->occurrenceOn($now, true));
        $this->assertEquals([null, null], $sut->earliestOccurrenceAfter($now));
    }

    private function cmb(int $year = 1969): Event
    {
        $event = Event::create("1969-03-24", "1969-03-24", "", "", "cmb", "", "", "###", "", "", "");
        assert($event instanceof BirthdayEvent);
        if ($year !== 1969) {
            $event = $event->occurrenceStartingAt($this->ldt($year, 3, 24, 0, 0));
        }
        return $event;
    }

    private function intfcb(): Event
    {
        return Event::create(
            "2025-04-16",
            "2025-04-16",
            "21:00",
            "22:45",
            "#INTFCB",
            "",
            "",
            "Guiseppe-Meazza-Stadion",
            "",
            "",
            ""
        );
    }

    private function easter(): Event
    {
        return Event::create("2025-04-20", "2025-04-21", "", "", "easter", "", "", "", "", "", "");
    }

    private function christmas(?int $year = null): Event
    {
        if ($year === null) {
            return Event::create("2000-12-24", "2000-12-26", "", "", "Christmas", "", "", "", "yearly", "", "");
        }
        return Event::create("$year-12-24", "$year-12-26", "", "", "Christmas", "", "", "", "", "", "");
    }

    private function turnOfTheYear(?int $year = null): Event
    {
        if ($year === null) {
            return Event::create("2000-12-31", "2001-01-01", "", "", "Turn of the year", "", "", "", "yearly", "", "");
        }
        $nextYear = $year + 1;
        return Event::create("$year-12-31", "{$nextYear}-01-01", "", "", "Turn of the year", "", "", "", "", "", "");
    }

    private function cards(?int $year = null, ?int $month = null, ?int $day = null): Event
    {
        if ($year === null && $month === null && $day === null) {
            return Event::create(
                "2025-04-17",
                "2025-04-17",
                "19:45",
                "22:15",
                "Cards",
                "",
                "",
                "",
                "weekly",
                "2025-06-12",
                ""
            );
        }
        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
        return Event::create($date, $date, "19:45", "22:15", "Cards", "", "", "", "", "", "");
    }

    private function lunchBreak(): Event
    {
        return Event::create("2025-04-25", "2025-04-25", "12:00", "13:00", "Lunch break", "", "", "", "daily", "2025-05-03", "");
    }

    private function lunchBreaks(LocalDateTime $from, LocalDateTime $to): array
    {
        $res = [];
        $day = new Interval(1, 0, 0);
        while ($from->compareDate($to) <= 0) {
            $date = $from->getIsoDate();
            $res[] = Event::create($date, $date, "12:00", "13:00", "Lunch break", "", "", "", "", "", "");
            $from = $from->plus($day);
        }
        return $res;
    }

    private function ldt(int $year, int $month, int $day, int $hour, int $minute): LocalDateTime
    {
        return new LocalDateTime($year, $month, $day, $hour, $minute);
    }
}
