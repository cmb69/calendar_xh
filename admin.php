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
use XH\ClassicPluginMenu;

if (!defined("CMSIMPLE_XH_VERSION")) {
    http_response_code(403);
    exit;
}

/**
 * @var string $action
 * @var string $admin
 * @var string $o
 * @var array<string,array<string,string>> $plugin_tx
 * @var string $sn
 * @var ClassicPluginMenu $_XH_pluginMenu
 */

$temp = $sn . "?&calendar&admin=import_export&normal"; // can't use Request::url() here
XH_registerStandardPluginMenuItems(true);
XH_registerPluginMenuItem("calendar", $plugin_tx["calendar"]["label_import_export"], $temp);
if (XH_wantsPluginAdministration("calendar")) {
    $o .= print_plugin_admin("on");
    $_XH_pluginMenu->makeRow();
    $_XH_pluginMenu->makeTab($temp, "", $plugin_tx["calendar"]["label_import_export"]);
    $o .= $_XH_pluginMenu->show();
    switch ($admin) {
        case '':
            $o .= Dic::makeInfoController()->defaultAction();
            break;
        case 'plugin_main':
            $o .= sprintf("<h1>Calendar – %s</h1>", XH_hsc($plugin_tx["calendar"]["menu_main"]))
                . Dic::makeEditEventController()(Request::current())();
            break;
        case 'import_export':
            $o .= Dic::makeIcalImportExportController()(Request::current())();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
