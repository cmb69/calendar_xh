<?php

const CMSIMPLE_URL = 'http://example.com';

const CMSIMPLE_XH_VERSION = 'CMSimple_XH 1.7.4';

/**
 * @return string
 */
function editevents() {}

/**
 * @return string
 */
function plugin_admin_common() {}

/**
 * @param string $main
 * @return string HTML
 */
function print_plugin_admin($main) {}

/**
 * @param string $string
 * @return string
 */
function XH_hsc($string) {}

/**
 * @param string $type
 * @param string $message
 * @return string
 */
function XH_message($type, $message) {}

/**
 * @param int $count
 * @return string
 */
function XH_numberSuffix($count) {}

/**
 * @param string $plugin
 * @param string $label
 * @param string $url
 * @param string $target
 * @return mixed
 */
function XH_registerPluginMenuItem($plugin, $label = null, $url = null, $target = null) {}

/**
 * @param bool $showMain
 * @return void
 */
function XH_registerStandardPluginMenuItems($showMain) {}

/**
 * @param string $pluginName
 * @return bool
 */
function XH_wantsPluginAdministration($pluginName) {}

namespace XH {
    class CSRFProtection {
        public function tokenInput(): string {}
        public function check() {}
    }
}
