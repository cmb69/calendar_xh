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
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class NextEventControllerTest extends TestCase
{
    /** @var EventDataService&Stub */
    private $eventDataService;

    public function setUp(): void
    {
        $this->eventDataService = $this->createStub(EventDataService::class);
    }

    public function testRendersNoEvent(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("1965-04-16T20:38:00+02:00")]);
        $response = $this->sut()->defaultAction($request);
        $this->assertStringContainsString("No further event scheduled.", $response);
    }

    public function testRendersEventBeforeStart(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersMultidayEventBeforeStart(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningEvent(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T21:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningMultidayEvent(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-20T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue51(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-23T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue70(): void
    {
        $this->eventDataService->method("readEvents")->willReturn(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-25T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    private function sut(): NextEventController
    {
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['calendar'];
        $orientation = XH_includeVar("./config/config.php", "plugin_cf")["calendar"]["nextevent_orientation"];
        $dateTimeFormatter = new DateTimeFormatter($lang);
        $view = new View("./views/", $lang);
        return new NextEventController($orientation, $this->eventDataService, $dateTimeFormatter, $view);
    }

    private function cmb(): Event
    {
        return new Event(
            new LocalDateTime(1969, 3, 24, 0, 0),
            new LocalDateTime(1969, 3, 24, 23, 59),
            "cmb",
            "",
            "",
            "###"
        );
    }

    private function intfcb(): Event
    {
        return new Event(
            new LocalDateTime(2025, 4, 16, 21, 0),
            new LocalDatetime(2025, 4, 16, 22, 45),
            "#INTFCB",
            "",
            "",
            "Guiseppe-Meazza-Stadion"
        );
    }

    private function easter(): Event
    {
        return new Event(
            new LocalDateTime(2025, 4, 20, 0, 0),
            new LocalDateTime(2025, 4, 21, 23, 59),
            "easter",
            "",
            "",
            ""
        );
    }
}
