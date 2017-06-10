<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
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

class EventListController extends Controller
{
    private $month;

    private $year;

    private $endMonth;

    private $pastMonth;

    public function __construct($month, $year, $end_month, $past_month)
    {
        parent::__construct();
        $this->month = $month;
        $this->year = $year;
        $this->endMonth = $end_month;
        $this->pastMonth = $past_month;
    }

    public function defaultAction()
    {
        $month_input = isset($_GET['month']) ? htmlspecialchars($_GET['month']) : '';
        $month_input .= isset($_POST['month']) ? htmlspecialchars($_POST['month']) : '';

        if ($this->month) {
            if ($month_input) {
                if ($this->month >= $month_input) {
                    $this->month = $month_input;
                }
            }
        } else {
            $this->month = $month_input;
        }

        $this->year = isset($_GET['year']) ?  htmlspecialchars($_GET['year']) : '';
        $this->year .= isset($_POST['year']) ?  htmlspecialchars($_POST['year']) : '';

        if ($this->month == '') {
            $this->month = date('m');
        }
        if ($this->year == '') {
            $this->year = date('Y');
        }

        if (!$this->pastMonth) {
            $this->pastMonth = $this->conf['show_number_of_previous_months'];
        }
        if (!$this->pastMonth) {
            $this->pastMonth = 0;
        }

        $this->month = $this->month - $this->pastMonth;
        if ($this->month < 1) {
            $this->year = $this->year - 1;
            $this->month = 12 + $this->month;
        }
        //Now $month and $year give the dates for the event display

        if ($this->endMonth == '') {
            if ($this->conf['show_number_of_future_months']) {
                $this->endMonth = $this->conf['show_number_of_future_months'];
            } else {
                $this->endMonth= "1";
            }
        }
    
        $display_end_month = $this->month + $this->endMonth + $this->pastMonth;
        $display_end_year = $this->year;
        while ($display_end_month > 12) {
            $display_end_year = $display_end_year + 1;
            $display_end_month = $display_end_month - 12;
        }

        $this->endMonth = $this->endMonth + $this->pastMonth + 1;

        $event_yearmonth_array  = array();
        $events = (new EventDataService)->readEvents();
        foreach ($events as $event) {
            list($event->startday, $event->startmonth, $event->startyear)
                = explode($this->dpSeperator(), $event->datestart);
            if (isset($event->dateend)) {
                list($event->endday, $event->endmonth, $event->endyear)
                    = explode($this->dpSeperator(), $event->dateend);
            } else {
                $event->endday = $event->endmonth = $event->endyear = null;
            }
            $event->datetime = "{$event->datestart} {$event->starttime}";
            $event->link = "{$event->linkadr},{$event->linktxt}";
            $event_yearmonth_array[] = "{$event->startmonth}.{$event->startyear}";
        }

        $x = 1;

        $textmonth = date('F', mktime(1, 1, 1, $this->month, 1, $this->year));
        $monthnames = explode(',', $this->lang['monthnames_array']);

        $t = '';
        if ($this->conf['show_period_of_events']) {
            $t .= "<p class=\"period_of_events\">"
               .  $this->lang['text_announcing_overall_period']
               .  " <span>"
               .  $monthnames[$this->month - 1] . " "
               .  $this->year . "</span> "
               .  $this->lang['event_date_till_date']
               .  " <span>" . $monthnames[$display_end_month - 1]
               .  " " . $display_end_year . "</span></p>\n";
        }

        $t .= "<table border=\"0\" width=\"100%\">\n";

        // the number of tablecolumns is calculated
        // starting with minimum number of columns (date + main entry)
        $tablecols = 2;
        // adding columns according to config settings
        if ($this->conf['show_event_time']) {
            $tablecols++;
        }
        if ($this->conf['show_event_location']) {
            $tablecols++;
        }
        if ($this->conf['show_event_link']) {
            $tablecols++;
        }

        while ($x <= $this->endMonth) {
            $textmonth = $monthnames[$this->month - 1];
            $today = (isset($today)) ? $today : date('j');
            $today = ($this->month == date('m') && $this->year == date('Y')) ? $today : 32;

            $table = false;
            /*headline with month, year and subheadline is being generated*/
            if (in_array("{$this->month}.{$this->year}", $event_yearmonth_array)) {
                $table = true;
            }
            if ($table) {
                $t .= new HtmlString($this->createHeadlineView($tablecols, $textmonth));
            }

            usort($events, function ($a, $b) {
                return strcmp($a->datetime, $b->datetime);
            });

            foreach ($events as $event) {
                //=============================================
                //here the case of birthday annoncements starts
                //=============================================
                if (trim($event->location) == '###' && $event->startmonth == $this->month) {
                    $age = $this->year - $event->startyear;
                    if ($age >= 0) {
                        if ($this->month < 10) {
                            if (strlen($this->month) == 1) {
                                $this->month = "0{$this->month}";
                            }
                        }

                        //headline with month has to be generated in case there is no ordinary event
                        if (!$table) {
                            $table = true;
                            $t .= $this->createHeadlineView($tablecols, $textmonth);
                        }

                        $t .= "<tr class=\"birthday_data_row\">\n";
                        $t .= "<td class=\"event_data event_date\">{$event->startday}" . $this->dpSeperator()
                            . "{$this->month}" . $this->dpSeperator() . "{$this->year}</td>\n";
                        if ($this->conf['show_event_time']) {
                            $t .= "<td class=\"event_data event_time\"></td>\n";
                        }

                        if ($age >= 5) {
                            $t .= "<td class=\"event_data event_event\">"
                                . "{$event->event} {$age} {$this->lang['age_plural2_text']}</td>\n";
                        } elseif ($age >= 2 && $age < 5) {
                            $t .= "<td class=\"event_data event_event\">"
                                . "{$event->event} {$age} {$this->lang['age_plural1_text']}</td>\n";
                        } else {
                            $t .= "<td class=\"event_data event_event\">"
                                . "{$event->event} {$age} {$this->lang['age_singular_text']}</td>\n";
                        }

                        if ($this->conf['show_event_location']) {
                            $t .= "<td class=\"event_data event_location\">"
                                . "{$this->lang['birthday_text']}</td>\n";
                        }
                        if ($this->conf['show_event_link']) {
                            if (strpos($event->link, 'ext:') === 0) {
                                $external_site = substr($event->link, 4);
                                list($external_site, $external_text) = explode(',', $external_site);
                                if (!$external_text) {
                                    $external_text = $external_site;
                                }
                                $t .= "<td class=\"event_data event_link\"><a href=\"http://"
                                    . "{$external_site}\" target=\"_blank\" title=\""
                                    . strip_tags($event->event) . "\">$external_text</a></td>\n";
                            } elseif (strpos($event->link, 'int:') === 0) {
                                $internal_page = substr($event->link, 4);
                                list($internal_page, $internal_text) = explode(',', $internal_page);
                                if (!$internal_text) {
                                    $internal_text = $internal_page;
                                }
                                $t .= "<td class=\"event_data event_link\"><a href=\"?"
                                    . "{$internal_page}\" title=\""
                                    . strip_tags($event->event) . "\">$internal_text</a></td>\n";
                            } else {
                                $t .= "<td class=\"event_data event_link\">";
                                if (substr($event->link, 0, 1) == ',') {
                                    $t .= substr(strip_tags($event->link), 1) . "</td>\n";
                                } else {
                                    $t .= strip_tags($event->link) . "</td>\n";
                                }
                            }
                        }
                        $t .= "</tr>\n";
                    }
                }
    
                //==================
                // now normal events
                //==================
                if ($event->startyear == $this->year && $event->startmonth == $this->month) {
                    if ($this->month < 10) {
                        if (strlen($this->month) == 1) {
                            $this->month = '0' . $this->month;
                        }
                    }
                    $t .= "<tr class=\"event_data_row\">\n";
                    //now

                    //date field
                    $t .= "<td class=\"event_data event_date\">{$event->startday}";
                    // if beginning and end dates are there, these are put one under the other
                    if ($event->endday) {
                        if ($this->month != $event->endmonth
                            || $this->year != $event->endyear
                        ) {
                            $t .= $this->dpSeperator() . $this->month;
                        }
                        if ($this->year != $event->endyear) {
                            $t .= $this->dpSeperator() . $this->year;
                        }
                        if ($this->year == $event->endyear && $this->dpSeperator() == '.') {
                            $t.= '.';
                        }
                        $t .= "&nbsp;" . $this->lang['event_date_till_date'] . tag('br');
                        $t .= $event->endday . $this->dpSeperator() . $event->endmonth
                            . $this->dpSeperator() . $event->endyear;
                    } else {
                        $t .= $this->dpSeperator() . "{$this->month}" . $this->dpSeperator() . $this->year;
                    }

                    $t .= "</td>\n";

                    //time field
                    if ($this->conf['show_event_time']) {
                        $t .="<td class=\"event_data event_time\">" . $event->starttime;
                        if ($event->endtime) {
                            if (!$event->endday) {
                                $t .= ' ' . $this->lang['event_time_till_time'];
                            }
                            $t .= tag('br') . $event->endtime;
                        }
                        $t .="</td>\n";
                    }

                    //event field
                    $t .= "<td class=\"event_data event_event\">" . $event->event . "</td>\n";

                    //location field
                    if ($this->conf['show_event_location']) {
                        $t .= "<td class=\"event_data event_location\">{$event->location}</td>\n";
                    }

                    //link field
                    if ($this->conf['show_event_link']) {
                        if (strpos($event->link, 'ext:') === 0) {
                            $external_site = substr($event->link, 4);
                            list($external_site,$external_text) = explode(',', $external_site);
                            if (!$external_text) {
                                $external_text = $external_site;
                            }
                            $t .= "<td class=\"event_data event_link\"><a href=\"http://{$external_site}\""
                                . " target=\"_blank\" title=\"" . strip_tags($event->event)
                                . "\">$external_text</a></td>\n";
                        } elseif (strpos($event->link, 'int:') === 0) {
                            $internal_page = substr($event->link, 4);
                            list($internal_page,$internal_text) = explode(',', $internal_page);
                            if (!$internal_text) {
                                $internal_text = $internal_page;
                            }
                            $t .= "<td class=\"event_data event_link\"><a href=\"?{$internal_page}\" title=\""
                                . strip_tags($event->event) . "\">$internal_text</a></td>\n";
                        } else {
                            $t .= "<td class=\"event_data event_link\">";
                            if (substr($event->link, 0, 1) == ',') {
                                $t .= substr(strip_tags($event->link), 1) . "</td>\n";
                            } else {
                                $t .= strip_tags($event->link) . "</td>\n";
                            }
                        }
                    }
                    $t .= "</tr>\n";
                }
            }
            $x++;
            if ($this->month == 12) {
                $this->year++;
                $this->month = 1;
            } else {
                $this->month++;
            }
        }
        $t .="</table>\n";
        echo $t;
    }

    private function createHeadlineView($tablecols, $textmonth)
    {
        $view = new View("event-list-headline");
        $view->tablecols = $tablecols;
        $view->textmonth = $textmonth;
        $view->year = $this->year;
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        return $view;
    }
}
