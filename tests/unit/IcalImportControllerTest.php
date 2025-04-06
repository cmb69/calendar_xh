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
use Plib\View;

class IcalImportControllerTest extends TestCase
{
    public function testDefaultActionsRendersForms()
    {
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $icsFileFinder->method('all')->willReturn(["cal1.ics", "cal2.ics"]);
        $eventDataService = $this->createStub(EventDataService::class);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportController($icsFileFinder, $eventDataService, $view);
        $response = $sut(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testImportActionRedirects()
    {
        $_POST = ['calendar_ics' => "foo.ics"];
        $icsFileFinder = $this->createStub(IcsFileFinder::class);
        $icsFileFinder->method('read')->willReturn([]);
        $eventDataService = $this->createStub(EventDataService::class);
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $view = new View("./views/", $plugin_tx['calendar']);
        $sut = new IcalImportController($icsFileFinder, $eventDataService, $view);
        $response = $sut(new FakeRequest(["url" => "http://example.com/?calendar&admin=import&action=import"]));
        $this->assertEquals(
            "http://example.com/?&calendar&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }
}
