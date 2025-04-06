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

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.5";
const CMSIMPLE_URL = "http://example.com/";
const CALENDAR_VERSION = "3.0-dev";

require_once './vendor/autoload.php';

require_once '../../cmsimple/functions.php';

require_once "../plib/classes/CsrfProtector.php";
require_once "../plib/classes/Request.php";
require_once "../plib/classes/Response.php";
require_once "../plib/classes/SystemChecker.php";
require_once "../plib/classes/Url.php";
require_once "../plib/classes/View.php";
require_once "../plib/classes/FakeRequest.php";
require_once "../plib/classes/FakeSystemChecker.php";

require_once './classes/model/Event.php';
require_once './classes/model/LocalDateTime.php';
require_once './classes/Calendar.php';
require_once './classes/CalendarController.php';
require_once './classes/DateTimeFormatter.php';
require_once './classes/Dic.php';
require_once './classes/EditEventsController.php';
require_once './classes/EventDataService.php';
require_once './classes/EventListController.php';
require_once './classes/IcalImportController.php';
require_once './classes/IcsFileFinder.php';
require_once './classes/InfoController.php';
require_once './classes/ICalendarParser.php';
require_once './classes/NextEventController.php';
