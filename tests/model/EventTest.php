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

    /** @dataProvider occurrenceDuringData */
    public function testoccurrenceDuring(Event $sut, int $year, int $month, ?Event $expected): void
    {
        $this->assertEquals($expected, $sut->occurrenceDuring($year, $month));
    }

    public function occurrenceDuringData(): array
    {
        return [
            [$this->cmb(), 2025, 3, $this->cmb(2025)],
            [$this->intfcb(), 2025, 4, $this->intfcb()],
            [$this->easter(), 2025, 4, $this->easter()],
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
        ];
    }

    public function testGH98()
    {
        $sut = Event::create("2026-04-16", "", "", "", "Someone not yet born", "", "", "###");
        $now = $this->ldt(2025, 4, 16, 0, 0);
        $this->assertNull($sut->occurrenceDuring(2025, 4));
        $this->assertNull($sut->occurrenceOn($now, true));
        $this->assertEquals([null, null], $sut->earliestOccurrenceAfter($now));
    }

    private function cmb(int $year = 1969): Event
    {
        $event = Event::create("1969-03-24", "1969-03-24", "", "", "cmb", "", "", "###");
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
            "Guiseppe-Meazza-Stadion"
        );
    }

    private function easter(): Event
    {
        return Event::create("2025-04-20", "2025-04-21", "", "", "easter", "", "", "");
    }

    private function ldt(int $year, int $month, int $day, int $hour, int $minute): LocalDateTime
    {
        return new LocalDateTime($year, $month, $day, $hour, $minute);
    }
}
