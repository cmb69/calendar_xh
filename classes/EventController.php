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

namespace Calendar;

use Calendar\Infra\DateTimeFormatter;
use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\LocalDateTime;
use Plib\DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;

class EventController
{
    use DateTimeFormatting;
    use MicroFormatting;

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

    public function __invoke(Request $request): Response
    {
        if (!$this->conf["event_allow_single"] || ($id = $request->get("event_id")) === null) {
            return Response::create();
        }
        $calendar = Calendar::retrieveFrom($this->store);
        if (($event = $calendar->event($id)) === null) {
            return Response::error(404);
        }
        $start = $request->get("calendar_occurrence");
        if ($start !== null) {
            $day = LocalDateTime::fromIsoString($start . "T00:00");
            if ($day !== null) {
                $occurrence = $event->occurrenceOn($day, true);
                if ($occurrence !== null) {
                    $event = $occurrence;
                }
            }
        }
        return Response::create($this->view->render("event", [
            "summary" => $event->summary(),
            "date_time" => $this->renderRecurrence($event),
            "location" => $event->location(),
            "description" => $event->linktxt(),
            "data" => $this->eventData($request, $event),
        ]))->withTitle($this->view->text("event_page") . " – " . $this->view->esc($event->summary()));
    }

    private function renderRecurrence(Event $event): string
    {
        $freq = $event->recurrence();
        if ($freq === "none") {
            return $this->renderEventDateTime($event);
        }
        $until = $event->recursUntil();
        if ($until === null) {
            return $this->view->plain(
                "format_recurs",
                $this->view->text("label_recur_$freq"),
                $this->renderEventDateTime($event)
            );
        }
        return $this->view->plain(
            "format_recurs_until",
            $this->view->text("label_recur_$freq"),
            $this->renderEventDateTime($event),
            $this->view->esc($this->dateTimeFormatter->formatDate($until))
        );
    }
}
