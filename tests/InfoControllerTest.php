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
use Calendar\Infra\EventDataService;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\SystemChecker;
use Plib\View;

class InfoControllerTest extends TestCase
{
    /** @var EventDataService */
    private $dataService;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->dataService = new EventDataService(vfsStream::url("root/"), ".");
        $this->systemChecker = new FakeSystemChecker(true);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["calendar"]);
    }

    private function sut(): InfoController
    {
        return new InfoController(
            "./",
            $this->dataService,
            $this->systemChecker,
            $this->view
        );
    }

    public function testDefaultActionRendersPluginInfo(): void
    {
        $response = $this->sut()->defaultAction();
        Approvals::verifyHtml($response->output());
    }
}
