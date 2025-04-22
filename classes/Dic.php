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

use Calendar\Infra\Counter;
use Calendar\Infra\DateTimeFormatter;
use Calendar\Infra\Editor;
use Calendar\Model\Html2Text;
use Calendar\Model\ICalRepo;
use Plib\CsrfProtector;
use Plib\DocumentStore;
use Plib\Random;
use Plib\SystemChecker;
use Plib\View;

class Dic
{
    public static function makeCalendarController(): CalendarController
    {
        global $pth, $plugin_cf, $plugin_tx;
        static $num = 0;
        return new CalendarController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf['calendar'],
            new DocumentStore(self::getDataFolder()),
            new DateTimeFormatter($plugin_tx['calendar']),
            ++$num,
            self::counter(),
            self::view()
        );
    }

    public static function makeEventListController(): EventListController
    {
        global $plugin_cf, $plugin_tx;

        return new EventListController(
            $plugin_cf['calendar'],
            new DocumentStore(self::getDataFolder()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::view()
        );
    }

    public static function makeNextEventController(): NextEventController
    {
        global $plugin_cf, $plugin_tx;

        return new NextEventController(
            $plugin_cf["calendar"],
            new DocumentStore(self::getDataFolder()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::view()
        );
    }

    public static function makeEditEventController(): EditEventsController
    {
        global $pth, $plugin_cf;

        return new EditEventsController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf["calendar"],
            new DocumentStore(self::getDataFolder()),
            new CsrfProtector(),
            new Random(),
            new Editor(),
            self::view()
        );
    }

    public static function makeInfoController(): InfoController
    {
        global $pth;

        return new InfoController(
            "{$pth['folder']['plugins']}calendar/",
            new DocumentStore(self::getDataFolder()),
            new SystemChecker(),
            self::view()
        );
    }

    public static function makeIcalImportExportController(): IcalImportExportController
    {
        return new IcalImportExportController(
            new ICalRepo(self::getDataFolder(), $_SERVER["HTTP_HOST"], new Html2Text()),
            new DocumentStore(self::getDataFolder()),
            self::view()
        );
    }

    private static function getDataFolder(): string
    {
        global $pth, $sl, $cf, $plugin_cf;

        $dataFolder = $pth['folder']['content'];
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages'] && $sl !== $cf['language']['default']) {
            $dataFolder = dirname($dataFolder) . '/';
        }
        return $dataFolder . "calendar/";
    }

    private static function counter(): Counter
    {
        static $counter = null;

        if ($counter === null) {
            $counter = new Counter(1);
        }
        return $counter;
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;

        return new View("{$pth['folder']['plugins']}calendar/views/", $plugin_tx['calendar']);
    }
}
