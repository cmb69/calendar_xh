<?php

/*
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

use DirectoryIterator;

class IcalImportController extends Controller
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $dpSeparator;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     * @param string $dataFolder
     * @param string $dpSeparator
     */
    public function __construct(array $conf, array $lang, $dataFolder, $dpSeparator, View $view)
    {
        $this->conf = $conf;
        $this->lang = $lang;
        $this->view = $view;
        $this->dataFolder = $dataFolder;
        $this->dpSeparator = $dpSeparator;
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $sn;

        $this->view->render('import', [
            'url' => $sn . '?&calendar&admin=import&action=import',
            'files' => $this->findIcsFiles(),
        ]);
    }

    /**
     * @return string[]
     */
    private function findIcsFiles()
    {
        $result = [];
        foreach (new DirectoryIterator($this->dataFolder) as $file) {
            if ($file->isFile() && $file->getExtension() === 'ics') {
                $result[] = $file->getFilename();
            }
        }
        return $result;
    }

    /**
     * @return void
     */
    public function importAction()
    {
        assert(is_string($_POST['calendar_ics']));
        $file = $this->dataFolder . '/' . $_POST['calendar_ics'];
        $reader = new ICalendarReader($file);
        $dataService = new EventDataService($this->dpSeparator);
        $events = array_merge($dataService->readEvents(), $reader->read());
        $dataService->writeEvents($events);
        $url = CMSIMPLE_URL . '?&calendar&admin=plugin_main&action=plugin_text';
        header("Location: $url", true, 303);
        exit;
    }
}
