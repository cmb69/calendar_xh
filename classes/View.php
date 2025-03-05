<?php

/**
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

class View
{
    /** @var string */
    private $viewFolder;

    /** @var array<string,string> */
    private $lang;

    /** @param array<string,string> $lang */
    public function __construct(string $viewFolder, array $lang)
    {
        $this->viewFolder = $viewFolder;
        $this->lang = $lang;
    }

    /**
     * @param float|int|string $args
     */
    protected function text(string $key, ...$args): string
    {
        return sprintf(XH_hsc($this->lang[$key]), ...$args);
    }

    /**
     * @param float|int|string ...$args
     */
    protected function plural(string $key, int $count, ...$args): string
    {
        $key .= XH_numberSuffix($count);
        return sprintf(XH_hsc($this->lang[$key]), $count, ...$args);
    }

    /**
     * @param array<string,mixed> $_data
     */
    public function render(string $_template, array $_data): string
    {
        array_walk_recursive($_data, function (&$value) {
            if (is_string($value)) {
                $value = XH_hsc($value);
            } elseif ($value instanceof HtmlString) {
                $value = $value->string();
            }
        });
        extract($_data);
        ob_start();
        include "{$this->viewFolder}{$_template}.php";
        return (string) ob_get_clean();
    }
}
