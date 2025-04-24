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

class EventListControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var DocumentStore */
    private $store;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["calendar"];
        $this->lang = XH_includeVar("./languages/en.php", "plugin_tx")["calendar"];
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->storeEvents([
            "111" => $this->lunchBreak(),
            "222" => $this->easter(),
            "333" => $this->birthday(),
            "444" => $this->nightShift(),
            "555" => $this->goodFriday(),
        ]);
        $this->dateTimeFormatter = new DateTimeFormatter($this->lang);
        $this->view = new View("./views/", $this->lang);
    }

    private function sut(): EventListController
    {
        return new EventListController(
            $this->conf,
            $this->store,
            $this->dateTimeFormatter,
            $this->view
        );
    }

    public function testRendersClassicEventListByDefault()
    {
        $request = new FakeRequest(["time" => strtotime("2023-01-30T14:27:00+00:00")]);
        Approvals::verifyHtml($this->sut()->defaultAction(0, 0, 0, 0, $request));
    }

    public function testRendersNewStyleEventListIfConfigured(): void
    {
        $this->conf["eventlist_template"] = "eventlist_new";
        $request = new FakeRequest(["time" => strtotime("2023-01-30T14:27:00+00:00")]);
        Approvals::verifyHtml($this->sut()->defaultAction(0, 0, 0, 0, $request));
    }

    /** @dataProvider intervalData */
    public function testShowsProperIntervalAsRequested(int $month, int $year, string $url, string $expected): void
    {
        $request = new FakeRequest(["url" => $url, "time" => strtotime("2023-01-30T14:27:00+00:00")]);
        $response = $this->sut()->defaultAction($month, $year, 0, 0, $request);
        $this->assertStringContainsString($expected, $response);
    }

    public function intervalData(): array
    {
        return [
            [
                0, 0,
                "http://example.com/?Events",
                "Events in the period from <span>January 2023</span> till <span>December 2023</span>",
            ],
            [
                0, 2020,
                "http://example.com/?Events",
                "Events in the period from <span>January 2020</span> till <span>December 2020</span>",
            ],
            [
                3, 2020,
                "http://example.com/?Events",
                "Events in the period from <span>March 2020</span> till <span>February 2021</span>",
            ],
            [
                0, 0,
                "http://example.com/?Events&year=2021&month=7",
                "Events in the period from <span>July 2021</span> till <span>June 2022</span>",
            ],
            [
                8, 2020,
                "http://example.com/?Events&year=2021&month=7",
                "Events in the period from <span>August 2020</span> till <span>July 2021</span>",
            ],
        ];
    }

    private function storeEvents(array $events): void
    {
        $calendar = Calendar::updateIn($this->store);
        foreach ($events as $id => $event) {
            $calendar->addEvent($id, $event->toDto());
        }
        $this->store->commit();
    }

    private function lunchBreak(): Event
    {
        $start = new LocalDateTime(2023, 1, 4, 12, 0);
        $end = new LocalDateTime(2023, 1, 4, 13, 0);
        $url = "http://example.com/lunchbreak";
        $description = "<a href=\"http://example.com/lunchbreak\">Tips for lunch breaks</a>";
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Lunch break", $url, $description, "whereever I am", $recurrence);
    }

    private function easter(): Event
    {
        $start = new LocalDateTime(2023, 4, 9, 0, 0);
        $end = new LocalDateTime(2023, 4, 10, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Easter", "", "", "almost everywhere", $recurrence);
    }

    private function birthday(): Event
    {
        $start = new LocalDateTime(1969, 3, 24, 0, 0);
        $end = new LocalDateTime(1969, 3, 24, 23, 59);
        $recurrence = new YearlyRecurrence($start, $end, null);
        return new Event("", $start, $end, "Christoph M. Becker", "", "", "###", $recurrence);
    }

    private function nightShift(): Event
    {
        $start = new LocalDateTime(2023, 4, 22, 22, 0);
        $end = new LocalDateTime(2023, 4, 23, 6, 0);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Night shift", "", "", "", $recurrence);
    }

    private function goodFriday(): Event
    {
        $start = new LocalDateTime(2023, 4, 7, 0, 0);
        $end = new LocalDateTime(2023, 4, 7, 23, 59);
        $recurrence = new NoRecurrence($start, $end);
        return new Event("", $start, $end, "Good Friday", "", "", "", $recurrence);
    }
}
