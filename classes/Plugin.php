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

use ReflectionClass;
use ReflectionMethod;
use XH\CSRFProtection as CsrfProtector;

class Plugin
{
    const VERSION = '2.4-dev';

    /**
     * @return void
     */
    public static function run()
    {
        global $sn, $plugin_tx, $admin, $o;

        self::registerUserFunctions();
        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(true);
            XH_registerPluginMenuItem(
                'calendar',
                $plugin_tx['calendar']['label_import'],
                $sn . '?&calendar&admin=import&normal'
            );
            if (XH_wantsPluginAdministration('calendar')) {
                $o .= print_plugin_admin('on');
                pluginMenu("ROW");
                pluginMenu(
                    "TAB",
                    "$sn?&calendar&admin=import&normal",
                    "",
                    $plugin_tx['calendar']['label_import']
                );
                $o .= (string) pluginMenu("SHOW");
                $o .= self::admin($admin);
            }
        }
    }

    /** @return void */
    private static function registerUserFunctions()
    {
        $rc = new ReflectionClass(self::class);
        foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $rm) {
            if (strcmp($rm->getName(), "run") !== 0) {
                $name = $rm->getName();
                $lcname = strtolower($name);
                $params = $args = [];
                foreach ($rm->getParameters() as $rp) {
                    $param = $arg = "\${$rp->getName()}";
                    if ($rp->isOptional()) {
                        $default = var_export($rp->getDefaultValue(), true);
                        assert($default !== null);
                        $param .= " = " . $default;
                    }
                    $params[] = $param;
                    $args[] = $arg;
                }
                $parameters = implode(", ", $params);
                $arguments = implode(", ", $args);
                $body = "return \\Calendar\\Plugin::$name($arguments);";
                $code = "function $lcname($parameters) {\n\t$body\n}";
                eval($code);
            }
        }
    }

    private static function admin(string $admin): string
    {
        switch ($admin) {
            case '':
                return self::info();
            case 'plugin_main':
                return self::mainAdministration();
            case 'import':
                return self::iCalendarImport();
            default:
                return plugin_admin_common();
        }
    }

    private static function info(): string
    {
        global $pth, $plugin_tx;

        $controller = new InfoController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_tx['calendar'],
            new SystemChecker(),
            self::view()
        );
        return $controller->defaultAction();
    }

    private static function mainAdministration(): string
    {
        global $plugin_tx;

        return sprintf('<h1>Calendar – %s</h1>', XH_hsc($plugin_tx['calendar']['menu_main']))
            . self::editEvents();
    }

    private static function iCalendarImport(): string
    {
        global $sn, $action;

        $controller = new IcalImportController(
            $sn,
            new IcsFileFinder(self::getDataFolder()),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            self::view()
        );
        switch ($action) {
            case 'import':
                return $controller->importAction()->trigger();
            default:
                return $controller->defaultAction()->trigger();
        }
    }

    /** @return string|never */
    public static function calendar(int $year = 0, int $month = 0, string $eventpage = '')
    {
        global $pth, $plugin_cf, $plugin_tx, $sn, $su;

        $controller = new CalendarController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::view(),
            "$sn?$su",
            $year,
            $month,
            $eventpage
        );
        return $controller->defaultAction()->trigger();
    }

    public static function events(int $month = 0, int $year = 0, int $end_month = 0, int $past_month = 0): string
    {
        global $plugin_cf, $plugin_tx;

        $controller = new EventListController(
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::view(),
            $month,
            $year,
            $end_month,
            $past_month
        );
        return $controller->defaultAction();
    }

    public static function nextEvent(): string
    {
        global $plugin_tx;

        $controller = new NextEventController(
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            self::view()
        );
        return $controller->defaultAction();
    }

    public static function editEvents(): string
    {
        global $pth, $plugin_cf, $plugin_tx, $su;

        if (isset($_POST['action'])) {
            assert(is_string($_POST['action']));
            $action = $_POST['action'];
        } elseif (isset($_GET['action'])) {
            assert(is_string($_GET['action']));
            $action = $_GET['action'];
        } else {
            $action = 'editevents';
        }
        switch ($action) {
            case 'create':
                $action = 'createAction';
                break;
            case 'update':
                $action = 'updateAction';
                break;
            case 'delete':
                $action = 'deleteAction';
                break;
            default:
                $action = 'defaultAction';
        }
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $action = "do" . ucfirst($action);
        }
        $controller = new EditEventsController(
            "{$pth['folder']['plugins']}calendar/",
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            self::getCsrfProtector(),
            self::view(),
            $su
        );
        if (!is_callable([$controller, $action])) {
            $action = 'defaultAction';
        }
        return $controller->{$action}()->trigger();
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

    private static function getDpSeparator(): string
    {
        global $plugin_cf;

        $sep = $plugin_cf['calendar']['date_delimiter'];
        if (!in_array($sep, ['.', '/', '-'], true)) {
            $sep = '.';
        }
        return $sep;
    }

    public static function now(): LocalDateTime
    {
        $result = LocalDateTime::fromIsoString(date('Y-m-d\TH:i'));
        assert($result !== null);
        return $result;
    }

    private static function getCsrfProtector(): CsrfProtector
    {
        global $_XH_csrfProtection;

        if ($_XH_csrfProtection === null) {
            $_XH_csrfProtection = new CsrfProtector();
        }
        return $_XH_csrfProtection;
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;

        return new View("{$pth['folder']['plugins']}calendar/views/", $plugin_tx['calendar']);
    }
}
