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

namespace Calendar;

class Plugin
{
    const VERSION = '2.0dev1';

    public function run()
    {
        global $sn, $plugin_tx;

        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(true);
            XH_registerPluginMenuItem('calendar', $plugin_tx['calendar']['label_import'], $sn . '?&calendar&admin=import&normal');
            if (XH_wantsPluginAdministration('calendar')) {
                $this->handleAdministration();
            }
        }
    }

    private function handleAdministration()
    {
        global $o, $pth, $plugin_tx, $admin, $action, $plugin;

        $o .= print_plugin_admin('on');

        switch ($admin) {
            case '':
                $view = new View('info');
                $view->logo = "{$pth['folder']['plugins']}calendar/calendar.png";
                $view->version = self::VERSION;
                $view->checks = (new SystemCheckService)->getChecks();
                $o .= $view;
                break;
            case 'plugin_main':
                $o .= sprintf('<h1>Calendar – %s</h1>', XH_hsc($plugin_tx['calendar']['menu_main']));
                $o .= EditEvents();
                break;
            case 'import':
                $controller = new IcalImportController;
                ob_start();
                switch ($action) {
                    case 'import':
                        $controller->importAction();
                        break;
                    default:
                        $controller->defaultAction();
                }
                $o .= ob_get_clean();
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
        }
    }
}
