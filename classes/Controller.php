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

abstract class Controller
{
    /**
     * @var array<string,string>
     */
    protected $conf;

    /**
     * @var array<string,string>
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
     * and to check for allowed separators
     *
     * Allowed seperators:
     * full-stop ".", forward slash "/" and minus/dash "-"
     *
     * @return string
     */
    protected function dpSeparator()
    {
        $sep = $this->conf['date_delimiter'];
        if ($sep != '/' && $sep != '-') {
            $sep = '.'; //set default
        }
        return $sep;
    }

    /**
     * @param int $month
     * @param int $year
     * @return string
     */
    protected function formatMonthYear($month, $year)
    {
        $monthnames = explode(',', $this->lang['monthnames_array']);
        return "{$monthnames[$month-1]} $year";
    }
}
