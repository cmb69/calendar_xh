<?php

/**
 * Copyright 2025 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx;

        $pth = ["folder" => ["content" => "", "plugins" => ""]];
        $plugin_cf = ["calendar" => ["date_delimiter" => "", "same-event-calendar_for_all_languages" => ""]];
        $plugin_tx = ["calendar" => ["monthnames_array" => ""]];
    }

    public function testMakeNextEventController(): void
    {
        $this->assertInstanceOf(NextEventController::class, Dic::makeNextEventController());
    }

    public function testMakeInfoController(): void
    {
        $this->assertInstanceOf(InfoController::class, Dic::makeInfoController());
    }
}
