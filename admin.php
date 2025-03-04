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

/**
 * @var string $action
 * @var string $admin
 * @var string $o
 * @var array<string,array<string,string>> $plugin_tx
 * @var string $sn
 */

XH_registerStandardPluginMenuItems(true);
XH_registerPluginMenuItem("calendar", $plugin_tx["calendar"]["label_import"], $sn . "?&calendar&admin=import&normal");
if (XH_wantsPluginAdministration("calendar")) {
    $o .= print_plugin_admin("on");
    pluginMenu("ROW");
    pluginMenu("TAB", "$sn?&calendar&admin=import&normal", "", $plugin_tx["calendar"]["label_import"]);
    $o .= (string) pluginMenu("SHOW");
    switch ($admin) {
        case '':
            $o .= Dic::makeInfoController()->defaultAction();
            break;
        case 'plugin_main':
            $o .= sprintf("<h1>Calendar – %s</h1>", XH_hsc($plugin_tx["calendar"]["menu_main"]))
                . Dic::makeEditEventController()()->trigger();
            break;
        case 'import':
            $o .= Dic::makeIcalImportController()($action)->trigger();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
