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
use Calendar\Model\Html2Text;
use Calendar\Model\ICalRepo;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class IcalImportControllerTest extends TestCase
{
    /** @var ICalRepo&Stub */
    private $iCalRepo;

    /** @var DocumentStore */
    private $store;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->iCalRepo = new ICalRepo(vfsStream::url("root/"), "localhost", new Html2Text());
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")['calendar']);
    }

    private function sut(): IcalImportExportController
    {
        return new IcalImportExportController(
            $this->iCalRepo,
            $this->store,
            $this->view
        );
    }

    public function testDefaultActionsRendersForms()
    {
        touch(vfsStream::url("root/cal1.ics"));
        touch(vfsStream::url("root/cal2.ics"));
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&calendar_ignored=2",
        ]);
        $response = $this->sut()($request);
        $this->assertSame("Calendar – Import/Export", $response->title());
        Approvals::verifyHtml($response->output());
    }

    public function testImportActionRedirects()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=import",
            "post" => ["calendar_ics" => "foo.ics"],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals(
            "http://example.com/?calendar&admin=import_export&calendar_ignored=0",
            $response->location()
        );
    }

    public function testSuccessfulExportSavesAndRedirects()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=export",
            "post" => ["calendar_ics" => "calendar.ics"],
        ]);
        $response = $this->sut()($request);
        $this->assertFileExists(vfsStream::url("root/calendar.ics"));
        $this->assertEquals(
            "http://example.com/?calendar&admin=import_export",
            $response->location()
        );
    }

    public function testFailedExportReportsError()
    {
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=import_export&action=export",
            "post" => ["calendar_ics" => "calendar.ics"],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("Calendar – Import/Export", $response->title());
        $this->assertStringContainsString("Could not export to calendar.ics!", $response->output());
    }
}
