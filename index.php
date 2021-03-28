<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2021 Christoph M. Becker
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

/**
 * @param int $year
 * @param int $month
 * @param string $eventpage
 * @return string
 */
function calendar($year = 0, $month = 0, $eventpage = '')
{
    global $plugin_cf, $plugin_tx;

    ob_start();
    (new Calendar\CalendarController($plugin_cf['calendar'], $plugin_tx['calendar'], $year, $month, $eventpage))->defaultAction();
    return ob_get_clean();
}

/**
 * @param int $month
 * @param int $year
 * @param int $end_month
 * @param int $past_month
 * @return string
 */
function events($month = 0, $year = 0, $end_month = 0, $past_month = 0)
{
    global $plugin_cf, $plugin_tx;

    ob_start();
    (new Calendar\EventListController($plugin_cf['calendar'], $plugin_tx['calendar'], $month, $year, $end_month, $past_month))
        ->defaultAction();
    return ob_get_clean();
}

/**
 * @return string
 */
function nextevent()
{
    global $plugin_cf, $plugin_tx;

    ob_start();
    (new Calendar\NextEventController($plugin_cf['calendar'], $plugin_tx['calendar']))->defaultAction();
    return ob_get_clean();
}

/**
 * @return string
 */
function editevents()
{
    global $plugin_cf, $plugin_tx;

    if (isset($_POST['action'])) {
        assert(is_string($_POST['action']));
        $action = $_POST['action'];
    } elseif (isset($_GET['action'])) {
        assert(is_string($_GET['action']));
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
    (new Calendar\EditEventsController($plugin_cf['calendar'], $plugin_tx['calendar']))->{$action}();
    return ob_get_clean();
}

(new Calendar\Plugin)->run();
