<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017-2019 Christoph M. Becker
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

use stdClass;

class EventListController extends Controller
{
    private $month;

    private $year;

    private $endMonth;

    private $pastMonth;

    public function __construct($month, $year, $end_month, $past_month)
    {
        parent::__construct();
        $this->month = $month;
        $this->year = $year;
        $this->endMonth = $end_month;
        $this->pastMonth = $past_month;
    }

    public function defaultAction()
    {
        $this->determineYearAndMonth();
        $this->determineEndMonth();
        $this->endMonth = $this->endMonth + $this->pastMonth;

        $events = $this->fetchEvents();

        $endmonth = $this->month + $this->endMonth;
        $endyear = $this->year;
        while ($endmonth > 12) {
            $endyear++;
            $endmonth -= 12;
        }

        $tablecols = $this->calcTablecols();

        $view = new View('eventlist');
        $view->showHeading = (bool) $this->conf['show_period_of_events'];
        $view->start = new HtmlString('<span>' . XH_hsc($this->formatMonthYear($this->month, $this->year)) . '</span>');
        $view->end = new HtmlString('<span>' . XH_hsc($this->formatMonthYear($endmonth, $endyear)) . '</span>');
        $monthEvents = [];
        $x = 0;
        while ($x <= $this->endMonth) {
            $filteredEvents = (new EventDataService($this->dpSeparator()))->filterByMonth($events, sprintf('%04d-%02d', $this->year, $this->month));
            $monthEvents[] = new HtmlString($this->renderMonthEvents($filteredEvents, $tablecols));
            $x++;
            $this->advanceMonth();
        }
        $view->monthEvents = $monthEvents;
        $view->render();
    }

    private function determineYearAndMonth()
    {
        $month_input = isset($_GET['month']) ? $_GET['month'] : '';

        if ($this->month) {
            if ($month_input) {
                if ($this->month >= $month_input) {
                    $this->month = $month_input;
                }
            }
        } else {
            $this->month = $month_input;
        }

        $this->year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        if ($this->month == '') {
            $this->month = date('m');
        }

        if (!$this->pastMonth) {
            $this->pastMonth = $this->conf['show_number_of_previous_months'];
        }
        $this->pastMonth = (int) $this->pastMonth;

        $this->month = $this->month - $this->pastMonth;
        if ($this->month < 1) {
            $this->year = $this->year - 1;
            $this->month = 12 + $this->month;
        }
    }

    private function determineEndMonth()
    {
        if ($this->endMonth == '') {
            if ($this->conf['show_number_of_future_months']) {
                $this->endMonth = $this->conf['show_number_of_future_months'];
            } else {
                $this->endMonth= "1";
            }
        }
    }

    private function advanceMonth()
    {
        if ($this->month == 12) {
            $this->year++;
            $this->month = 1;
        } else {
            $this->month++;
        }
    }

    private function fetchEvents()
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        foreach ($events as $event) {
            list($event->startyear, $event->startmonth, $event->startday)
                = explode('-', $event->datestart);
            if (isset($event->dateend)) {
                list($event->endyear, $event->endmonth, $event->endday)
                    = explode('-', $event->dateend);
                $event->past_event = (strtotime($event->dateend) < time());
            } else {
                $event->endday = $event->endmonth = $event->endyear = null;
                $event->past_event = (strtotime($event->datestart) < time());
            }
        }
        return $events;
    }

    private function calcTablecols()
    {
        // the number of tablecolumns is calculated
        // starting with minimum number of columns (date + main entry)
        $tablecols = 2;
        // adding columns according to config settings
        if ($this->conf['show_event_time']) {
            $tablecols++;
        }
        if ($this->conf['show_event_location']) {
            $tablecols++;
        }
        if ($this->conf['show_event_link']) {
            $tablecols++;
        }
        return $tablecols;
    }

    private function renderMonthEvents(array $events, $tablecols)
    {
        $t = '';
        if (!empty($events)) {
            $this->month = sprintf('%02d', $this->month);
            $t = $this->createHeadlineView($tablecols) . $t;
            foreach ($events as $event) {
                if (isset($event->age)) {
                    $t .= $this->createBirthdayRowView($event, $event->age);
                } else {
                    $t .= $this->createEventRowView($event);
                }
            }
        }
        return $t;
    }

    /**
     * @return View
     */
    private function createBirthdayRowView(stdClass $event, $age)
    {
        $view = new View('birthday-row');
        $view->event = $event;
        $view->age = $age;
        $view->date = $event->startday . $this->dpSeparator()
            . $this->month . $this->dpSeparator() . $this->year;
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        $view->link = new HtmlString($this->renderLink($event));
        return $view;
    }

    /**
     * @return View
     */
    private function createEventRowView(stdClass $event)
    {
        $view = new View('event-row');
        $view->event = $event;
        $view->date = new HtmlString($this->renderDate($event));
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        $view->link = new HtmlString($this->renderLink($event));
        $time = $event->starttime;
        if ($event->endtime) {
            if (!$event->endday) {
                $time .= ' ' . $this->lang['event_time_till_time'];
            }
            $time .= tag('br') . $event->endtime;
        }
        $view->time = new HtmlString($time);
        return $view;
    }

    private function renderDate($event)
    {
        $t = $event->startday;
        // if beginning and end dates are there, these are put one under the other
        if ($event->endday) {
            if ($this->month != $event->endmonth
                || $this->year != $event->endyear
            ) {
                $t .= $this->dpSeparator() . $this->month;
            }
            if ($this->year != $event->endyear) {
                $t .= $this->dpSeparator() . $this->year;
            }
            if ($this->year == $event->endyear && $this->dpSeparator() == '.') {
                $t.= '.';
            }
            $t .= "&nbsp;" . $this->lang['event_date_till_date'] . tag('br');
            $t .= $event->endday . $this->dpSeparator() . $event->endmonth
                . $this->dpSeparator() . $event->endyear;
        } else {
            $t .= $this->dpSeparator() . "{$this->month}" . $this->dpSeparator() . $this->year;
        }
        return $t;
    }

    private function renderLink(stdClass $event)
    {
        if ($event->linkadr) {
            $url = $event->linkadr;
            $target = (strpos($url, '://') === false) ? '_self' : '_blank';
            $title = strip_tags($event->event);
            $text = $event->linktxt ?: $event->linkadr;
            return "<a href=\"{$url}\" target=\"{$target}\" title=\"{$title}\">"
                . "{$text}</a>";
        } else {
            return $event->linktxt;
        }
    }

    private function createHeadlineView($tablecols)
    {
        $view = new View("event-list-headline");
        $view->tablecols = $tablecols;
        $view->monthYear = $this->formatMonthYear($this->month, $this->year);
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        return $view;
    }
}
