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

use DirectoryIterator;

class IcalImportController
{
    /** @var string */
    private $scriptName;

    /** @var string */
    private $dataFolder;

    /** @var EventDataService */
    private $eventDataService;

    /** @var View */
    private $view;

    public function __construct(
        string $scriptName,
        string $dataFolder,
        EventDataService $eventDataService,
        View $view
    ) {
        $this->scriptName = $scriptName;
        $this->view = $view;
        $this->dataFolder = $dataFolder;
        $this->eventDataService = $eventDataService;
    }

    public function defaultAction(): Response
    {
        $output = $this->view->render('import', [
            'url' => $this->scriptName . '?&calendar&admin=import&action=import',
            'files' => $this->findIcsFiles(),
        ]);
        return new NormalResponse($output);
    }

    /**
     * @return string[]
     */
    private function findIcsFiles(): array
    {
        $result = [];
        foreach (new DirectoryIterator($this->dataFolder) as $file) {
            if ($file->isFile() && $file->getExtension() === 'ics') {
                $result[] = $file->getFilename();
            }
        }
        return $result;
    }

    public function importAction(): Response
    {
        assert(is_string($_POST['calendar_ics']));
        $file = $this->dataFolder . '/' . $_POST['calendar_ics'];
        $reader = new ICalendarReader($file);
        $events = array_merge($this->eventDataService->readEvents(), $reader->read());
        $this->eventDataService->writeEvents($events);
        $url = CMSIMPLE_URL . '?&calendar&admin=plugin_main&action=plugin_text';
        return new RedirectResponse($url);
    }
}
