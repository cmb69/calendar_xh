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
class LocalDateTimeTest extends TestCase
{
    /**
     * @dataProvider isoStringProvider
     * @param string $string
     * @param bool $valid
     */
    public function testFromIsoString($string, $valid): void
    {
        $actual = LocalDateTime::fromIsoString($string);
        if ($valid) {
            $this->assertInstanceOf(LocalDateTime::class, $actual);
        } else {
            $this->assertNull($actual);
        }
    }

    public function isoStringProvider(): array
    {
        return [
            ["1969-03-24T10:10", true],
            ["1969-03-24", false], // missing time
            ["1969-03-24T10:10:10", false], // time has seconds
            ["2021-02-29T00:00", false], // not a leap year
            ["2021-04-03T24:00", false], // max 23:59
        ];
    }

    public function testWithYearHandlesLeapDays(): void
    {
        $subject = (new LocalDateTime(2020, 2, 29, 0, 0))->withYear(2021);
        $this->assertSame(3, $subject->month());
        $this->assertSame(1, $subject->day());
    }

    /**
     * @dataProvider compareDateProvider
     * @param int $expected
     */
    public function testCompareDate(LocalDateTime $one, LocalDateTime $other, $expected): void
    {
        $this->assertSame($expected, $one->compareDate($other));
    }

    public function compareDateProvider(): array
    {
        return [
            [new LocalDateTime(2021, 4, 3, 18, 50), new LocalDateTime(2021, 4, 3, 0, 0), 0],
            [new LocalDateTime(2020, 4, 3, 0, 0), new LocalDateTime(2021, 4, 3, 0, 0), -1],
            [new LocalDateTime(2021, 4, 3, 0, 0), new LocalDateTime(2021, 3, 3, 0, 0), 1],
        ];
    }

    /** @dataProvider arithmeticData */
    public function testPlus(LocalDateTime $ldt1, Interval $interval, LocalDateTime $ldt2): void
    {
        $this->assertEquals($ldt2, $ldt1->plus($interval));
        $this->assertEquals($ldt1, $ldt2->plus($interval->negate()));
    }

    /** @dataProvider arithmeticData */
    public function testMinus(LocalDateTime $ldt1, Interval $interval, LocalDateTime $ldt2): void
    {
        $this->assertEquals($ldt1, $ldt2->minus($interval));
        $this->assertEquals($ldt1, $ldt2->minus($interval->negate()));
    }

    /** @dataProvider arithmeticData */
    public function testDiff(LocalDateTime $ldt1, Interval $interval, LocalDateTime $ldt2): void
    {
        $this->assertEquals($interval, $ldt2->diff($ldt1));
        $this->assertEquals($interval->negate(), $ldt1->diff($ldt2));
    }

    public function arithmeticData(): array
    {
        return [
            [$this->ldt(2025, 4, 17, 0, 0), new Interval(1, 1, 1), $this->ldt(2025, 4, 18, 1, 1)],
            [$this->ldt(2025, 4, 17, 0, 1), new Interval(1, 1, 59), $this->ldt(2025, 4, 18, 2, 0)],
            [$this->ldt(2025, 4, 17, 0, 1), new Interval(1, 23, 59), $this->ldt(2025, 4, 19, 0, 0)],
            [$this->ldt(2025, 4, 17, 0, 1), new Interval(13, 23, 59), $this->ldt(2025, 5, 1, 0, 0)],
            [$this->ldt(2025, 4, 17, 0, 1), new Interval(44, 23, 59), $this->ldt(2025, 6, 1, 0, 0)],
            [$this->ldt(2025, 12, 31, 0, 1), new Interval(1, 23, 59), $this->ldt(2026, 1, 2, 0, 0)],
            [$this->ldt(2025, 12, 31, 0, 1), new Interval(365, 23, 59), $this->ldt(2027, 1, 1, 0, 0)],
            [$this->ldt(2024, 2, 28, 0, 1), new Interval(0, 23, 59), $this->ldt(2024, 2, 29, 0, 0)],
        ];
    }

    /** @dataProvider plusMonthsData */
    public function testPlusMonths(LocalDateTime $now, int $months, LocalDateTime $expected): void
    {
        $actual = $now->plusMonths($months);
        $this->assertSame($expected->year(), $actual->year());
        $this->assertSame($expected->month(), $actual->month());
    }

    public function plusMonthsData(): array
    {
        return [
            [new LocalDateTime(2025, 4, 1, 0, 0), 0, new LocalDateTime(2025, 4, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), 3, new LocalDateTime(2025, 7, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), -3, new LocalDateTime(2025, 1, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), -4, new LocalDateTime(2024, 12, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), -20, new LocalDateTime(2023, 8, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), 9, new LocalDateTime(2026, 1, 1, 0, 0)],
            [new LocalDateTime(2025, 4, 1, 0, 0), 21, new LocalDateTime(2027, 1, 1, 0, 0)],
        ];
    }

    private function ldt(int $year, int $month, int $day, int $hour, int $minute): LocalDateTime
    {
        return new LocalDateTime($year, $month, $day, $hour, $minute);
    }
}
