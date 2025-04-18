<?php

/**
 * Copyright (c) Christoph M. Becker
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

namespace Calendar\Model;

class Interval
{
    /** @var bool */
    private $negative = false;

    /** @var int */
    private $days;

    /** @var int */
    private $hours;

    /** @var int */
    private $minutes;

    public function __construct(int $days, int $hours, int $minutes, bool $negative = false)
    {
        assert($days >= 0);
        assert($hours >= 0 && $hours < 24);
        assert($minutes >= 0 && $minutes < 60);
        $this->negative = $negative;
        $this->days = $days;
        $this->hours = $hours;
        $this->minutes = $minutes;
    }

    public function days(): int
    {
        return $this->days;
    }

    public function hours(): int
    {
        return $this->hours;
    }

    public function minutes(): int
    {
        return $this->minutes;
    }

    public function negative(): bool
    {
        return $this->negative;
    }

    public function negate(): self
    {
        $that = clone $this;
        $that->negative = !$that->negative;
        return $that;
    }

    public function plus(int $days): self
    {
        $that = clone $this;
        if ($this->negative) {
            $that->days -= $days;
            if ($that->days < 0) {
                $that->days = -$that->days;
                if ($that->hours > 0) {
                    $that->hours = 24 - $that->hours;
                    $that->days -= 1;
                }
                if ($that->minutes > 0) {
                    $that->minutes = 60 - $that->minutes;
                    $that->hours -= 1;
                }
                if ($that->hours < 0) {
                    $that->hours += 24;
                    $that->days -= 1;
                }
                $that->negative = false;
            }
        } else {
            $that->days += $days;
        }
        return $that;
    }
}
