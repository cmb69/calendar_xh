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

namespace Calendar;

use PHPUnit\Framework\TestCase;

class ICalendarParserTest extends TestCase
{
    public function testRead()
    {
        $lines = file(__DIR__ . '/ics/basic.ics', FILE_IGNORE_NEW_LINES);
        $subject = new ICalendarParser();
        $actual = $subject->parse($lines);
        $this->assertContainsOnlyInstancesOf(Event::class, $actual);
        $this->assertCount(2, $actual);

        $first = $actual[0];
        $this->assertSame(0, (new LocalDateTime(1997, 7, 14, 17, 0))->compare($first->start));
        $this->assertSame(0, (new LocalDateTime(1997, 7, 15, 3, 59))->compare($first->end));
        $this->assertSame("Bastille Day Party", $first->summary);
        $this->assertSame("", $first->linkadr);
        $this->assertSame("", $first->linktxt);
        $this->assertSame("Place de la Bastille", $first->location);

        $second = $actual[1];
        $this->assertSame(0, (new LocalDateTime(1969, 3, 24, 0, 0))->compare($second->start));
        $this->assertSame(0, (new LocalDateTime(1969, 3, 24, 23, 59))->compare($second->end));
        $this->assertSame("cmb", $second->summary);
        $this->assertSame("", $second->linkadr);
        $this->assertSame("", $second->linktxt);
        $this->assertSame("", $second->location);
    }
}
