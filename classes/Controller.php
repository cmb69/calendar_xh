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

abstract class Controller
{
    /**
     * @var array
     */
    protected $conf;

    /**
     * @var array
     */
    protected $lang;

    public function __construct()
    {
        global $plugin_cf, $plugin_tx;

        $this->conf = $plugin_cf['calendar'];
        $this->lang = $plugin_tx['calendar'];
    }

    /**
     * Helper-function to parse the date-seperator, set in plugin-config,
     * to build the config-string for the Date-Picker and to
     * check for allowed seperators
     *
     * Allowed seperators:
     * full-stop ".", forward slash "/" and minus/dash "-"
     */
    protected function dpSeperator($mode = '')
    {
        $sep = $this->conf['date_delimiter'];
        $dp_sep = ''; //the string to configure the DatePicker
    
        if ($sep != '/' && $sep != '-') {
            $sep = '.'; //set default
        }

        switch ($sep) {
            case '.':
                $dp_sep = 'dt';
                break;
            case '/':
                $dp_sep = 'sl';
                break;
            case '-':
                $dp_sep = 'ds';
                break;
        }

        if ($mode == 'dp') {
            return $dp_sep;
        } else {
            return $sep;
        }
    }
}
