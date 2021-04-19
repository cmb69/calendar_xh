<?php

/**
 * Copyright 2021 Christoph M. Becker
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

class NextEventControllerTest extends TestCase
{
    public function testIssue51()
    {
        $text = [
            'birthday_text' => "Birthday",
            'age_5' => "%d years",
        ];
        $event = Event::create("1969-03-24", null, "", null, "cmb", "", "", "###");
        $now = LocalDateTime::fromIsoString("2021-03-23T12:34");
        $eventDataService = $this->createStub(EventDataService::class);
        $eventDataService->method("findNextEvent")->willReturn($event);
        $dateTimeFormatter = $this->createStub(DateTimeFormatter::class);
        $view = $this->createMock(View::class);
        $subject = new NextEventController($text, $now, $eventDataService, $dateTimeFormatter, $view);
        $view->expects($this->once())
            ->method("render")
            ->with(
                $this->equalTo("nextevent"),
                $this->callback(function ($data) {
                    return $data['event']->summary === "cmb"
                        && (string) $data['event_text'] === "52 years"
                        && $data['location'] === "Birthday";
                })
            );
        $subject->defaultAction();
    }
}
