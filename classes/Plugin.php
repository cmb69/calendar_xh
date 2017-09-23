<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
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
    const VERSION = '@CALENDAR_VERSION@';

    public function run()
    {
        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(true);
            if (XH_wantsPluginAdministration('calendar')) {
                $this->handleAdministration();
            }
        }
    }

    private function handleAdministration()
    {
        global $o, $pth, $plugin_cf, $plugin_tx, $admin, $action, $plugin;

        $eventfile = (new EventDataService)->getFilename();
        if (!is_file($eventfile)) {
            $handle = fopen($eventfile, "w");
            fclose($handle);
        }

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
                $o .= EditEvents($plugin_cf['calendar']['event-input_backend_narrow_medium_or_wide']);
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
        }
    }
}
