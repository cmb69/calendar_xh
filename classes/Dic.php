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

use XH\CSRFProtection as CsrfProtector;

class Dic
{
    public static function makeCalendarController(): CalendarController
    {
        global $pth, $plugin_cf, $plugin_tx, $sn, $su;

        return new CalendarController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::makeView(),
            "$sn?$su"
        );
    }

    public static function makeEventListController(): EventListController
    {
        global $plugin_cf, $plugin_tx;

        return new EventListController(
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::makeView()
        );
    }

    public static function makeNextEventController(): NextEventController
    {
        global $plugin_tx;

        return new NextEventController(
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::makeView()
        );
    }

    public static function makeEditEventController(): EditEventsController
    {
        global $pth, $plugin_cf, $plugin_tx, $su;

        return new EditEventsController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            self::getCsrfProtector(),
            self::makeView(),
            $su
        );
    }

    public static function makeInfoController(): InfoController
    {
        global $pth, $plugin_tx;

        return new InfoController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_tx['calendar'],
            new SystemChecker(),
            self::makeView()
        );
    }

    public static function makeIcalImportController(): IcalImportController
    {
        global $sn;

        return new IcalImportController(
            $sn,
            new IcsFileFinder(self::getDataFolder()),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            self::makeView()
        );
    }

    private static function now(): LocalDateTime
    {
        $result = LocalDateTime::fromIsoString(date('Y-m-d\TH:i'));
        assert($result !== null);
        return $result;
    }

    private static function getDataFolder(): string
    {
        global $pth, $sl, $cf, $plugin_cf;

        $dataFolder = $pth['folder']['content'];
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages'] && $sl !== $cf['language']['default']) {
            $dataFolder = dirname($dataFolder) . '/';
        }
        return $dataFolder;
    }

    /** @return non-empty-string */
    private static function getDpSeparator(): string
    {
        global $plugin_cf;

        $sep = $plugin_cf['calendar']['date_delimiter'];
        if (!in_array($sep, ['.', '/', '-'], true)) {
            $sep = '.';
        }
        return $sep;
    }

    private static function getCsrfProtector(): CsrfProtector
    {
        global $_XH_csrfProtection;

        if ($_XH_csrfProtection === null) {
            $_XH_csrfProtection = new CsrfProtector();
        }
        return $_XH_csrfProtection;
    }

    private static function makeView(): View
    {
        global $pth, $plugin_tx;

        return new View("{$pth['folder']['plugins']}calendar/views/", $plugin_tx['calendar']);
    }
}
