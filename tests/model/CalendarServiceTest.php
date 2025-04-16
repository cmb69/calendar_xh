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

class CalendarServiceTest extends TestCase
{
    public function testMonthMatrixForMarch2021()
    {
        $calendar = new CalendarService(true);
        $expected = [
            [   1,    2,    3,    4,    5,    6,    7],
            [   8,    9,   10,   11,   12,   13,   14],
            [  15,   16,   17,   18,   19,   20,   21],
            [  22,   23,   24,   25,   26,   27,   28],
            [  29,   30,   31, null, null, null, null],
        ];
        $this->assertEquals($expected, $calendar->getMonthMatrix(2021, 3));
    }

    public function testMonthMatrixForMarch2021WhereWeekStartsSunday()
    {
        $calendar = new CalendarService(false);
        $expected = [
            [null,    1,    2,    3,    4,    5,    6],
            [   7,    8,    9,   10,   11,   12,   13],
            [  14,   15,   16,   17,   18,   19,   20],
            [  21,   22,   23,   24,   25,   26,   27],
            [  28,   29,   30,   31, null, null, null],
        ];
        $this->assertEquals($expected, $calendar->getMonthMatrix(2021, 3));
    }
}
