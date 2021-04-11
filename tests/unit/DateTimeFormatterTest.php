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

class DateTimeFormatterTest extends TestCase
{
    public function testFormatMonthYear()
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_month_year' => "%F %Y",
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatMonthYear(4, 2021);
        $this->assertSame("April 2021", $actual);
    }

    public function testFormatDate(): void
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_date' => "%j. %n. %Y"
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatDate(new LocalDateTime(2021, 4, 3, 14, 2));
        $this->assertSame("3. 4. 2021", $actual);
    }

    public function testFormatDateTime(): void
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_date_time' => "%j. %n. %Y %G:%i"
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatDateTime(new LocalDateTime(2021, 4, 3, 14, 2));
        $this->assertSame("3. 4. 2021 14:02", $actual);
    }

    public function testFormatTime(): void
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_time' => "%G:%i"
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatTime(new LocalDateTime(2021, 4, 3, 14, 2));
        $this->assertSame("14:02", $actual);
    }
}
