<?php

/**
 * Copyright (c) Christoph M. Becker
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

namespace Calendar\Dto;

class HeaderRow
{
    /**
     * @readonly
     * @var int
     */
    public $tablecols;

    /**
     * @readonly
     * @var string
     */
    public $month_year;

    /**
     * @readonly
     * @var bool
     */
    public $show_time;

    /**
     * @readonly
     * @var bool
     */
    public $show_location;

    /**
     * @readonly
     * @var bool
     */
    public $show_link;

    public function __construct(
        int $tablecols,
        string $month_year,
        bool $show_time,
        bool $show_location,
        bool $show_link
    ) {
        $this->tablecols = $tablecols;
        $this->month_year = $month_year;
        $this->show_time = $show_time;
        $this->show_location = $show_location;
        $this->show_link = $show_link;
    }
}
