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
        $subject = Event::create("2021-04-04", "", "", "", "Easter", "", "", "");
        $this->assertTrue($subject->isFullDay());
    }

    /** @dataProvider occursDuringData */
    public function testOccursDuring(Event $sut, int $year, int $month, bool $expected): void
    {
        $this->assertSame($expected, $sut->occursDuring($year, $month));
    }

    public function occursDuringData(): array
    {
        return [
            [$this->intfcb(), 2025, 4, true],
            [$this->easter(), 2025, 4, true],
        ];
    }

    /** @dataProvider occursOnData */
    public function testOccursOn(Event $sut, LocalDateTime $day, bool $daysBetween, bool $expected): void
    {
        $this->assertSame($expected, $sut->occursOn($day, $daysBetween));
    }

    public function occursOnData(): array
    {
        return [
            [$this->intfcb(), new LocalDateTime(2025, 4, 16, 0, 0), true, true],
            [$this->easter(), new LocalDateTime(2025, 4, 20, 0, 0), true, true],
            [$this->easter(), new LocalDateTime(2025, 4, 20, 0, 0), false, true],
        ];
    }

    /** @dataProvider afterData */
    public function testAfter(Event $sut, LocalDateTime $date, ?LocalDateTime $expected): void
    {
        $this->assertEquals($expected, $sut->after($date));
    }

    public function afterData(): array
    {
        return [
            [$this->cmb(), new LocalDateTime(2025, 3, 20, 0, 0), new LocalDateTime(2025, 3, 24, 0, 0)],
            [$this->cmb(), new LocalDateTime(2025, 3, 25, 0, 0), new LocalDateTime(2026, 3, 24, 0, 0)],
            [$this->easter(), new LocalDateTime(2025, 4, 20, 0, 0), new LocalDateTime(2025, 4, 20, 0, 0)],
            [$this->easter(), new LocalDateTime(2025, 4, 21, 0, 0), new LocalDateTime(2025, 4, 21, 23, 59)],
            [$this->easter(), new LocalDateTime(2025, 4, 22, 0, 0), null],
        ];
    }

    public function testGH98()
    {
        $sut = Event::create("2026-04-16", "", "", "", "Someone not yet born", "", "", "###");
        $now = new LocalDateTime(2025, 4, 16, 0, 0);
        $this->assertFalse($sut->occursDuring(2025, 4));
        $this->assertFalse($sut->occursOn($now, true));
        $this->assertNull($sut->after($now));
    }

    private function cmb(): Event
    {
        return new Event(
            new LocalDateTime(1969, 3, 24, 0, 0),
            new LocalDateTime(1969, 3, 24, 23, 59),
            "cmb",
            "",
            "",
            "###"
        );
    }

    private function intfcb(): Event
    {
        return new Event(
            new LocalDateTime(2025, 4, 16, 21, 0),
            new LocalDatetime(2025, 4, 16, 22, 45),
            "#INTFCB",
            "",
            "",
            "Guiseppe-Meazza-Stadion"
        );
    }

    private function easter(): Event
    {
        return new Event(
            new LocalDateTime(2025, 4, 20, 0, 0),
            new LocalDateTime(2025, 4, 21, 23, 59),
            "easter",
            "",
            "",
            ""
        );
    }
}
