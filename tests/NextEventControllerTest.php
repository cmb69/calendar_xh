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
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class NextEventControllerTest extends TestCase
{
    public function testIssue51()
    {
        $subject = $this->makeNextEventController();
        $request = new FakeRequest(["time" => 1616502840]);
        $response = $subject->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    public function testIssue70()
    {
        $subject = $this->makeNextEventController();
        $request = new FakeRequest(["time" => 1616675640]);
        $response = $subject->defaultAction($request);
        Approvals::verifyHtml($response);
    }

    private function makeNextEventController(): NextEventController
    {
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['calendar'];
        $event = Event::create("1969-03-24", null, "", null, "cmb", "", "", "###");
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("findNextEvent")->willReturn($event);
        $eventDataService->method("readEvents")->willReturn(new Calendar([]));
        $dateTimeFormatter = new DateTimeFormatter($lang);
        $view = new View("./views/", $lang);
        return new NextEventController($eventDataService, $dateTimeFormatter, $view);
    }
}
