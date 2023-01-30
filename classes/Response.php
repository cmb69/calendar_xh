<?php

/**
 * Copyright 2023 Christoph M. Becker
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

class Response
{
    /** @var ?string */
    private $output;

    /** @var ?string */
    private $location;

    public static function create(string $output): self
    {
        $that = new Response();
        $that->output = $output;
        return $that;
    }

    public static function createRedirect(string $location): self
    {
        $that = new Response();
        $that->location = $location;
        return $that;
    }

    private function __construct()
    {
    }

    public function output(): string
    {
        return $this->output;
    }

    public function location(): string
    {
        return $this->location;
    }

    /** @return string|never */
    public function trigger()
    {
        if ($this->location !== null) {
            header("Location: {$this->location}");
            exit;
        }
        return $this->output;
    }
}
