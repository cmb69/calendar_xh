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
use Calendar\Model\BirthdayEvent;
use Calendar\Model\Calendar;
use Calendar\Model\CalendarRepo;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Calendar\Model\NoRecurrence;
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
        $this->calendarRepo->save(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("1965-04-16T20:38:00+02:00")]);
        $response = $this->sut()->defaultAction($request);
        $this->assertStringContainsString("No further event scheduled.", $response);
    }

    public function testRendersEventBeforeStart(): void
    {
        $this->calendarRepo->save(new Calendar([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersMultidayEventBeforeStart(): void
    {
        $this->calendarRepo->save(new Calendar([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningEvent(): void
    {
        $this->calendarRepo->save(new Calendar([$this->intfcb()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-16T21:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testRendersRunningMultidayEvent(): void
    {
        $this->calendarRepo->save(new Calendar([$this->easter()]));
        $request = new FakeRequest(["time" => strtotime("2025-04-20T20:38:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue51(): void
    {
        $this->calendarRepo->save(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-23T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue70(): void
    {
        $this->calendarRepo->save(new Calendar([$this->cmb()]));
        $request = new FakeRequest(["time" => strtotime("2021-03-25T12:34:00+00:00")]);
        $response = $this->sut()->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    private function cmb(): Event
    {
        $start = new LocalDateTime(1969, 3, 24, 0, 0);
        $end = new LocalDateTime(1969, 3, 24, 23, 59);
        return new BirthdayEvent("", $start, $end, "cmb", "", "");
    }

    private function intfcb(): Event
    {
        $start = new LocalDateTime(2025, 4, 16, 21, 0);
        $end = new LocalDateTime(2025, 4, 16, 22, 45);
        $location = "Guiseppe-Meazza-Stadion";
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "#INTFCB", "", "", $location, $recurrence);
    }

    private function easter(): Event
    {
        $start = new LocalDateTime(2025, 4, 20, 0, 0);
        $end = new LocalDateTime(2025, 4, 21, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "easter", "", "", "", $recurrence);
    }
}
