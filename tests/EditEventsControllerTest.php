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
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\Random;
use Plib\View;

class EditEventsControllerTest extends TestCase
{
    /** @var EventDataService&MockObject */
    private $eventDataService;

    /** @var CsrfProtector&MockObject */
    private $csrfProtector;

    /** @var Random&Stub */
    private $random;

    /** @var Editor&MockObject */
    private $editor;

    public function setUp(): void
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["111" => $this->lunchBreak()]));
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("42881056d048537da0e061f7f672854b");
        $this->csrfProtector->method("check")->willReturn(true);
        $this->random = $this->createStub(Random::class);
        $this->editor = $this->createMock(Editor::class);
    }

    private function sut(): EditEventsController
    {
        $view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["calendar"]);
        return new EditEventsController(
            "./",
            XH_includeVar("./config/config.php", "plugin_cf")["calendar"],
            $this->eventDataService,
            $this->csrfProtector,
            $this->random,
            $this->editor,
            $view
        );
    }

    public function testDefaultActionRendersHtml()
    {
        $response = $this->sut()(new FakeRequest(["time" => 1675088820]));
        Approvals::verifyHtml($response->output());
    }

    public function testCreateActionRendersHtml()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=create",
            "time" => 1675088820,
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testGenerateIdsActionRendersForm()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=generate_ids",
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testSingleActionRedirectsOnUnknownEvent()
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=edit_single&event_id=invalid%20id",
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testSingleActionRedirectsOnNonRecurringEvent()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=edit_single&event_id=111",
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testSingleActionRendersEditSingleForm()
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=edit_single&event_id=222",
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testUpdateActionRedirectsOnUnknowEvent()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=update&event_id=invalid%20id",
            "time" => 1675088820,
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testUpdateActionRendersEditFormOnKnownEvent()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=update&event_id=111",
            "time" => 1675088820,
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testDeleteActionRedirectsOnUnknowEvent()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=delete&event_id=invalid%20id",
            "time" => 1675088820,
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDeleteActionRendersEditFormOnKnownEvent()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=delete&event_id=111",
            "time" => 1675088820,
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testDoCreateActionRedirects()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=create",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoGenerateIdsActionIsCsrfProtected(): void
    {
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=generate_ids",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("You are not authorized for this action!", $response->output());
    }

    public function testDoGenerateIdsActionReportsFailureToSave(): void
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        // $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $this->eventDataService->method("writeEvents")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=generate_ids",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("ERROR: could not save event data.", $response->output());
    }

    public function testDoGenerateIdsActionRedirectsOnSuccess(): void
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        // $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $this->eventDataService->method("writeEvents")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=generate_ids",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoEditSingleActionIsCsrfProtected(): void
    {
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=edit_single",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("You are not authorized for this action!", $response->output());
    }

    public function testDoEditSingleActionRedirectsOnInvalidEvent(): void
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=edit_single&event_id=invalid%20id",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoEditSingleActionReportsFailureToSplit(): void
    {
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=edit_single&event_id=222",
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("There is no occurrence of the event on this date!", $response->output());
    }

    public function testDoEditSingleActionReportsFailureToSave(): void
    {
        $this->markTestSkipped("need to go through real Calendar");
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $this->eventDataService->method("writeEvents")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=edit_single&event_id=222",
            "post" => [
                "editdate" => "2025-12-25",
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("ERROR: could not save event data.", $response->output());
    }

    public function testDoEditSingleActionRedirectsOnSuccess(): void
    {
        $this->markTestSkipped("need to go through real Calendar");
        $this->eventDataService = $this->createMock(EventDataService::class);
        $this->eventDataService->method("readEvents")->willReturn(new Calendar(["222" => $this->christmas()]));
        $this->eventDataService->method("writeEvents")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=edit_single&event_id=222",
            "post" => [
                "editdate" => "2025-12-25",
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertSame("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionRedirectsOnInvalidEvent()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=update&event_id=invalid%20id",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionSavesEventAndRedirectsOnSuccess()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with(["111" => $this->lunchBreak()])
            ->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=update&event_id=111",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
                "datestart" => "2023-01-04T12:00",
                "dateend" => "2023-01-04T13:00",
                "event" => "Lunch break",
                "linkadr" => "http://example.com/lunchbreak",
                "linktxt" => "Tips for lunch breaks",
                "location" => "whereever I am",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionShowsErrorOnFailureToUpdateEvent()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with(["111" => $this->lunchBreak()])
            ->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=update&event_id=111",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
                "datestart" => "2023-01-04T12:00",
                "dateend" => "2023-01-04T13:00",
                "event" => "Lunch break",
                "linkadr" => "http://example.com/lunchbreak",
                "linktxt" => "Tips for lunch breaks",
                "location" => "whereever I am",
            ],
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testDoDeleteActionRedirectsOnUnknowEvent()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=delete&event_id=invalid%20id",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoDeleteActionDeletesEventAndRedirectsOnSuccess()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with([])->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?calendar&admin=plugin_main&action=delete&event_id=111",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoDeleteActionShowsErrorOnFailureToDeleteEvent()
    {
        $this->csrfProtector->expects($this->once())->method("check");
        $this->eventDataService->expects($this->once())->method("writeEvents")->with([])->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&admin=plugin_main&action=delete&event_id=111",
            "time" => 1675088820,
            "post" => [
                "calendar_do" => "",
            ],
        ]);
        $response = $this->sut()($request);
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
            "whereever I am",
            "",
            "",
            ""
        );
    }

    private function christmas(): Event
    {
        return Event::create("2020-12-25", "2020-12-26", "", "", "Christmas", "", "", "", "yearly", "", "");
    }
}
