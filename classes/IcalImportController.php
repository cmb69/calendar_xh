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

class IcalImportController
{
    /** @var IcsFileFinder */
    private $icsFileFinder;

    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    public function __construct(
        IcsFileFinder $icsFileFinder,
        EventDataService $eventDataService,
        View $view
    ) {
        $this->view = $view;
        $this->icsFileFinder = $icsFileFinder;
        $this->eventDataService = $eventDataService;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->get("action")) {
            case "import":
                return $this->importAction($request);
            default:
                return $this->defaultAction($request);
        }
    }

    private function defaultAction(Request $request): Response
    {
        $output = $this->view->render('import', [
            'url' => $request->url()->page("calendar")->with("admin", "import")->with("action", "import")->relative(),
            'files' => $this->icsFileFinder->all(),
        ]);
        return Response::create($output);
    }

    private function importAction(Request $request): Response
    {
        if ($request->post("calendar_ics") === null) {
            return $this->defaultAction($request);
        }
        $reader = new ICalendarParser();
        $events = $reader->parse($this->icsFileFinder->read($request->post("calendar_ics")));
        $events = array_merge($this->eventDataService->readEvents()->events(), $events);
        $this->eventDataService->writeEvents($events);
        $url = CMSIMPLE_URL . '?&calendar&admin=plugin_main&action=plugin_text';
        return Response::redirect($url);
    }
}
