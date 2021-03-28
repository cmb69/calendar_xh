<?php

/**
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

class View
{
    /**
     * @var array
     */
    public $data = array();

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function __call($name, array $args)
    {
        return $this->escape($this->data[$name]);
    }

    /**
     * @param string $template
     * @param array<string,mixed> $data
     * @return string
     */
    public function getString($template, array $data)
    {
        ob_start();
        $this->render($template, $data);
        return ob_get_clean();
    }

    /**
     * @param string $key
     * @return string
     */
    protected function text($key)
    {
        global $plugin_tx;

        $args = func_get_args();
        array_shift($args);
        return $this->escape(vsprintf($plugin_tx['calendar'][$key], $args));
    }

    /**
     * @param string $key
     * @param int $count
     * @return string
     */
    protected function plural($key, $count)
    {
        global $plugin_tx;

        $key .= XH_numberSuffix($count);
        $args = func_get_args();
        array_shift($args);
        return $this->escape(vsprintf($plugin_tx['calendar'][$key], $args));
    }

    /**
     * @param string $_template
     * @param array<string,mixed> $_data
     * @return void
     */
    public function render($_template, $_data)
    {
        global $pth;

        $this->data = $_data;
        /** @psalm-suppress UnresolvableInclude */
        include "{$pth['folder']['plugins']}calendar/views/{$_template}.php";
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function escape($value)
    {
        if ($value instanceof HtmlString) {
            return (string) $value;
        } else {
            return XH_hsc((string) $value);
        }
    }
}
