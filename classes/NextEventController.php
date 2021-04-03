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

class NextEventController extends Controller
{
    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     * @param string $dpSeparator
     */
    public function __construct(array $conf, array $lang, EventDataService $eventDataService, View $view)
    {
        $this->conf = $conf;
        $this->lang = $lang;
        $this->eventDataService = $eventDataService;
        $this->view = $view;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        $now = time();
        $nextevent = $this->findNextEvent($now);
        $data = [];
        if ($nextevent !== null) {
            if ($nextevent->isBirthday()) {
                $start = $nextevent->start;
                $timestamp = mktime(0, 0, 0, $start->month, $start->day, (int) date("Y"));
                $nexteventtext = '';
            } elseif ($nextevent->start->getTimestamp() >= $now) {
                $timestamp = $nextevent->start->getTimestamp();
                if (($nextevent->getDateEnd()) > $nextevent->getDateStart()) {
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
     * @param int $now
     * @return Event|null
     */
    private function findNextEvent($now)
    {
        $nextevent = null;
        $nextdiff = null;
        $events = $this->eventDataService->readEvents();
        foreach ($events as $event) {
            if ($event->isBirthday()) {
                $start = $event->start;
                $diff = mktime(0, 0, 0, (int) date("Y"), $start->month, $start->day) - $now;
                if ($diff < 0) {
                    continue;
                }
            } else {
                $diff = $event->start->getTimestamp() - $now;
                $end = $event->end;
                if ($diff < 0) {
                    continue;
                } else {
                    $diff = $end->getTimestamp() - $now;
                    if ($diff < 0) {
                        continue;
                    }
                }
            }
            if ($nextdiff === null || $diff < $nextdiff) {
                $nextevent = $event;
                $nextdiff = $diff;
            }
        }
        return $nextevent;
    }
}
