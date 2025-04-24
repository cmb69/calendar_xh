<?php

/*
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

use Calendar\Model\Calendar;
use Calendar\Model\Event;
use Calendar\Model\ICalRepo;
use Plib\DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;

class IcalImportExportController
{
    use MicroFormatting;

    /** @var array<string,string> */
    private $conf;

    /** @var ICalRepo */
    private $iCalendarRepo;

    /** @var DocumentStore */
    private $store;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        ICalRepo $iCalendarRepo,
        DocumentStore $store,
        View $view
    ) {
        $this->conf = $conf;
        $this->iCalendarRepo = $iCalendarRepo;
        $this->store = $store;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action")) {
            case "import":
                return $this->importAction($request);
            case "export":
                return $this->export($request);
            default:
                return $this->defaultAction($request);
        }
    }

    private function defaultAction(Request $request): Response
    {
        $ignored = $request->get("calendar_ignored");
        if ($ignored !== null) {
            $ignored = (int) $ignored;
        }
        $output = $this->view->render('import_export', [
            'url' => $request->url()->page("calendar")->with("admin", "import_export")
                ->with("action", "import")->relative(),
            'export_url' => $request->url()->page("calendar")
                ->with("admin", "import_export")->with("action", "export")->relative(),
            'files' => $this->iCalendarRepo->all(),
            'ignored' => $ignored,
        ]);
        return Response::create($output)->withTitle("Calendar – " . $this->view->text("label_import_export"));
    }

    private function importAction(Request $request): Response
    {
        if ($request->post("calendar_ics") === null) {
            return $this->defaultAction($request);
        }
        $calendar = Calendar::updateIn($this->store);
        $import = $this->iCalendarRepo->find($request->post("calendar_ics"), $eventCount);
        $ignored = $eventCount - count($import->events());
        $calendar->import($import);
        $this->store->commit(); // TODO handle error?
        $url = $request->url()->page("calendar")->with("admin", "import_export")
            ->with("calendar_ignored", (string) $ignored);
        return Response::redirect($url->absolute());
    }

    private function export(Request $request): Response
    {
        if ($request->post("calendar_ics") !== "calendar.ics") {
            return $this->defaultAction($request);
        }
        $genUrl = function (Event $event) use ($request) {
            return $this->eventUrl($request, $event);
        };
        if (!$this->iCalendarRepo->save("calendar", Calendar::retrieveFrom($this->store), $genUrl)) {
            return Response::create($this->view->message("fail", "error_export"))
                ->withTitle("Calendar – " . $this->view->text("label_import_export"));
        }
        return Response::redirect($request->url()->page("calendar")->with("admin", "import_export")->absolute());
    }
}
