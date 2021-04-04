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

class NextEventController
{
    /** @var array<string,string> */
    private $lang;

    /** @var LocalDateTime */
    private $now;

    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $lang
     */
    public function __construct(
        array $lang,
        LocalDateTime $now,
        EventDataService $eventDataService,
        View $view
    ) {
        $this->lang = $lang;
        $this->now = $now;
        $this->eventDataService = $eventDataService;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        $now = time();
        $nextevent = $this->findNextEvent();
        $data = [];
        if ($nextevent !== null) {
            if ($nextevent->isBirthday()) {
                $start = $nextevent->start;
                $timestamp = mktime(0, 0, 0, $start->month, $start->day, $this->now->year);
                $nexteventtext = '';
            } elseif ($nextevent->start->getTimestamp() >= $now) {
                $timestamp = $nextevent->start->getTimestamp();
                if ($nextevent->end->compareDate($nextevent->start) > 0) {
                    $nexteventtext = $this->lang['event_date_till_date'] . " " . '<br>'
                        . $nextevent->getDateEnd() . " " . $nextevent->getEndTime();
                } else {
                    $nexteventtext = '';
                }
            } else {
                $end = $nextevent->end;
                $timestamp = $end->getTimestamp();
                $nexteventtext = $this->lang['event_started'] . '<br>'
                    . $nextevent->getDateStart() . " " . $nextevent->getStartTime();
            }
            $date = date($this->lang['event_date_representation_in_next_event_marquee'], $timestamp);
            if (date('H:i', $timestamp) != "00:00") {
                $date.= ' — ' . date('H:i', $timestamp);
            }
            $data = [
                'event' => $nextevent,
                'event_text' => $nexteventtext,
                'date' => $date,
            ];
        }
        $this->view->render('nextevent', $data);
    }

    /**
     * @return Event|null
     */
    private function findNextEvent()
    {
        $nextevent = null;
        $nextldt = null;
        $events = $this->eventDataService->readEvents();
        foreach ($events as $event) {
            if ($event->isBirthday()) {
                $ldt = $event->start->withYear($this->now->year);
                if ($ldt->compare($this->now) < 0) {
                    continue;
                }
            } else {
                $ldt = $event->start;
                if ($ldt->compare($this->now) < 0) {
                    continue;
                } else {
                    $ldt = $event->end;
                    if ($ldt->compare($this->now) < 0) {
                        continue;
                    }
                }
            }
            if ($nextldt === null || $ldt->compare($nextldt) < 0) {
                $nextevent = $event;
                $nextldt = $ldt;
            }
        }
        return $nextevent;
    }
}
