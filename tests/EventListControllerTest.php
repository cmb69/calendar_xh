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
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class EventListControllerTest extends TestCase
{
    public function testRendersClassicEventListByDefault()
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $lang = XH_includeVar("./languages/en.php", "plugin_tx")["calendar"];
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->willReturn(
            new Calendar([$this->lunchBreak(), $this->easter(), $this->birthday()])
        );
        $view = new View("./views/", $lang);
        $sut = new EventListController(
            $conf,
            $eventDataService,
            new DateTimeFormatter($lang),
            $view
        );
        $request = new FakeRequest(["time" => 1675088820]);
        Approvals::verifyHtml($sut->defaultAction(0, 0, 0, 0, $request));
    }

    public function testRendersNewStyleEventListIfConfigured(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $conf["eventlist_template"] = "eventlist_new";
        $lang = XH_includeVar("./languages/en.php", "plugin_tx")["calendar"];
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("readEvents")->willReturn(
            new Calendar([$this->lunchBreak(), $this->easter(), $this->birthday()])
        );
        $view = new View("./views/", $lang);
        $sut = new EventListController(
            $conf,
            $eventDataService,
            new DateTimeFormatter($lang),
            $view
        );
        $request = new FakeRequest(["time" => 1675088820]);
        Approvals::verifyHtml($sut->defaultAction(0, 0, 0, 0, $request));
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
            "<a href=\"http://example.com/lunchbreak\">Tips for lunch breaks</a>",
            "whereever I am"
        );
    }

    private function easter(): Event
    {
        return Event::create(
            "2023-04-09",
            "2023-04-10",
            "",
            "",
            "Easter",
            "",
            "",
            "almost everywhere"
        );
    }

    private function birthday(): Event
    {
        return Event::create(
            "1969-03-24",
            "",
            "",
            "",
            "Christoph M. Becker",
            "",
            "",
            "###"
        );
    }
}
