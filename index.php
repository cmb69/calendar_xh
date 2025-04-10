<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2023 Christoph M. Becker
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

use Calendar\Dic;
use Plib\Request;

const CALENDAR_VERSION = "2.7-dev";

/** @return string|never */
function calendar(int $year = 0, int $month = 0, string $eventpage = '')
{
    return Dic::makeCalendarController()->defaultAction($year, $month, $eventpage, Request::current())();
}

function events(int $month = 0, int $year = 0, int $end_month = 0, int $past_month = 0): string
{
    return Dic::makeEventListController()->defaultAction($month, $year, $end_month, $past_month, Request::current());
}

function nextEvent(): string
{
    return Dic::makeNextEventController()->defaultAction(Request::current());
}

/** @return string|never */
function editevents()
{
    return Dic::makeEditEventController()(Request::current())();
}
