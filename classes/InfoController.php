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

use Plib\DocumentStore;
use Plib\Response;
use Plib\SystemChecker;
use Plib\View;

class InfoController
{
    /** @var string */
    private $pluginFolder;

    /** @var DocumentStore */
    private $store;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var View */
    private $view;

    public function __construct(
        string $pluginFolder,
        DocumentStore $store,
        SystemChecker $systemChecker,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->store = $store;
        $this->systemChecker = $systemChecker;
        $this->view = $view;
    }

    public function defaultAction(): Response
    {
        return Response::create($this->view->render('info', [
            'version' => CALENDAR_VERSION,
            'checks' => [
                $this->checkPhpVersion('7.0.0'),
                $this->checkXhVersion('1.7.0'),
                $this->checkWritability("{$this->pluginFolder}css/"),
                $this->checkWritability("{$this->pluginFolder}config/"),
                $this->checkWritability("{$this->pluginFolder}languages/"),
                $this->checkWritability($this->store->folder()),
            ],
        ]))->withTitle("Calendar " . $this->view->esc(CALENDAR_VERSION));
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkPhpVersion(string $version): array
    {
        $state = $this->systemChecker->checkVersion(PHP_VERSION, $version) ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_phpversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return compact('state', 'label', 'stateLabel');
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkXhVersion(string $version): array
    {
        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_xhversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return compact('state', 'label', 'stateLabel');
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkWritability(string $folder): array
    {
        $state = $this->systemChecker->checkWritability($folder) ? 'success' : 'warning';
        $label = $this->view->plain("syscheck_writable", $folder);
        $stateLabel = $this->view->plain("syscheck_$state");
        return compact('state', 'label', 'stateLabel');
    }
}
