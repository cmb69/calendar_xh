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
    /**
     * @return void
     */
    public function defaultAction()
    {
        $nextevent = null;

        $allevents = [];
        $events = (new EventDataService($this->dpSeparator()))->readEvents();
        foreach ($events as $event) {
            if (($end = $event->getDateEnd()) !== null) {
                $event->text = $this->lang['event_date_till_date'] . " " . '<br>'
                    . $end . " " . $event->getEndTime();
                $allevents[] = $event;
                $endevent = new Event(
                    $end,
                    $end,
                    (string) $event->getEndTime(),
                    $event->getEndTime(),
                    $event->event,
                    $event->linkadr,
                    $event->linktxt,
                    $event->location
                );
                $endevent->text = $this->lang['event_started'] . '<br>'
                        . $event->getDateStart() . " " . $event->getStartTime();
                $allevents[] = $endevent;
            } elseif ($event->isBirthday()) {
                $newevent = new Event(
                    sprintf("%04d-%02d-%02d", (int) date("Y"), $event->getStart()->getMonth(), $event->getStart()->getDay()),
                    $event->getDateEnd(),
                    $event->getStartTime(),
                    $event->getEndTime(),
                    $event->event,
                    $event->linkadr,
                    $event->linktxt,
                    $event->location
                );
                $newevent->text = '';
                $allevents[] = $newevent;
            } else {
                $allevents[] = $event;
            }
        }
        $events = $allevents;
        usort($events, /** @return int */ function (Event $a, Event $b) {
            return $a->getStart()->compare($b->getStart());
        });

        $today = new LocalDateTime(date("Y-m-d"), date("H:i:s"));

        foreach ($events as $event) {
            if ($event->getStart()->compare($today) > 0) {
                $nextevent = $event;
                break;
            }
        }
        $view = new View('nextevent');
        if (isset($nextevent)) {
            $timestamp = $nextevent->getStart()->getTimestamp();
            $date = date($this->lang['event_date_representation_in_next_event_marquee'], $timestamp);
            if (date('H:i', $timestamp) != "00:00") {
                $date.= ' — ' . date('H:i', $timestamp);
            }
            $view->data = [
                'event' => $nextevent,
                'date' => $date,
            ];
        }
        $view->render();
    }
}
