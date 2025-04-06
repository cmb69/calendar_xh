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

namespace Calendar;

class DateTimeFormatter
{
    /** @var array<string,string> */
    private $lang;

    /** @var array<int,string> */
    private $monthnames;

    /** @param array<string,string> $lang */
    public function __construct(array $lang)
    {
        $this->lang = $lang;
        $this->monthnames = explode(',', $this->lang['monthnames_array']);
    }

    public function formatMonthYear(int $month, int $year): string
    {
        $replace = [
            "%Y" => sprintf("%04d", $year),
            "%F" => $this->monthnames[$month - 1],
            "%n" => sprintf("%d", $month),
        ];
        return strtr($this->lang['format_month_year'], $replace);
    }

    public function formatDate(LocalDateTime $ldt): string
    {
        $replace = [
            "%Y" => sprintf("%04d", $ldt->year),
            "%F" => $this->monthnames[$ldt->month - 1],
            "%m" => sprintf("%02d", $ldt->month),
            "%n" => sprintf("%d", $ldt->month),
            "%d" => sprintf("%02d", $ldt->day),
            "%j" => sprintf("%d", $ldt->day),
        ];
        return strtr($this->lang['format_date'], $replace);
    }

    public function formatDateTime(LocalDateTime $ldt): string
    {
        $replace = [
            "%Y" => sprintf("%04d", $ldt->year),
            "%F" => $this->monthnames[$ldt->month - 1],
            "%m" => sprintf("%02d", $ldt->month),
            "%n" => sprintf("%d", $ldt->month),
            "%d" => sprintf("%02d", $ldt->day),
            "%j" => sprintf("%d", $ldt->day),
            "%a" => $ldt->hour < 12 ? "am" : "pm",
            "%g" => sprintf("%d", $ldt->hour % 12 === 0 ? 12 : $ldt->hour % 12),
            "%H" => sprintf("%02d", $ldt->hour),
            "%G" => sprintf("%d", $ldt->hour),
            "%i" => sprintf("%02d", $ldt->minute),
        ];
        return strtr($this->lang['format_date_time'], $replace);
    }

    public function formatTime(LocalDateTime $ldt): string
    {
        $replace = [
            "%a" => $ldt->hour < 12 ? "am" : "pm",
            "%g" => sprintf("%d", $ldt->hour % 12 === 0 ? 12 : $ldt->hour % 12),
            "%H" => sprintf("%02d", $ldt->hour),
            "%G" => sprintf("%d", $ldt->hour),
            "%i" => sprintf("%02d", $ldt->minute),
        ];
        return strtr($this->lang['format_time'], $replace);
    }
}
