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

use Calendar\Infra\DateTimeFormatter;
use Calendar\Model\Calendar;
use Calendar\Model\LocalDateTime;
use Plib\DocumentStore;
use Plib\Request;
use Plib\View;

class NextEventController
{
    /** @var array<string,string> */
    private $conf;

    /** @var DocumentStore */
    private $store;

    /** @var DateTimeFormatter */
    private $dateTimeFormatter;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        DocumentStore $store,
        DateTimeFormatter $dateTimeFormatter,
        View $view
    ) {
        $this->conf = $conf;
        $this->store = $store;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->view = $view;
    }

    public function defaultAction(Request $request): string
    {
        $now = LocalDateTime::fromIsoString(date("Y-m-d\TH:i", $request->time()));
        assert($now !== null);
        $calendar = Calendar::retrieveFrom($this->store);
        $nextevent = $calendar->nextEvent($now);
        if ($nextevent === null) {
            return $this->view->render('nextevent', ["has_next_event" => false]);
        }
        if ($nextevent->isBirthday()) {
            $ldt = $nextevent->start()->withYear($now->year());
            if ($ldt->compareDate($now) < 0) {
                $ldt = $nextevent->start()->withYear($now->year() + 1);
            }
            $nexteventtext = $this->view->plural("age", $nextevent->age());
            $nexteventtext2 = null;
        } elseif ($nextevent->start()->compare($now) >= 0) {
            $ldt = $nextevent->start();
            if ($nextevent->isMultiDay()) {
                $nexteventtext = $this->view->text("event_date_till_date");
                $nexteventtext2 = $nextevent->isFullDay()
                    ? $this->dateTimeFormatter->formatDate($nextevent->end())
                    : $this->dateTimeFormatter->formatDateTime($nextevent->end());
            } else {
                $nexteventtext = '';
                $nexteventtext2 = null;
            }
        } else {
            $ldt = $nextevent->end();
            $nexteventtext = $this->view->text("event_started");
            $nexteventtext2 = $nextevent->isFullDay()
                ? $this->dateTimeFormatter->formatDate($nextevent->start())
                : $this->dateTimeFormatter->formatDateTime($nextevent->start());
        }
        if ($nextevent->isFullDay()) {
            $date = $this->dateTimeFormatter->formatDate($ldt);
        } else {
            $date = $this->dateTimeFormatter->formatDateTime($ldt);
        }
        return $this->view->render('nextevent', [
            'has_next_event' => true,
            'summary' => $nextevent->summary(),
            'event_text' => $nexteventtext,
            'event_text_2' => $nexteventtext2,
            'date' => $date,
            'location' => $nextevent->isBirthday()
                ? $this->view->plain("birthday_text")
                : $nextevent->location(),
            'class' => $this->conf["nextevent_orientation"] === "horizontal" ? "calendar_horizontal" : "",
        ]);
    }
}
