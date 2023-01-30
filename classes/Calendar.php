<?php

/**
 * Copyright 2021-2023 Christoph M. Becker
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

class Calendar
{
    /** @var bool */
    private $weekStartsMonday;

    public function __construct(bool $weekStartsMonday)
    {
        $this->weekStartsMonday = $weekStartsMonday;
    }

    /**
     * @return array<int,array<int,(int|null)>>
     */
    public function getMonthMatrix(int $year, int $month): array
    {
        $result = [];
        $row = [];
        $days = (int) date('t', mktime(1, 1, 1, $month, 1, $year));
        for ($day = 1; $day <= $days; $day++) {
            $dayofweek = $this->getDayOfWeek($year, $month, $day);
            if ($day === 1) {
                for ($i = 0; $i < $dayofweek; $i++) {
                    $row[] = null;
                }
            }
            $row[] = $day;
            if ($day === $days) {
                for ($i = $dayofweek + 1; $i < 7; $i++) {
                    $row[] = null;
                }
            }
            if (count($row) === 7 || $day === $days) {
                $result[] = $row;
                $row = [];
            }
        }
        return $result;
    }

    private function getDayOfWeek(int $year, int $month, int $day): int
    {
        $dayofweek = (int) date('w', mktime(1, 1, 1, $month, $day, $year));
        if ($this->weekStartsMonday) {
            $dayofweek = ($dayofweek + 6) % 7;
        }
        return $dayofweek;
    }
}
