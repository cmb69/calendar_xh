<?php

/**
 * Copyright 2021 Christoph M. Becker
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

namespace Calendar;

use PHPUnit\Framework\TestCase;

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
        $this->assertSame(3, $subject->month);
        $this->assertSame(1, $subject->day);
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
}
