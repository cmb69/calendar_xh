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

class EventListControllerTest extends TestCase
{
    public function testIt()
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['calendar'];
        $dateTime = LocalDateTime::fromIsoString("2023-01-30T14:27");
        $eventDataService = $this->createStub(EventDataService::class);
        $dateTimeFormatter = $this->createStub(DateTimeFormatter::class);
        $view = new View("./views/", $lang);
        $sut = new EventListController(
            $conf,
            $lang,
            $dateTime,
            $eventDataService,
            $dateTimeFormatter,
            $view
        );
        Approvals::verifyHtml($sut->defaultAction(0, 0, 0, 0));
    }
}
