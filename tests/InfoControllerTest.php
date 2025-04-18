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
use Plib\FakeSystemChecker;
use Plib\View;

class InfoControllerTest extends TestCase
{
    public function testDefaultActionRendersPluginInfo(): void
    {
        $dataService = $this->createStub(EventDataService::class);
        $dataService->method("getFilename")->willReturn("./content/calendar/calendar.csv");
        $sut = new InfoController(
            "./",
            $dataService,
            new FakeSystemChecker(true),
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["calendar"])
        );
        $response = $sut->defaultAction();
        Approvals::verifyHtml($response->output());
    }
}
