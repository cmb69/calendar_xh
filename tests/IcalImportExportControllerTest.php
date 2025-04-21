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

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class IcalImportControllerTest extends TestCase
{
    public function testDefaultActionsRendersForms()
    {
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $icsFileFinder->method('all')->willReturn(["cal1.ics", "cal2.ics"]);
        $eventDataService = $this->createStub(EventDataService::class);
        $iCalendarWriter = $this->createStub(ICalendarWriter::class);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportExportController($icsFileFinder, $eventDataService, $iCalendarWriter, $view);
        $response = $sut(new FakeRequest([
            "url" => "http://example.com/?calendar&calendar_ignored=2",
        ]));
        $this->assertSame("Calendar – Import/Export", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testImportActionRedirects()
    {
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $icsFileFinder->method('read')->willReturn([]);
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->willReturn([]);
        $iCalendarWriter = $this->createStub(ICalendarWriter::class);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportExportController($icsFileFinder, $eventDataService, $iCalendarWriter, $view);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=import",
            "post" => ["calendar_ics" => "foo.ics"],
        ]);
        $response = $sut($request);
        $this->assertEquals(
            "http://example.com/?calendar&admin=import_export&calendar_ignored=0",
            $response->location()
        );
    }

    public function testSuccessfulExportRedirects()
    {
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->willReturn([]);
        $iCalendarWriter = $this->createStub(ICalendarWriter::class);
        $iCalendarWriter->method("write")->willReturn(true);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportExportController($icsFileFinder, $eventDataService, $iCalendarWriter, $view);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=export",
            "post" => ["calendar_ics" => "calendar.ics"],
        ]);
        $response = $sut($request);
        $this->assertEquals(
            "http://example.com/?calendar&admin=import_export",
            $response->location()
        );
    }

    public function testFailedExportShowsMessage()
    {
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->willReturn([]);
        $iCalendarWriter = $this->createStub(ICalendarWriter::class);
        $iCalendarWriter->method("write")->willReturn(false);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportExportController($icsFileFinder, $eventDataService, $iCalendarWriter, $view);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=export",
            "post" => ["calendar_ics" => "calendar.ics"],
        ]);
        $response = $sut($request);
        $this->assertSame("Calendar – Import/Export", $response->title());
        $this->assertStringContainsString("Could not export to calendar.ics!", $response->output());
    }
}
