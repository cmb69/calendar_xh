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
use Calendar\Infra\DateTimeFormatter;
use Calendar\Infra\EventDataService;
use Calendar\Model\Calendar;
use Calendar\Model\CalendarRepo;
use Calendar\Model\Event;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class NextEventControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var CalendarRepo */
    private $calendarRepo;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["calendar"];
        $this->lang = XH_includeVar("./languages/en.php", 'plugin_tx')["calendar"];
        $this->calendarRepo = new CalendarRepo(vfsStream::url("root/"), ".");
        $this->dateTimeFormatter = new DateTimeFormatter($this->lang);
        $this->view = new View("./views/", $this->lang);
    }

    private function sut(): NextEventController
    {
        return new NextEventController($this->conf, $this->calendarRepo, $this->dateTimeFormatter, $this->view);
    }

    public function testRendersNoEvent(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("1965-04-16T20:38:00+02:00")]);
        $response = $this->sut()->defaultAction($request);
        $this->assertStringContainsString("No further event scheduled.", $response);
    }

    public function testRendersEventBeforeStart(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersMultidayEventBeforeStart(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningEvent(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T21:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningMultidayEvent(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-20T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue51(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-23T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue70(): void
    {
        $this->calendarRepo->save(Calendar::fromEvents([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-25T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    private function cmb(): Event
    {
        return Event::create("1969-03-24", "1969-03-24", "", "", "cmb", "", "", "###", "", "", "");
    }

    private function intfcb(): Event
    {
        return Event::create(
            "2025-04-16",
            "2025-04-16",
            "21:00",
            "22:45",
            "#INTFCB",
            "",
            "",
            "Guiseppe-Meazza-Stadion",
            "",
            "",
            ""
        );
    }

    private function easter(): Event
    {
        return Event::create("2025-04-20", "2025-04-21", "", "", "easter", "", "", "", "", "", "");
    }
}
