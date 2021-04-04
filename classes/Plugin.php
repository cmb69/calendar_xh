<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
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

use ReflectionClass;
use ReflectionMethod;

class Plugin
{
    const VERSION = '2.0dev1';

    /**
     * @return void
     */
    public static function run()
    {
        global $sn, $plugin_tx, $admin, $o;

        self::registerUserFunctions();
        /** @psalm-suppress UndefinedConstant */
        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(true);
            XH_registerPluginMenuItem(
                'calendar',
                $plugin_tx['calendar']['label_import'],
                $sn . '?&calendar&admin=import&normal'
            );
            if (XH_wantsPluginAdministration('calendar')) {
                $o .= print_plugin_admin('on')
                    . self::admin($admin);
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

    /**
     * @param string $admin
     * @return string
     */
    private static function admin($admin)
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

    /**
     * @return string
     */
    private static function info()
    {
        ob_start();
        (new InfoController(new View()))->defaultAction();
        return ob_get_clean();
    }

    /**
     * @return string
     */
    private static function mainAdministration()
    {
        global $plugin_tx;

        return sprintf('<h1>Calendar – %s</h1>', XH_hsc($plugin_tx['calendar']['menu_main']))
            . self::editEvents();
    }

    /**
     * @return string
     */
    private static function iCalendarImport()
    {
        global $action;

        $controller = new IcalImportController(
            self::getDataFolder(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new View()
        );
        ob_start();
        switch ($action) {
            case 'import':
                $controller->importAction();
                break;
            default:
                $controller->defaultAction();
        }
        return ob_get_clean();
    }

    /**
     * @param int $year
     * @param int $month
     * @param string $eventpage
     * @return string
     */
    public static function calendar($year = 0, $month = 0, $eventpage = '')
    {
        global $plugin_cf, $plugin_tx;

        ob_start();
        $controller = new CalendarController(
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            new View(),
            $year,
            $month,
            $eventpage
        );
        $controller->defaultAction();
        return ob_get_clean();
    }

    /**
     * @param int $month
     * @param int $year
     * @param int $end_month
     * @param int $past_month
     * @return string
     */
    public static function events($month = 0, $year = 0, $end_month = 0, $past_month = 0)
    {
        global $plugin_cf, $plugin_tx;

        ob_start();
        $controller = new EventListController(
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::getDpSeparator(),
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new DateTimeFormatter($plugin_tx['calendar']),
            new View(),
            $month,
            $year,
            $end_month,
            $past_month
        );
        $controller->defaultAction();
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function nextEvent()
    {
        global $plugin_tx;

        ob_start();
        $controller = new NextEventController(
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new View()
        );
        $controller->defaultAction();
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public static function editEvents()
    {
        global $plugin_cf, $plugin_tx;

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
            case 'saveevents':
                $action = 'saveAction';
                break;
            default:
                $action = 'defaultAction';
        }
        $controller = new EditEventsController(
            $plugin_cf['calendar'],
            $plugin_tx['calendar'],
            self::now(),
            new EventDataService(self::getDataFolder(), self::getDpSeparator()),
            new View()
        );
        ob_start();
        $controller->{$action}();
        return ob_get_clean();
    }

    /** @return string */
    private static function getDataFolder()
    {
        global $pth, $sl, $cf, $plugin_cf;

        $dataFolder = $pth['folder']['content'];
        if ($plugin_cf['calendar']['same-event-calendar_for_all_languages'] && $sl !== $cf['language']['default']) {
            $dataFolder = dirname($dataFolder) . '/';
        }
        return $dataFolder;
    }

    /**
     * @return string
     */
    private static function getDpSeparator()
    {
        global $plugin_cf;

        $sep = $plugin_cf['calendar']['date_delimiter'];
        if (!in_array($sep, ['.', '/', '-'], true)) {
            $sep = '.';
        }
        return $sep;
    }

    /**
     * @return LocalDateTime
     */
    public static function now()
    {
        $result = LocalDateTime::fromIsoString(date('Y-m-d\TH:i'));
        assert($result !== null);
        return $result;
    }
}
