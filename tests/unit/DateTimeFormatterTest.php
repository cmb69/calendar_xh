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

    /**
     * @dataProvider formatDateProvider
     */
    public function testFormatDate(LocalDateTime $ldt, string $format, string $expected): void
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_date' => $format
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatDate($ldt);
        $this->assertSame($expected, $actual);
    }

    public function formatDateProvider(): array
    {
        return [
            [new LocalDateTime(2021, 4, 3, 14, 2), "%j. %n. %Y", "3. 4. 2021"],
            [new LocalDateTime(2021, 4, 3, 14, 2), "%d. %m. %Y", "03. 04. 2021"],
        ];
    }

    /**
     * @dataProvider formatDateTimeProvider
     */
    public function testFormatDateTime(LocalDateTime $ldt, string $format, string $expected): void
    {
        $lang = [
            'monthnames_array' => "January,February,March,April,May,June"
                . ",July,August,September,Oktober,November,December",
            'format_date_time' => $format
        ];
        $subject = new DateTimeFormatter($lang);
        $actual = $subject->formatDateTime($ldt);
        $this->assertSame($expected, $actual);
    }

    public function formatDateTimeProvider(): array
    {
        return [
            [new LocalDateTime(2021, 4, 3, 14, 2), "%j. %n. %Y %G:%i", "3. 4. 2021 14:02"],
            [new LocalDateTime(2021, 4, 3, 14, 2), "%d. %m. %Y %G:%i", "03. 04. 2021 14:02"],
        ];
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
