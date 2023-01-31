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

class IcalImportController
{
    /** @var string */
    private $scriptName;

    /** @var IcsFileFinder */
    private $icsFileFinder;

    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    public function __construct(
        string $scriptName,
        IcsFileFinder $icsFileFinder,
        EventDataService $eventDataService,
        View $view
    ) {
        $this->scriptName = $scriptName;
        $this->view = $view;
        $this->icsFileFinder = $icsFileFinder;
        $this->eventDataService = $eventDataService;
    }

    public function defaultAction(): Response
    {
        $output = $this->view->render('import', [
            'url' => $this->scriptName . '?&calendar&admin=import&action=import',
            'files' => $this->icsFileFinder->all(),
        ]);
        return new NormalResponse($output);
    }

    public function importAction(): Response
    {
        assert(is_string($_POST['calendar_ics']));
        $file = $this->icsFileFinder->folder() . '/' . $_POST['calendar_ics'];
        $reader = new ICalendarParser();
        $events = $reader->parse(file($file, FILE_IGNORE_NEW_LINES));
        $events = array_merge($this->eventDataService->readEvents(), $events);
        $this->eventDataService->writeEvents($events);
        $url = CMSIMPLE_URL . '?&calendar&admin=plugin_main&action=plugin_text';
        return new RedirectResponse($url);
    }
}
