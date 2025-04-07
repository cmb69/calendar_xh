<?php

/**
 * Copyright 2023 Christoph M. Becker
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

class IcsFileFinderTest extends TestCase
{
    public function testFindsAllFiles(): void
    {
        $sut = new IcsFileFinder(__DIR__ . "/ics/");
        $files = $sut->all();
        $this->assertEquals(["basic.ics"], $files);
    }

    public function testReadsLines(): void
    {
        $sut = new IcsFileFinder(__DIR__ . "/ics/");
        $lines = $sut->read("basic.ics");
        $expected = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
            'BEGIN:VEVENT',
            'UID:uid1@example.com',
            'DTSTAMP:19970714T170000Z',
            'ORGANIZER;CN=John Doe:MAILTO:john.doe@example.com',
            'DTSTART:19970714T170000Z',
            'DTEND:19970715T035959Z',
            'SUMMARY:Bastille Day Party',
            'LOCATION:Place de la Bastille',
            'GEO:48.85299;2.36885',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'DTSTART;VALUE=DATE:19690324',
            'SUMMARY:cmb',
            'LOCATION:a\\\\\\\\b\;c\,d\Ne\nf',
            'URL:https://3-magi.net/',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'UID:event_65843ff6ae2ef',
            'SEQUENCE:25',
            'LAST-MODIFIED;TZID=Europe/Berlin:20250204T124056',
            'DTSTAMP;TZID=Europe/Berlin:20250204T124056',
            'CREATED;TZID=Europe/Berlin:20231221T141700',
            'SUMMARY:Digitale Reise Bubenheim',
            'DESCRIPTION:Digitale Reise Bubenheim',
            'LOCATION:55270 Bubenheim Schulstraße 2 Dorfgemeinschaftshaus ',
            'URL:https://www.digibos.org',
            'DTSTART;TZID=Europe/Berlin:20240123T150000',
            'DTEND;TZID=Europe/Berlin:20240123T170000',
            'TRANSP:TRANSPARENT',
            'STATUS:CONFIRMED',
            'CLASS:PUBLIC',
            'END:VEVENT',
            'END:VCALENDAR',
        ];
        $this->assertEquals($expected, $lines);
    }
}
