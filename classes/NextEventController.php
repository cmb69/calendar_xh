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

class NextEventController
{
    /** @var array<string,string> */
    private $lang;

    /** @var LocalDateTime */
    private $now;

    /** @var EventDataService */
    private $eventDataService;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $lang
     */
    public function __construct(
        array $lang,
        LocalDateTime $now,
        EventDataService $eventDataService,
        DateTimeFormatter $dateTimeFormatter,
        View $view
    ) {
        $this->lang = $lang;
        $this->now = $now;
        $this->eventDataService = $eventDataService;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->view = $view;
    }

    public function defaultAction(): string
    {
        $events = $this->eventDataService->readEvents();
        $nextevent = $this->eventDataService->findNextEvent($events, $this->now);
        $data = [];
        if ($nextevent !== null) {
            if ($nextevent->isBirthday()) {
                $ldt = $nextevent->start->withYear($this->now->year);
                if ($ldt->compare($this->now) < 0) {
                    $ldt = $nextevent->start->withYear($this->now->year + 1);
                }
                $age = $this->now->year - $nextevent->start->year;
                $nexteventtext = sprintf($this->lang['age' . XH_numberSuffix($age)], $age);
                $nexteventtext2 = null;
            } elseif ($nextevent->start->compare($this->now) >= 0) {
                $ldt = $nextevent->start;
                if ($nextevent->isMultiDay()) {
                    $nexteventtext = $this->lang['event_date_till_date'];
                    $nexteventtext2 = $nextevent->isFullDay()
                        ? $this->dateTimeFormatter->formatDate($nextevent->end)
                        : $this->dateTimeFormatter->formatDateTime($nextevent->end);
                } else {
                    $nexteventtext = '';
                    $nexteventtext2 = null;
                }
            } else {
                $ldt = $nextevent->end;
                $nexteventtext = $this->lang['event_started'];
                $nexteventtext2 = $nextevent->isFullDay()
                    ? $this->dateTimeFormatter->formatDate($nextevent->start)
                    : $this->dateTimeFormatter->formatDateTime($nextevent->start);
            }
            if ($nextevent->isFullDay()) {
                $date = $this->dateTimeFormatter->formatDate($ldt);
            } else {
                $date = $this->dateTimeFormatter->formatDateTime($ldt);
            }
            $data = [
                'event' => $nextevent,
                'event_text' => $nexteventtext,
                'event_text_2' => $nexteventtext2,
                'date' => $date,
                'location' => $nextevent->isBirthday() ? $this->lang['birthday_text'] : $nextevent->location,
            ];
        }
        return $this->view->render('nextevent', $data);
    }
}
