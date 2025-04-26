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
use Calendar\Infra\Counter;
use Calendar\Infra\DateTimeFormatter;
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Calendar\Model\NoRecurrence;
use Calendar\Model\YearlyRecurrence;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class CalendarControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string */
    private $lang;

    /** @var DocumentStore */
    private $store;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var Counter */
    private $counter;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup();
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["calendar"];
        $this->lang = XH_includeVar("./languages/en.php", "plugin_tx")["calendar"];
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $calendar = Calendar::updateIn($this->store);
        $calendar->addEvent("111", $this->lunchBreak()->toDto());
        $calendar->addEvent("222", $this->weekend()->toDto());
        $calendar->addEvent("333", $this->birthday()->toDto());
        $this->store->commit();
        $this->dateTimeFormatter = new DateTimeFormatter($this->lang);
        $this->view = new View("./views/", $this->lang);
        $this->counter = new Counter(1);
    }

    private function sut(): CalendarController
    {
        return new CalendarController(
            "./",
            $this->conf,
            $this->store,
            $this->dateTimeFormatter,
            1,
            $this->counter,
            $this->view
        );
    }

    public function testDefaultActionRendersHtml()
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?page",
            "time" => strtotime("2023-01-30T14:27:00+00:00"),
        ]);
        $response = $this->sut()(0, 0, "", false, $request);
        Approvals::verifyHtml($response->output());
    }

    /** @dataProvider monthData */
    public function testShowsMonthAsRequested(int $month, int $year, string $url, string $expected): void
    {
        $request = new FakeRequest(["url" => $url, "time" => strtotime("2023-01-30T14:27:00+00:00")]);
        $response = $this->sut()($year, $month, "", false, $request);
        $this->assertStringContainsString($expected, $response->output());
    }

    public function monthData(): array
    {
        return [
            [
                0, 0,
                "http://example.com/?Events",
                "January 2023",
            ],
            [
                0, 2020,
                "http://example.com/?Events",
                "January 2020",
            ],
            [
                3, 2020,
                "http://example.com/?Events",
                "March 2020",
            ],
            [
                0, 0,
                "http://example.com/?Events&year=2021&month=7",
                "July 2021",
            ],
            [
                8, 2020,
                "http://example.com/?Events&year=2021&month=7",
                "August 2020",
            ],
            [
                13, -1,
                "http://example.com/?Events&year=2021&month=7",
                "December 0001",
            ],
        ];
    }

    public function testFailsToRenderBigCalendarIfEventPagesAreDisabled(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?page",
            "time" => strtotime("2023-01-30T14:27:00+00:00"),
        ]);
        $response = $this->sut()(0, 0, "", true, $request);
        $this->assertStringContainsString("Big calendars require event pages to be enabled!", $response->output());
    }

    public function testRendersBigCalendar(): void
    {
        $this->conf["event_allow_single"] = "true";
        $request = new FakeRequest([
            "url" => "http://example.com/?page",
            "time" => strtotime("2023-01-30T14:27:00+00:00"),
        ]);
        $response = $this->sut()(0, 0, "", true, $request);
        Approvals::verifyHtml($response->output());
    }

    private function lunchBreak(): Event
    {
        $start = new LocalDateTime(2023, 1, 4, 12, 0);
        $end = new LocalDateTime(2023, 1, 4, 13, 0);
        $url = "http://example.com/lunchbreak";
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Lunch break", $url, "Tips for lunch breaks", "whereever I am", $recurrence);
    }

    private function weekend(): Event
    {
        $start = new LocalDateTime(2023, 1, 7, 0, 0);
        $end = new LocalDateTime(2023, 1, 8, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Weekend", "", "", "", $recurrence);
    }

    private function birthday(): Event
    {
        $start = new LocalDateTime(2000, 1, 1, 0, 0);
        $end = new LocalDateTime(2000, 1, 1, 23, 59);
        $recurrence = new YearlyRecurrence($start, $end, null);
        return new Event("", $start, $end, "Millenium", "", "", "###", $recurrence);
    }
}
