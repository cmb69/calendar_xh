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
use PHPUnit\Framework\TestCase;

class NextEventControllerTest extends TestCase
{
    public function testIssue51()
    {
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['calendar'];
        $event = Event::create("1969-03-24", null, "", null, "cmb", "", "", "###");
        $now = LocalDateTime::fromIsoString("2021-03-23T12:34");
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("findNextEvent")->willReturn($event);
        $dateTimeFormatter = new DateTimeFormatter($lang);
        $view = new View("./views/", $lang);
        $subject = new NextEventController($lang, $now, $eventDataService, $dateTimeFormatter, $view);
        $response = $subject->defaultAction();
        Approvals::verifyHtml($response);
    }
}
