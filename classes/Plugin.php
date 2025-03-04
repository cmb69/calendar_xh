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
                return Dic::makeInfoController()->defaultAction();
            case 'plugin_main':
                return self::mainAdministration();
            case 'import':
                return self::iCalendarImport();
            default:
                return plugin_admin_common();
        }
    }

    private static function mainAdministration(): string
    {
        global $plugin_tx;

        return sprintf('<h1>Calendar – %s</h1>', XH_hsc($plugin_tx['calendar']['menu_main']))
            . self::editEvents();
    }

    private static function iCalendarImport(): string
    {
        global $action;

        switch ($action) {
            case 'import':
                return Dic::makeIcalImportController()->importAction()->trigger();
            default:
                return Dic::makeIcalImportController()->defaultAction()->trigger();
        }
    }

    /** @return string|never */
    public static function calendar(int $year = 0, int $month = 0, string $eventpage = '')
    {
        return Dic::makeCalendarController()->defaultAction($year, $month, $eventpage)->trigger();
    }

    public static function events(int $month = 0, int $year = 0, int $end_month = 0, int $past_month = 0): string
    {
        return Dic::makeEventListController()->defaultAction($month, $year, $end_month, $past_month);
    }

    public static function nextEvent(): string
    {
        return Dic::makeNextEventController()->defaultAction();
    }

    public static function editEvents(): string
    {
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
        $controller = Dic::makeEditEventController();
        if (!is_callable([$controller, $action])) {
            $action = 'defaultAction';
        }
        return $controller->{$action}()->trigger();
    }

    public static function now(): LocalDateTime
    {
        $result = LocalDateTime::fromIsoString(date('Y-m-d\TH:i'));
        assert($result !== null);
        return $result;
    }
}
