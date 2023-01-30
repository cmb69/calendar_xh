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
use org\bovigo\vfs\vfsStream;

class EventDataServiceTest extends TestCase
{
    public function testMissingEventFileIsCreated()
    {
        $root = vfsStream::setup("root");
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $subject->readEvents();
        $this->assertTrue($root->hasChild("calendar.csv"));
        $this->assertSame(0, $root->getChild("calendar.csv")->size());
    }

    public function testEmptyEventFileReadsEmptyArray()
    {
        vfsStream::setup("root");
        vfsStream::newFile("root/calendar.csv");
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $events = $subject->readEvents();
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    public function testFilterByMonthProperlySortsBirthdayEvents()
    {
        $csv = <<<CSV
1969-04-03;;;;martin;###;;
1971-04-02;;;;markus;###;;
CSV;
        vfsStream::setup("root");
        file_put_contents(vfsStream::url("root/calendar.csv"), $csv);
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $events = array_values($subject->filterByMonth($subject->readEvents(), 2021, 4));
        $this->assertCount(2, $events);
        $this->assertSame("markus", $events[0]->summary);
        $this->assertSame("martin", $events[1]->summary);
    }

    /**
     * @dataProvider findNextEventProvider
     * @param string|null $expected
     */
    public function testFindNextEvent(LocalDateTime $now, $expected): void
    {
        $csv = <<<CSV
1969-03-24;00:00;1969-03-24;23:59;cmb;###;;
2021-04-21;00:00;2021-04-23;23:59;multi day;;;
2021-04-21;10:30;2021-04-21;10:30;instant;;;
CSV;
        vfsStream::setup("root");
        file_put_contents(vfsStream::url("root/calendar.csv"), $csv);
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $nextevent = $subject->findNextEvent($subject->readEvents(), $now);
        if ($expected !== null) {
            $this->assertInstanceOf(Event::class, $nextevent);
            $this->assertSame($expected, $nextevent->summary);
        } else {
            $this->assertNull($nextevent);
        }
    }

    public function findNextEventProvider(): array
    {
        return [
            [new LocalDateTime(2021, 4, 4, 13, 36), "multi day"],
            [new LocalDateTime(2021, 4, 21, 1, 0), "instant"],
            [new LocalDateTime(2021, 3, 21, 0, 0), "cmb"],
            [new LocalDateTime(2021, 4, 24, 0, 0), null],
        ];
    }
}
