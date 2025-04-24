<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2025 Christoph M. Becker
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

use Calendar\Model\Event;

trait DateTimeFormatting
{
    private function renderEventDateTime(Event $event): string
    {
        if (!$event->isMultiDay()) {
            if ($event->isFullDay() || $event->isBirthday()) {
                $dateTime = $this->view->esc("\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10");
            } else {
                $dateTime = $this->view->text(
                    "format_date-time",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                    $this->view->plain(
                        "format_time_interval",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->start()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->end()) . "\x10"
                    )
                );
            }
        } else {
            if ($event->isFullDay() || $event->isBirthday()) {
                $dateTime = $this->view->text(
                    "format_date_interval",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                    "\x11" . $this->dateTimeFormatter->formatDate($event->end()) . "\x10"
                );
            } else {
                $dateTime = $this->view->text(
                    "format_date_interval",
                    $this->view->plain(
                        "format_date-time",
                        "\x11" . $this->dateTimeFormatter->formatDate($event->start()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->start()) . "\x10"
                    ),
                    $this->view->plain(
                        "format_date-time",
                        "\x11" . $this->dateTimeFormatter->formatDate($event->end()) . "\x10",
                        "\x12" . $this->dateTimeFormatter->formatTime($event->end()) . "\x10"
                    )
                );
            }
        }
        return str_replace(
            ["\x11", "\x12", "\x10"],
            ['<span class="event_date">', '<span class="event_time">', "</span>"],
            $dateTime
        );
    }
}
