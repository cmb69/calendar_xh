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

class EventRow
{
    /**
     * @readonly
     * @var bool
     */
    public $is_birthday;

    /**
     * @readonly
     * @var string
     */
    public $summary;

    /**
     * @readonly
     * @var string
     */
    public $location;

    /**
     * @readonly
     * @var string
     */
    public $start_date;

    /**
     * @readonly
     * @var string
     */
    public $end_date;

    /**
     * @readonly
     * @var string
     */
    public $date;

    /**
     * @readonly
     * @var string
     */
    public $time;

    /**
     * @readonly
     * @var string
     */
    public $date_time;

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

    /**
     * @readonly
     * @var string
     */
    public $url;

    /**
     * @readonly
     * @var string
     */
    public $link;

    /**
     * @readonly
     * @var string
     */
    public $past_event_class;

    public function __construct(
        string $summary,
        string $location,
        string $start_date,
        string $end_date,
        string $date,
        string $time,
        string $date_time,
        bool $show_time,
        bool $show_location,
        bool $show_link,
        string $url,
        string $link,
        string $past_event_class
    ) {
        $this->is_birthday = false;
        $this->summary = $summary;
        $this->location = $location;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->date = $date;
        $this->time = $time;
        $this->date_time = $date_time;
        $this->show_time = $show_time;
        $this->show_location = $show_location;
        $this->show_link = $show_link;
        $this->url = $url;
        $this->link = $link;
        $this->past_event_class = $past_event_class;
    }
}
