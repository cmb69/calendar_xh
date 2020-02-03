<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2019 Christoph M. Becker
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

function calendar($year = '', $month = '', $eventpage = '')
{
    ob_start();
    (new Calendar\CalendarController($year, $month, $eventpage))->defaultAction();
    return ob_get_clean();
}

function events($month = null, $year = null, $end_month = null, $past_month = null)
{
    ob_start();
    (new Calendar\EventListController($month, $year, $end_month, $past_month))->defaultAction();
    return ob_get_clean();
}

function nextevent()
{
    ob_start();
    (new Calendar\NextEventController)->defaultAction();
    return ob_get_clean();
}

/**
 * @return string
 */
function editevents()
{
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = 'editevents';
    }
    switch ($action) {
        case 'saveevents':
            $action = 'saveAction';
            break;
        default:
            $action = 'defaultAction';
    }
    ob_start();
    (new Calendar\EditEventsController())->{$action}();
    return ob_get_clean();
}

(new Calendar\Plugin)->run();
