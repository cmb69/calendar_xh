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

use Plib\Request;
use Plib\Response;
use Plib\View;

class IcalImportExportController
{
    /** @var IcsFileFinder */
    private $icsFileFinder;

    /** @var EventDataService */
    private $eventDataService;

    /** @var ICalendarWriter */
    private $iCalendarWriter;

    /** @var View */
    private $view;

    public function __construct(
        IcsFileFinder $icsFileFinder,
        EventDataService $eventDataService,
        ICalendarWriter $iCalendarWriter,
        View $view
    ) {
        $this->view = $view;
        $this->icsFileFinder = $icsFileFinder;
        $this->eventDataService = $eventDataService;
        $this->iCalendarWriter = $iCalendarWriter;
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
            'files' => $this->icsFileFinder->all(),
            'ignored' => $ignored,
        ]);
        return Response::create($output)->withTitle("Calendar – " . $this->view->text("label_import_export"));
    }

    private function importAction(Request $request): Response
    {
        if ($request->post("calendar_ics") === null) {
            return $this->defaultAction($request);
        }
        $events = ICalendarParser::parse($this->icsFileFinder->read($request->post("calendar_ics")), $eventCount);
        $ignored = $eventCount - count($events);
        $events = array_merge($this->eventDataService->readEvents()->events(), $events);
        $this->eventDataService->writeEvents($events);
        $url = $request->url()->page("calendar")->with("admin", "import_export")->with("calendar_ignored", (string) $ignored);
        return Response::redirect($url->absolute());
    }

    private function export(Request $request): Response
    {
        if ($request->post("calendar_ics") !== "calendar.ics") {
            return $this->defaultAction($request);
        }
        if (!$this->iCalendarWriter->write($this->eventDataService->readEvents())) {
            return Response::create($this->view->message("fail", "error_export"))
                ->withTitle("Calendar – " . $this->view->text("label_import_export"));
        }
        return Response::redirect($request->url()->page("calendar")->with("admin", "import_export")->absolute());
    }
}
