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

class ICalendarReaderTest extends TestCase
{
    public function testRead()
    {
        $subject = new ICalendarReader(__DIR__ . '/basic.ics', '-');
        $actual = $subject->read();
        $this->assertContainsOnlyInstancesOf(Event::class, $actual);
        $this->assertCount(1, $actual);
        $this->assertSame(0, (new LocalDateTime(1997, 7, 14, 17, 0))->compare($actual[0]->start));
        $this->assertSame(0, (new LocalDateTime(1997, 7, 15, 3, 59))->compare($actual[0]->end));
        $this->assertSame("Bastille Day Party", $actual[0]->summary);
        $this->assertSame("", $actual[0]->linkadr);
        $this->assertSame("", $actual[0]->linktxt);
        $this->assertSame("Place de la Bastille", $actual[0]->location);
    }
}
