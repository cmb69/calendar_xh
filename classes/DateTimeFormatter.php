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

class DateTimeFormatter
{
    /**
     * @var array<string,string>
     */
    private $lang;

    /**
     * @param array<string,string> $lang
     */
    public function __construct(array $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @param int $month
     * @param int $year
     * @return string
     */
    public function formatMonthYear($month, $year)
    {
        $monthnames = explode(',', $this->lang['monthnames_array']);
        return "{$monthnames[$month-1]} $year";
    }

    /**
     * @return string
     */
    public function formatDate(LocalDateTime $ldt)
    {
        $search = ["{year}", "{month}", "{day}"];
        $replace = [$ldt->year, $ldt->month, $ldt->day];
        return str_replace($search, $replace, $this->lang['format_date']);
    }

    /**
     * @return string
     */
    public function formatDateTime(LocalDateTime $ldt)
    {
        $search = ["{year}", "{month}", "{day}", "{hour}", "{minute}"];
        $replace = [$ldt->year, $ldt->month, $ldt->day, $ldt->hour, $ldt->minute];
        return str_replace($search, $replace, $this->lang['format_date_time']);
    }
}
