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

use ApprovalTests\Approvals;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class EventDataServiceTest extends TestCase
{
    public function testMissingEventFileReadsEmptyArray()
    {
        $root = vfsStream::setup("root");
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $events = $subject->readEvents()->events();
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    public function testEmptyEventFileReadsEmptyArray()
    {
        vfsStream::setup("root");
        vfsStream::newFile("root/calendar.2.6.csv");
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $events = $subject->readEvents()->events();
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
        file_put_contents(vfsStream::url("root/calendar.2.6.csv"), $csv);
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $events = array_values($subject->readEvents()->eventsDuring(2021, 4));
        $this->assertCount(2, $events);
        $this->assertSame("markus", $events[0]->summary());
        $this->assertSame("martin", $events[1]->summary());
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
        file_put_contents(vfsStream::url("root/calendar.2.6.csv"), $csv);
        $subject = new EventDataService(vfsStream::url("root/"), "-");
        $nextevent = $subject->readEvents()->nextEvent($now);
        if ($expected !== null) {
            $this->assertInstanceOf(Event::class, $nextevent);
            $this->assertSame($expected, $nextevent->summary());
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
            [new LocalDateTime(2021, 4, 24, 0, 0), "cmb"],
        ];
    }

    public function testReadingOfLegacyDataFiles(): void
    {
        $csv = <<<CSV
04.03.2025,04.03.2025,13:00;Lunch Break;here;http://example.com/,Lunch break tips;12:00
05.03.2025;Calendar_XH Release;Wonderland;int:Start;
06.03.1950;Schorsch;###;ext:example.com/Schorsch;
CSV;
        vfsStream::setup("root");
        file_put_contents(vfsStream::url("root/calendar.txt"), $csv);
        $subject = new EventDataService(vfsStream::url("root/"), ".");
        $subject->readEvents();
        $actual = file_get_contents(vfsStream::url("root/calendar.txt"));
        Approvals::verifyStringWithFileExtension($actual, "txt");
    }
}
