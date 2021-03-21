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

class EventListController extends Controller
{
    /** @var int */
    private $month;

    /** @var int */
    private $year;

    /** @var int */
    private $endMonth;

    /** @var int */
    private $pastMonth;

    /**
     * @param int $month
     * @param int $year
     * @param int $end_month
     * @param int $past_month
     */
    public function __construct($month, $year, $end_month, $past_month)
    {
        parent::__construct();
        $this->month = $month;
        $this->year = $year;
        $this->endMonth = $end_month;
        $this->pastMonth = $past_month;
    }

    /**
     * @return void
     */
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

        $monthEvents = [];
        $x = 0;
        while ($x <= $this->endMonth) {
            $filteredEvents = (new EventDataService($this->dpSeparator()))->filterByMonth($events, sprintf('%04d-%02d', $this->year, $this->month));
            if (($oneMonthEvents = $this->getMonthEvents($filteredEvents, $tablecols))) {
                $monthEvents[] = $oneMonthEvents;
            }
            $x++;
            $this->advanceMonth();
        }
        $view = new View('eventlist');
        $view->data = [
            'showHeading' => (bool) $this->conf['show_period_of_events'],
            'start' => new HtmlString('<span>' . XH_hsc($this->formatMonthYear($this->month, $this->year)) . '</span>'),
            'end' => new HtmlString('<span>' . XH_hsc($this->formatMonthYear($endmonth, $endyear)) . '</span>'),
            'monthEvents' => $monthEvents,
        ];
        $view->render();
    }

    /**
     * @return void
     */
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
            $this->month = (int) date('m');
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

    /**
     * @return void
     */
    private function determineEndMonth()
    {
        if ($this->endMonth == '') {
            if ($this->conf['show_number_of_future_months']) {
                $this->endMonth = (int) $this->conf['show_number_of_future_months'];
            } else {
                $this->endMonth= 1;
            }
        }
    }

    /**
     * @return void
     */
    private function advanceMonth()
    {
        if ($this->month == 12) {
            $this->year++;
            $this->month = 1;
        } else {
            $this->month++;
        }
    }

    /**
     * @return Event[]
     */
    private function fetchEvents()
    {
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        foreach ($events as $event) {
            if ($event->getDateEnd() !== null) {
                list($event->endyear, $event->endmonth, $event->endday)
                    = explode('-', (string) $event->getDateEnd());
            } else {
                $event->endday = $event->endmonth = $event->endyear = null;
            }
        }
        return $events;
    }

    /**
     * @return int
     */
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

    /**
     * @param int $tablecols
     * @return array
     */
    private function getMonthEvents(array $events, $tablecols)
    {
        if (empty($events)) {
            return [];
        }
        $result = ['headline' => $this->getHeadline($tablecols), 'rows' => []];
        foreach ($events as $event) {
            if ($event->isBirthday()) {
                $result['rows'][] = $this->getBirthdayRowView($event);
            } else {
                $result['rows'][] = $this->getEventRowView($event);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getBirthdayRowView(Event $event)
    {
        assert($event->getDateEnd() !== null);
        return [
            'is_birthday' => true,
            'age' => $this->year - (int) substr((string) $event->getDateEnd(), 0, 4),
            'event' => $event,
            'date' => sprintf('%02d', $event->getStart()->getDay()) . $this->dpSeparator()
                . sprintf('%02d', $this->month) . $this->dpSeparator() . $this->year,
            'showTime' => $this->conf['show_event_time'],
            'showLocation' => $this->conf['show_event_location'],
            'showLink' => $this->conf['show_event_link'],
            'link' => new HtmlString($this->renderLink($event)),
        ];
    }

    /**
     * @return array
     */
    private function getEventRowView(Event $event)
    {
        $time = $event->getStartTime();
        if ($event->getEndTime()) {
            if (!$event->endday) {
                $time .= ' ' . $this->lang['event_time_till_time'];
            }
            $time .= '<br>' . $event->getEndTime();
        }
        return [
            'is_birthday' => false,
            'event' => $event,
            'date' => new HtmlString($this->renderDate($event)),
            'showTime' => $this->conf['show_event_time'],
            'showLocation' => $this->conf['show_event_location'],
            'showLink' => $this->conf['show_event_link'],
            'link' => new HtmlString($this->renderLink($event)),
            'time' => new HtmlString($time),
        ];
    }

    /**
     * @return string
     */
    private function renderDate(Event $event)
    {
        $t = sprintf("%02d", $event->getStart()->getDay());
        // if beginning and end dates are there, these are put one under the other
        if ($event->endday) {
            if ($this->month != $event->endmonth
                || $this->year != $event->endyear
            ) {
                $t .= $this->dpSeparator() . sprintf('%02d', $this->month);
            }
            if ($this->year != $event->endyear) {
                $t .= $this->dpSeparator() . $this->year;
            }
            if ($this->year == $event->endyear && $this->dpSeparator() == '.') {
                $t.= '.';
            }
            $t .= "&nbsp;" . $this->lang['event_date_till_date'] . '<br>';
            $t .= $event->endday . $this->dpSeparator() . $event->endmonth
                . $this->dpSeparator() . $event->endyear;
        } else {
            $t .= $this->dpSeparator() . sprintf('%02d', $this->month) . $this->dpSeparator() . $this->year;
        }
        return $t;
    }

    /**
     * @return string
     */
    private function renderLink(Event $event)
    {
        if ($event->linkadr) {
            $url = $event->linkadr;
            $target = (strpos($url, '://') === false) ? '_self' : '_blank';
            $title = strip_tags($event->event);
            $text = $event->linktxt ?: $event->linkadr;
            return "<a href=\"{$url}\" target=\"{$target}\" title=\"{$title}\">"
                . "{$text}</a>";
        } else {
            return (string) $event->linktxt;
        }
    }

    /**
     * @param int $tablecols
     * @return array
     */
    private function getHeadline($tablecols)
    {
        return [
            'tablecols' => $tablecols,
            'monthYear' => $this->formatMonthYear($this->month, $this->year),
            'showTime' => $this->conf['show_event_time'],
            'showLocation' => $this->conf['show_event_location'],
            'showLink' => $this->conf['show_event_link'],
        ];
    }
}
