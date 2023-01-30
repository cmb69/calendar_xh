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
use XH\CSRFProtection as CsrfProtector;

class EditEventsControllerTest extends TestCase
{
    /** @var EditEventsController */
    private $sut;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['calendar'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['calendar'];
        $dateTime = LocalDateTime::fromIsoString("2023-01-30T14:27");
        $eventDataService = $this->createStub(EventDataService::class);
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $view = new View("./views/", $lang);
        $this->sut = new EditEventsController("./", $conf, $lang, $dateTime, $eventDataService, $csrfProtector, $view, "");
    }

    public function testDefaultActionRendersHtml()
    {
        $response = $this->sut->defaultAction();
        assert($response instanceof NormalResponse);
        Approvals::verifyHtml($response->output());
    }

    public function testCreateActionRendersHtml()
    {
        $response = $this->sut->createAction();
        assert($response instanceof NormalResponse);
        Approvals::verifyHtml($response->output());
    }

    public function testUpdateActionRedirectsOnUnknowEvent()
    {
        $_GET = ["event_id" => "invalid id"];
        $response = $this->sut->updateAction();
        assert($response instanceof RedirectResponse);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDeleteActionRedirectsOnUnknowEvent()
    {
        $_GET = ["event_id" => "invalid id"];
        $response = $this->sut->deleteAction();
        assert($response instanceof RedirectResponse);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoCreateActionRedirects()
    {
        $response = $this->sut->doCreateAction();
        assert($response instanceof RedirectResponse);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoUpdateActionRedirects()
    {
        $_GET = ["event_id" => "invalid id"];
        $response = $this->sut->doUpdateAction();
        assert($response instanceof RedirectResponse);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }

    public function testDoDeleteActionRedirectsOnUnknowEvent()
    {
        $_GET = ["event_id" => "invalid id"];
        $response = $this->sut->doDeleteAction();
        assert($response instanceof RedirectResponse);
        $this->assertEquals("http://example.com/?calendar&admin=plugin_main&action=plugin_text", $response->location());
    }
}
