<?php

/**
 * Copyright 2017 Christoph M. Becker
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
    public function run()
    {
        if (XH_ADM) {
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    private function isAdministrationRequested()
    {
        global $calendar;

        return isset($calendar) && $calendar === 'true';
    }

    private function handleAdministration()
    {
        global $o, $sl, $pth, $plugin_cf, $plugin_tx, $admin, $action, $plugin;

        $eventfile = (new EventDataService)->getFilename();
        if (!is_file($eventfile)) {
            $handle = fopen($eventfile, "w");
            fclose($handle);
        }

        $o .= print_plugin_admin('on');

        $credits = "<h2>Calendar plugin version 1.2.10 </h2>\n"
            . '<p>1.2.10: Maintenance release 03/2013 fixing magic quotes problems, suggested by <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
            . '1.2.9: Maintenance release 05/2012, on suggestion of <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
            . '1.2.8: Security release 03/2012, on suggestion of  <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
            . '1.2.1 - 1.2.7 (10/2011): Maintenance releases  by <a href="http://svasti.de" target="_blank">svasti</a>' . tag('br')
            . '1.2 with functionality and security added by <a href="http://cmsimple.holgerirmler.de" target="_blank">Holger</a>' . tag('br')
            . '1.1 (03/2011) with major changes by <a href="http://svasti.de" target="_blank">svasti</a>' . tag('br')
            . '0.6 - 1.0 by Tory  (and in between mod 02/2008 by Bob (cmsimple.fr))' . tag('br')
            . '0.1 - 0.5 (2005-2006) by Michael Svarrer</p>';

        switch ($admin) {
            case '':
                $o .= $credits;
                break;
            case 'plugin_main':
                $o .= $plugin_tx['calendar']['admin_text_start'];
                $o .= EditEvents($plugin_cf['calendar']['event-input_backend_narrow_medium_or_wide']);
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
        }
    }
}
