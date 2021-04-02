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
}
