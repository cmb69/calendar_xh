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
use Plib\Response;
use Plib\View;

class CalendarControllerTest extends TestCase
{
    public function testDefaultActionRendersHtml()
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $dateTime = LocalDateTime::fromIsoString("2023-01-30T14:27");
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->wilLReturn([$this->lunchBreak(), $this->weekend(), $this->birthday()]);
        $dateTimeFormatter = $this->createStub(DateTimeFormatter::class);
        $view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["calendar"]);
        $sut = new CalendarController(
            "./",
            $conf,
            $dateTime,
            $eventDataService,
            $dateTimeFormatter,
            $view
        );
        $response = $sut->defaultAction(0, 0, "", new FakeRequest(["url" => "http://example.com/?page"]));
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

    private function weekend(): Event
    {
        return Event::create(
            "2023-01-07",
            "2023-01-08",
            "",
            "",
            "Weekend",
            "",
            "",
            ""
        );
    }

    private function birthday(): Event
    {
        return Event::create(
            "2000-01-01",
            "",
            "",
            "",
            "Millenium",
            "",
            "",
            "###"
        );
    }
}
