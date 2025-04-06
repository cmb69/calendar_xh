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
use XH\CSRFProtection as CsrfProtector;

class EditEventsControllerTest extends TestCase
{
    /** @var EditEventsController */
    private $sut;

    /** @var EventDataService&MockObject */
    private $eventDataService;

    /** @var CsrfProtector&MockObject */
    private $csrfProtector;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $dateTime = LocalDateTime::fromIsoString("2023-01-30T14:27");
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(["111" => $this->lunchBreak()]);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("tokenInput")->willReturn(
            "<input type=\"hidden\" name=\"xh_csrf_token\" value=\"42881056d048537da0e061f7f672854b\">"
        );
        $view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["calendar"]);
        $this->sut = new EditEventsController(
            "./",
            $conf,
            $dateTime,
            $this->eventDataService,
            $this->csrfProtector,
            $view,
            ""
        );
    }

    public function testDefaultActionRendersHtml()
    {
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testCreateActionRendersHtml()
    {
        $_GET = ["action" => "create"];
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testUpdateActionRedirectsOnUnknowEvent()
    {
        $_GET = ["action" => "update", "event_id" => "invalid id"];
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testUpdateActionRendersEditFormOnKnownEvent()
    {
        $_GET = ["action" => "update", "event_id" => "111"];
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testDeleteActionRedirectsOnUnknowEvent()
    {
        $_GET = ["action" => "delete", "event_id" => "invalid id"];
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDeleteActionRendersEditFormOnKnownEvent()
    {
        $_GET = ["action" => "delete", "event_id" => "111"];
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testDoCreateActionRedirects()
    {
        $_GET = ["action" => "create"];
        $_POST = ["foo" => "bar"];
        $this->csrfProtector->expects($this->once())->method("check");
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionRedirectsOnInvalidEvent()
    {
        $_GET = ["action" => "update", "event_id" => "invalid id"];
        $_POST = ["foo" => "bar"];
        $this->csrfProtector->expects($this->once())->method("check");
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionSavesEventAndRedirectsOnSuccess()
    {
        $_GET = ["action" => "update", "event_id" => "111"];
        $_POST = [
            "datestart" => "2023-01-04",
            "dateend" => "2023-01-04",
            "starttime" => "12:00",
            "endtime" => "13:00",
            "event" => "Lunch break",
            "linkadr" => "http://example.com/lunchbreak",
            "linktxt" => "Tips for lunch breaks",
            "location" => "whereever I am",
        ];
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with(["111" => $this->lunchBreak()])
            ->willReturn(true);
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionShowsErrorOnFailureToUpdateEvent()
    {
        $_GET = ["action" => "update", "event_id" => "111"];
        $_POST = [
            "datestart" => "2023-01-04",
            "dateend" => "2023-01-04",
            "starttime" => "12:00",
            "endtime" => "13:00",
            "event" => "Lunch break",
            "linkadr" => "http://example.com/lunchbreak",
            "linktxt" => "Tips for lunch breaks",
            "location" => "whereever I am",
        ];
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with(["111" => $this->lunchBreak()])
            ->willReturn(false);
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testDoDeleteActionRedirectsOnUnknowEvent()
    {
        $_GET = ["action" => "delete", "event_id" => "invalid id"];
        $_POST = ["foo" => "bar"];
        $this->csrfProtector->expects($this->once())->method("check");
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoDeleteActionDeletesEventAndRedirectsOnSuccess()
    {
        $_GET = ["action" => "delete", "event_id" => "111"];
        $_POST = ["foo" => "bar"];
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with([])->willReturn(true);
        $response = ($this->sut)(new FakeRequest());
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoDeleteActionShowsErrorOnFailureToDeleteEvent()
    {
        $_GET = ["action" => "delete", "event_id" => "111"];
        $_POST = ["foo" => "bar"];
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with([])->willReturn(false);
        $response = ($this->sut)(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    private function lunchBreak(): Event
    {
        return Event::create(
            "2023-01-04",
            "2023-01-04",
            "12:00",
            "13:00",
            "Lunch break",
            "http://example.com/lunchbreak",
            "Tips for lunch breaks",
            "whereever I am"
        );
    }
}
