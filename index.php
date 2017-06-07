<?php
/*
CMSimple - Calendar Plugin (based on v. 0.5 from 2006 by Michael Svarrer)

-- 1.2.10 Update eliminating rare problems with magic quotes. Fix by cmb.
-- 1.2.9 Update with ideas of cmb (empty lines bug on some servers, rel_"nofollow" added)
-- 1.2.8 Security update (on adive by cmb)
-- 1.2.7 (10/2011) Slovak translation completed by Stanka
    - Fixed bug in calendar: Now calendar title-tooltip can show more than 1 event per day.

-- 1.2.6 Maintenance release by svasti
    - Bug fixed: number of table columns now determined dynamically
    - Bug fixed: switching off field doesn't delete values any more (added all switched off fields as hidden values)
    - Beauty fix: Date of multi-day events is presented in a nicer way
    - User demand: Date can again be entered directly (date check added, "-,--,?,??" possible as days)
    - User demand: Swichted off fields are now also off in all input tables
      (was a big job!) and remailing fields now fill available space automatically
    - Some changes in css
    - Bug fixed: Enddate in Birthday would screw up the coloring of the calendar display, Now enddate automatically deleted in birthday case
    - changed all <input.. and <img .. to tag('input... and tag('img.. so that even the backand validates (could be used on member pages)
    - added filepath_data in config
    - added possibility to have the notice "no next event sceduled" in next event
-- 1.2 Build 5 by svasti
    - Possibility to enter any text in link-text field,
    - further cleaning up the code,
    - width definition of input fields moved to css, now IE and FF display the input table in the same way,
    - sorting on save, function written by manu

-- Version 1.2 modified by Holger http://cmsimple.holgerirmler.de
   - Fixed some PHP 5.3.0 deprecated code (split() ...)
   - Added xh_debugmode() and a bit security
   - Removed direct edit of the eventfiles in admin-area
   - Bugfix: layout crashed on eventspage when a birthday (###) was the first event in a month
   - Added the DatePicker v5.4 by frequency-decoder.com and made the date inputs readonly
   - Replaced ";" by " " in users input to prevent a script-crash and to keep the field-structure in the event-files
   - nextevent() will return now nothing when there is no future event
   - $plugin_cf['calendar']['date_delimiter']: is ready to use full-stop ".", forward slash "/" and minus/dash "-",
     a changed delimitter at runtime will be converted by the DatePicker, so no crashed eventfiles anymore
   - Slightly changed the "narrow" style in the backend

-- Version 1.1 modified by svasti http://svasti.de
   - xh-unicode-compatible
   - lots of obsolete attributes moved from config to css (otherwise no validation in html5)
   - elimination of unnecessary attributes
   - changed obsolete php code ("eregi" is obsolete in php 5.3)
   - improvement in backend, 3 input styles available (wide, medium narrow)
   - tooltips in config
   - extra line in event display giving the period for which the events are listed,
   - possibility to show past events, new way to show end of events
   - bug corrections: begin and end display of events lasting several days were mixed up
   - events from different years would mess up the event-display,
   - new way to show end of events (date and/or time),
   - correction of other minor bugs (like birthday in calendar view) plus beautifying the code display a bit... but not too much

----------------------------------------------------------------------------------------------------------------
-- Version 1.0 By Tory
-- The function of showing/not showing time, location and link in event view will now work again
----------------------------------------------------------------------------------------------------------------
Modified by Bob (cmsimple.fr), 02/2008. (this is from CMSimple Flex package)
----------------------------------------------------------------------------------------------------------------
-- Version 0.9 By Tory
-- Minor bugs corrected regarding Date-delimiter problem in events in Calendar view.
-- Event Page is now configured in the language file. You therefore don't need to add Eventpage-name in Calender call in Template or Calendar-page
-- Singular-Plural text regarding age in birtdays in EventPage are now added to language files.
-- Parameters added to language files are:
 $plugin_tx['calendar']['event_page']="Events";  - The Eventpage, in wich Events in Calendar are related to.
 $plugin_tx['calendar']['age_singular_text']="year"; age text for ages <2 Years
 $plugin_tx['calendar']['age_plural1_text']="years";
 age text for ages >1 and <5 years
 $plugin_tx['calendar']['age_plural2_text']="years"; age text for ages >4 years, used in many east european contries

- Version 0.8 By Tory
Events can now be added / edited / deleted from a CMSimple page, without the Administrator Rights.
Just create a page and add the following line:
#CMSimple $output.=EditEvents();#
The page can be behind Memberspage or Register plugin to allow just a single group of members to be Editor of the Event File. Eg.: #CMSimple $output.=access('admin');$output.=EditEvents();# (Used in combination with register plugin.)
Only index.php file in Calender plugin has been changed since Version 0.7
- Version 0.7 By Tory based on Svarrers version 0.5 : 20. Sep. 2007
Several dates events are now handled so that they can last over several days, weeks, month or years.
Begin and End date/time are entered in the eventfile separately. They are only shown in Eventpage with
the Begin date and End date. In the Calender it is possible to select whether the between dates should be
higlighted or not.
In Nextevent and Event-page are automatically written Begin date-time or End date-time to a several dates event.
- Version 0.6 Mod by Tory : 16. Sep 2007
New Function added: nextevent() will display next coming event compared to current date-time.
New way of sorting events that makes it possible to have more events the same day, and have them sorted
by date and time.
Events are entered in the eventfile this way:
Date;Event;Location (### = birthday);Link (int:?InternalPage / ext:www.ExternalPage.com),LinkTxT;Time (eg: 09:45)
Date can be: begin-date,end-date,end-time for events over several days e.g: 19-6-2007,27-6-2007,16:30
- Version 0.5 : 17. mar 2006
New feature added external linkage and miltiple dates for same event syntax
date delimiter can be /.- for input. For output the event table parameter
$plugin_cf['calendar']['date_delimiter'] controls the output
New CSS clases added for the event_monthyear, event_heading,event_data
Improved way of generating event table implemented
- Version 0.4 : 11. sept 2005
 Title added to link in calendar mode showing event
 $end_month added in the events function, here you specify the amount
 of months the events function should show.
 Prev/Next month button avaliable, set 'prev_next_button' to true to enable
 Bug when using week_starts_monday = false
- Version 0.3 : Third release - 16. jun 2005
Parameters added to config
 $plugin_cf['calendar']['calendar_width']
 $plugin_cf['calendar']['event_width_date']="20%";
 $plugin_cf['calendar']['event_width_event']="60%";
 $plugin_cf['calendar']['event_width_location']="20%";
- Version 0.2 : Second release - 15. jun 2005
Some Notice: Undefined variable: for different variables
*/

if ((!function_exists('sv')) || (strlen($sl) !== 2) || preg_match('/calendar'.DIRECTORY_SEPARATOR.'index.php/i', sv('PHP_SELF')))die('Access denied');

if (!$plugin_cf['calendar']['filepath_data']){
    $datapath = $pth['folder']['plugins'].$plugin."/content/";
} else {
    $datapath = $plugin_cf['calendar']['filepath_data'];
}

/*function to display a calendar in the template or on a page*/
function calendar($year='',$month='',$eventpage='')
{
    global $plugin_cf,$plugin_tx,$datapath,$sl,$plugin,$sn,$su;
    $plugin=basename(dirname(__FILE__),"/");
    if ($eventpage == '') $eventpage = $plugin_tx['calendar']['event_page'];
    if ($plugin_cf['calendar']['same-event-calendar_for_all_languages']=="true") {
        $eventfile = $datapath."eventcalendar.txt";
    }else {
        $eventfile = $datapath."eventcalendar_".$sl.".txt";
    }
    if(!is_file($eventfile)){$handle = fopen($eventfile, "w");
    fclose($handle);}

    $startmon = strtolower($plugin_cf[$plugin]['week_starts_mon']);

    if($month==''){$month = isset($_GET['month']) ? htmlspecialchars($_GET['month']) : date("m",time());}
    if($year==''){$year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : date("Y",time());}

    $event_year_array           = array();
    $event_month_array          = array();
    $event_yearmonth_array      = array();
    $event_date_array           = array();
    $event_array                = array();
    $event_location_array       = array();
    $event_time_array           = array();

    $t                = '';
    $event_dates      = '';
    $eventdates       = '';
    $event_date       = '';
    $event_date_start = '';
    $event_end_date   = '';
    $event_year       = '';
    $event_month      = '';
    $event_yearmonth  = '';
    $event            = '';
    $event_day        = '';
    $location         = '';
    $event_today      = '';
    $event_title      = '';
    $event_time       = '';
    $event_end_time   = '';

    if(is_file($eventfile)){
        $fp = fopen($eventfile, "r");
        while (!feof($fp)){
            $line = fgets($fp, 4096);
            if(stristr($line,';')){
                 list($eventdates,$event,$location,,$event_time) = explode( ";", $line);
                 if(stristr($eventdates, ',')){
                      list($event_date_start,$event_end_date,$event_end_time) = explode(",",$eventdates);
                      list($event_date1,$event_month1,$event_year1) = explode( dpSeperator(), $event_end_date);
                      list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
                      $event_end = mktime(null,null,null,$event_month1,$event_date1,$event_year1);
                      $event_start = mktime(null,null,null,$event_month,$event_date,$event_year);
                 }else{
                      $event_date_start = $eventdates;
                      $event_end_date = '';
                      $event_end_time = '';
                      list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
                 }
           }
           if($event_end_date){
                $txt = $event." ".$plugin_tx['calendar']['event_date_till_date']." " . $event_end_date ." ". $event_end_time;
                if(stristr("true",$plugin_cf['calendar']['show_days_between_dates'])){$count = 86400;}else{$count = $event_end-$event_start;}
                for($i=$event_start; $i <= $event_end; $i+=$count):
                array_push($event_year_array, date('Y',$i));
                array_push($event_month_array, date('m',$i));
                array_push($event_yearmonth_array, date('Y.m',$i));
                array_push($event_date_array, date('d',$i));
                array_push($event_location_array, $location);
                if($i==$event_start){
                    array_push($event_time_array, $event_time);
                    array_push($event_array, " ".$txt);
                }else{
                    array_push($event_time_array, "");
                    array_push($event_array, $txt);
                }
                endfor;
            }else{
                array_push($event_year_array, $event_year);
                array_push($event_month_array, $event_month);
                array_push($event_yearmonth_array, $event_yearmonth);
                array_push($event_date_array, $event_date);
                if($event_time<>''){array_push($event_array, " ".$event);}else{array_push($event_array, $event);}
                array_push($event_location_array, $location);
                array_push($event_time_array, $event_time);
            }
        }
        fclose($fp);
    }


    $month = (isset($month)) ? $month : date("n",time());
    $textmonth = date("F",mktime(1,1,1,$month,1,$year));

    $monthnames = explode(",", $plugin_tx['calendar']['monthnames_array']);

    $textmonth = $monthnames[$month - 1];

      $year     = (isset($year)) ? $year : date("Y",time());
      $today    = (isset($today))? $today : date("j", time());
      $today    = ($month == date("n",time()) && $year == date("Y",time())) ? $today : 32;
      $days     = date("t",mktime(1,1,1,$month,1,$year));
      $dayone   = date("w",mktime(1,1,1,$month,1,$year));
      $daylast  = date("w",mktime(1,1,1,$month,$days,$year));
      $dayarray = explode(",", $plugin_tx['calendar']['daynames_array']);


    $t .="<table class='calendar_main'>\n<tr>\n";
    $t .="<td colspan='7'>\n";


    if(stristr("true",$plugin_cf['calendar']['prev_next_button'])) {
        if($month<=1){$month_prev=12;$year_prev=$year-1;}
        else {$month_prev=$month-1;$year_prev=$year;}
        if($month>=12){$month_next=1;$year_next=$year+1;}
        else {$month_next=$month+1;$year_next=$year;}
        $t .= "<div class='calendar_monthyear'>\n<a href='$sn?$su&amp;month=$month_prev&amp;year=$year_prev' rel='nofollow' title='"
           .  $plugin_tx['calendar']['prev_button_text']
           .  "'>&lt;&lt;</a>&nbsp;$textmonth $year&nbsp;<a href='$sn?$su&amp;month=$month_next&amp;year=$year_next' rel='nofollow' title='"
           .  $plugin_tx['calendar']['next_button_text'] . "'>&gt;&gt;</a></div>\n";
    }
    else $t .="<div class='calendar_monthyear'>$textmonth $year</div>\n";

    $t .="</td>\n";
    $t .="</tr>\n<tr>\n";

        for($i=0; $i <= 6; $i++):
         if($startmon=='true'){$j=$i+1;}else{$j=$i;}
         if($j==7)$j=0;

    $t .="<td class='calendar_daynames'>$dayarray[$j]</td>\n";
        endfor;
    $t .="</tr>\n";
    //done printing the top row of days

         if($startmon=='true'){$span1 = $dayone-1;}else{$span1 = $dayone;}
         if($span1==-1)$span1=6;

         if($startmon=='true'){$span2 = 7 - $daylast;}else{$span2 = 6 - $daylast;}
         if($span2==7)$span2=0;
      for($i = 1; $i <= $days; $i++):
        $dayofweek = date("w",mktime(1,1,1,$month,$i,$year));

         if($startmon=='true')$dayofweek=$dayofweek-1;
         if($dayofweek==-1)$dayofweek=6;

    foreach($event_year_array as $keys=>$temp){
    if($event_year_array[$keys] == $year
       and $event_month_array[$keys] == $month
       and $event_date_array[$keys]==$i){
       $event_day=$i;
       $external_site ='';
    if($event_title){
       $event_title.=" &nbsp;|&nbsp; ".trim($event_time_array[$keys]).strip_tags($event_array[$keys]);}
       else{
       $event_title=trim($event_time_array[$keys]).strip_tags($event_array[$keys]);}
    }

    if(trim($event_location_array[$keys])=='###'
       and $event_month_array[$keys] == $month
       and $event_date_array[$keys]==$i){
       $event_day=$i;
       $age = $year-$event_year_array[$keys];
       if ($age >= 5){
             $age .= " ".$plugin_tx['calendar']['age_plural2_text'];
       }
       elseif ($age >= 2 and $age < 5){
           $age .= " ".$plugin_tx['calendar']['age_plural1_text'];
       }
       else {
           $age .= " ".$plugin_tx['calendar']['age_singular_text'];
       }

       $external_site ='';

    if($event_title){$event_title.="\r\n".$event_array[$keys]." ".$age;}else{

       $event_title= $event_array[$keys]." ".$age;}
    }
     }

        $tableday = $i;
          if($i == 1 || $dayofweek == 0):
          $t .= "<tr>\n";
          if($span1 > 0 && $i == 1)
            $t .= "<td class='calendar_noday' colspan='$span1'>&nbsp;</td>\n";
        endif;

    if($today==$event_day)$event_today=$today;

        switch ($i):
            case $event_today:
    if($external_site){$t .="<td class='calendar_today'><a href='http://".$external_site."' target='_blank' title='$event_title'>$tableday</a></td>\n";
    }else{$t .="<td class='calendar_today'><a href='?".$eventpage."&amp;month=$month&amp;year=$year' title='$event_title'>$tableday</a></td>\n";$event_title='';}
                  break;
            case $today:
            $t .="<td class='calendar_today'>$tableday</td>\n";
                  break;
            case $event_day:
    if($external_site){$t .="<td class='calendar_eventday'><a href='http://".$external_site."' target='_blank' title='$event_title'>$tableday</a></td>\n";}
    else{$t .="<td class='calendar_eventday'><a href='?".$eventpage."&amp;month=$month&amp;year=$year' title='$event_title'>$tableday</a></td>\n";$event_title='';}
                  break;

          default:
          if ($dayofweek == $plugin_cf['calendar']['week-end_day_1'] || $dayofweek == $plugin_cf['calendar']['week-end_day_2']) {$t .="<td class='calendar_we'>$tableday</td>\n";}else{$t .="<td class='calendar_day'>$tableday</td>\n";}
        endswitch;

        if($i == $days && $span2 > 0)
          $t .= "<td class='calendar_noday' colspan='$span2'>&nbsp;</td>\n";
        if($dayofweek == 6 || $i == $days)
          $t .= "</tr>\n";
      endfor;
      $t .= "</table>\n";

return $t;
}

// ****************************************************************
// *                  Display of the event list                   *
// ****************************************************************

//function to display the list of events on a page
function events($month,$year,$end_month,$past_month)
{
    global $plugin_cf,$plugin_tx,$datapath,$sl,$plugin;

    // 4 variables for bugtraking only
    $month_start      = $month;
    $year_start       = $year;
    $end_month_start  = $end_month;
    $past_month_start = $past_month;


    $month_input = isset($_GET['month']) ? htmlspecialchars($_GET['month']) : '';
    $month_input .= isset($_POST['month']) ? htmlspecialchars($_POST['month']) : '';

    if($month){
        if($month_input){
            if($month>=$month_input)$month=$month_input;}
    }else{$month=$month_input;}

    $year = isset($_GET['year']) ?  htmlspecialchars($_GET['year']) : '';
    $year .= isset($_POST['year']) ?  htmlspecialchars($_POST['year']) : '';

    if($month==''){$month = date("m",time());}
    if($year==''){$year = date("Y",time());}

    if(!$past_month) {$past_month = $plugin_cf['calendar']['show_number_of_previous_months'];}
    if(!$past_month) {$past_month = 0;}

    $month = $month - $past_month;
    if($month < 1){
        $year = $year-1;
        $month=12 + $month;
        }
    //Now $month and $year give the dates for the event display


    if($end_month==''){
        if($plugin_cf['calendar']['show_number_of_future_months']){
            $end_month = $plugin_cf['calendar']['show_number_of_future_months'];
        } else $end_month= "1";
    }

    $display_end_month = $month + $end_month + $past_month;
    $display_end_year = $year;
    while ($display_end_month > 12)
    {
        $display_end_year  = $display_end_year + 1;
        $display_end_month = $display_end_month - 12;
    }

    $end_month = $end_month + $past_month + 1;

    $plugin=basename(dirname(__FILE__),"/");

    $event_year_array       = array();
    $event_month_array      = array();
    $event_end_month_array  = array();
    $event_end_year_array   = array();
    $event_yearmonth_array  = array();
    $event_end_date_array   = array();
    $event_date_array       = array();
    $event_array            = array();
    $event_location_array   = array();
    $event_link_array       = array();
    $event_time_array       = array();
    $event_end_time_array   = array();
    $event_datetime_array   = array();

    if ($plugin_cf['calendar']['same-event-calendar_for_all_languages']=="true") {
        $eventfile = $datapath."eventcalendar.txt";
    }else {
        $eventfile = $datapath."eventcalendar_".$sl.".txt";
    }

    if(is_file($eventfile)){
        $fp = fopen($eventfile, "r");
        while (!feof($fp)) {
               $line = fgets($fp, 4096);
               //var_dump($line);
             list($eventdates,$event,$location,$link,$event_time,$description)    = explode( ";", $line);
             list($event_date_start,$event_end_date,$event_end_time) = explode(",",$eventdates);
             list($event_date,$event_month,$event_year)              = explode( dpSeperator(), $event_date_start);
             list($event_end_date,$event_end_month,$event_end_year)  = explode( dpSeperator(), $event_end_date);
             $datetime=$event_date_start ." ". $event_time;

             array_push($event_year_array,      $event_year);
             array_push($event_month_array,     $event_month);
             array_push($event_end_month_array, $event_end_month);
             array_push($event_end_year_array,  $event_end_year);
             array_push($event_yearmonth_array, ($event_month.".".$event_year));
             array_push($event_date_array,      $event_date);
             array_push($event_end_date_array,  $event_end_date);
             array_push($event_array,           $event);
             array_push($event_location_array,  $location);
             array_push($event_link_array,      $link);
             array_push($event_time_array,      $event_time);
             array_push($event_end_time_array,  $event_end_time);
             array_push($event_datetime_array,  $datetime);
         }

        fclose($fp);
    }

    $x=1;

    $textmonth  = date("F",mktime(1,1,1,$month,1,$year));
    $monthnames = explode(",", $plugin_tx['calendar']['monthnames_array']);

    if (stristr($plugin_cf['calendar']['show_period_of_events'],"true")){
        $t .= "<p class='period_of_events'>"
           .  $plugin_tx['calendar']['text_announcing_overall_period']
           .  " <span>"
           .  $monthnames[$month - 1] . " "
           .  $year."</span> "
           .  $plugin_tx['calendar']['event_date_till_date']
           .  " <span>" . $monthnames[$display_end_month-1]
           .  " " . $display_end_year . "</span></p>\n";
    }

    /*for bugtracking
    $t .="<p><b>Variables:</b> month: ".$month_start.", year: ".$year_start.", end_month: ".$end_month_start.", past_month: ".$past_month_start.
    ", Config show number of previous months: ".$plugin_cf['calendar']['show_number_of_previous_months'].
    ", Config show number of future month: ".$plugin_cf['calendar']['show_number_of_future_months'].
    ", display_end_month: ".$display_end_month.", display_end_year: ".$display_end_year.".</p>";*/

    $t .="<table border='0' width='100%'>\n";

    // the number of tablecolumns is calculated
    // starting with minimum number of columns (date + main entry)
    $tablecols = 2;
    // adding columns according to config settings
    if ($plugin_cf['calendar']['show_event_time']) $tablecols++;
    if ($plugin_cf['calendar']['show_event_location']) $tablecols++;
    if ($plugin_cf['calendar']['show_event_link']) $tablecols++;


    while($x<=$end_month){
        $textmonth = $monthnames[$month - 1];
        $today     = (isset($today))? $today : date("j", time());
        $today     = ($month == date("m",time()) && $year == date("Y",time())) ? $today : 32;

        $table=false;
        /*headline with month, year and subheadline is being generated*/
        if (in_array(($month.".".$year),$event_yearmonth_array))
        {
            $table=true;
        }
        if($table)
        {
            $t .="<tr>\n";
            $t .="<td class='event_monthyear' colspan='$tablecols'>$textmonth $year".tag('br')."</td>\n";
            $t .="</tr>\n";
            $t .="<tr class='event_heading_row'>\n";
            $t .="<td class='event_heading event_date'>".$plugin_tx['calendar']['event_date']."</td>\n";
            if (stristr("true",$plugin_cf['calendar']['show_event_time']))
            {
                $t .="<td class='event_heading event_time'>".$plugin_tx['calendar']['event_time']."</td>\n";
            }
            $t .="<td class='event_heading event_event'>".$plugin_tx['calendar']['event_event']."</td>\n";
            if (stristr("true",$plugin_cf['calendar']['show_event_location']))
            {
                $t .="<td class='event_heading event_location'>".$plugin_tx['calendar']['event_location']."</td>\n";
            }
            if (stristr("true",$plugin_cf['calendar']['show_event_link']))
            {
                $t .="<td class='event_heading event_link'>".$plugin_tx['calendar']['event_link_etc']."</td>\n";
            }
            $t .="</tr>\n";
        }

        asort($event_datetime_array);

        foreach($event_datetime_array as $keys=>$temp){

            //=============================================
            //here the case of birthday annoncements starts
            //=============================================
            if(trim($event_location_array[$keys])=='###' and $event_month_array[$keys] == $month )
            {
                $age = $year-$event_year_array[$keys];
                if ($age >= 0)
                {
                    if ($month < 10)
                    {
                        if (strlen($month)==1)
                        {
                        $month = '0'. $month;
                        }
                    }

                    //headline with month has to be generated in case there is no ordinary event
                    if (!$table)
                    {
                        $table=true;
                        $t .= "<tr>\n";
                        $t .= "<td class='event_monthyear' colspan='$tablecols'>$textmonth $year" . tag('br') . "</td>\n";
                        $t .= "</tr>\n";

                        $t .= "<tr class='event_heading_row'>\n";
                        $t .= "<td class='event_heading event_date'>" . $plugin_tx['calendar']['event_date'] . "</td>\n";
                        if (stristr("true",$plugin_cf['calendar']['show_event_time']))
                        {
                            $t .= "<td class='event_heading event_time'>" . $plugin_tx['calendar']['event_time'] . "</td>\n";
                        }
                        $t .= "<td class='event_heading event_event'>" . $plugin_tx['calendar']['event_event'] . "</td>\n";
                        if (stristr("true",$plugin_cf['calendar']['show_event_location']))
                        {
                            $t .= "<td class='event_heading event_location'>" . $plugin_tx['calendar']['event_location'] . "</td>\n";
                        }
                        if (stristr("true",$plugin_cf['calendar']['show_event_link']))
                        {
                            $t .= "<td class='event_heading event_link'>" . $plugin_tx['calendar']['event_link_etc'] . "</td>\n";
                        }
                        $t .= "</tr>\n";
                        //end of headline for birthdays
                        }
                        $t .= "<tr class='birthday_data_row'>\n";
                        $t .= "<td class='event_data event_date'>$event_date_array[$keys]".dpSeperator()."$month".dpSeperator()."$year</td>\n";
                        if (stristr("true",$plugin_cf['calendar']['show_event_time'])){$t .="<td class='event_data event_time'>".""."</td>\n";}

                        if ($age >= 5)
                        {
                              $t .= "<td class='event_data event_event'>".$event_array[$keys]." $age ".$plugin_tx['calendar']['age_plural2_text']."</td>\n";
                        }
                        elseif ($age >= 2 and $age < 5)
                        {
                            $t .= "<td class='event_data event_event'>".$event_array[$keys]." $age ".$plugin_tx['calendar']['age_plural1_text']."</td>\n";
                        }
                        else
                        {
                            $t .= "<td class='event_data event_event'>".$event_array[$keys]." $age ".$plugin_tx['calendar']['age_singular_text']."</td>\n";
                        }


                        if (stristr("true",$plugin_cf['calendar']['show_event_location']))
                        {
                            $t .= "<td class='event_data event_location'>"
                               .  $plugin_tx['calendar']['birthday_text']."</td>\n";
                        }
                        if (stristr("true",$plugin_cf['calendar']['show_event_link']))
                        {
                            if(stristr($event_link_array[$keys], 'ext:'))
                            {
                                $external_site = substr($event_link_array[$keys],4);
                                list($external_site,$external_text) = explode(",",$external_site);
                                if(!$external_text) $external_text=$external_site;
                                $t .= "<td class='event_data event_link'><a href='http://"
                                   .  $external_site."' target='_blank' title='"
                                   .  strip_tags($event_array[$keys]) . "'>$external_text</a></td>\n";
                            }
                            elseif(stristr($event_link_array[$keys], 'int:'))
                            {
                                $internal_page = substr($event_link_array[$keys],4);
                                list($internal_page,$internal_text) = explode(",",$internal_page);
                                if(!$internal_text)$internal_text=$internal_page;
                                $t .= "<td class='event_data event_link'><a href='?"
                                   .  $internal_page."' title='"
                                   .  strip_tags($event_array[$keys])."'>$internal_text</a></td>\n";
                            }
                            else
                            {
                                $t .= "<td class='event_data event_link'>";
                                if (substr($event_link_array[$keys],0,1)==',')
                                {
                                    $t .= substr(strip_tags($event_link_array[$keys]),1) . "</td>\n";
                                }
                                else
                                {
                                    $t .= strip_tags($event_link_array[$keys]) . "</td>\n";
                                }
                            }
                        }
                        $t .= "</tr>\n";
                    }
                }

            //==================
            // now normal events
            //==================
            if($event_year_array[$keys] == $year and $event_month_array[$keys] == $month)
            {
                 if ($month<10){if (strlen($month)==1){$month='0'.$month;}}
                 $t .= "<tr class='event_data_row'>\n";
                 //now

                 //date field
                 $t .= "<td class='event_data event_date'>$event_date_array[$keys]";
                 // if beginning and end dates are there, these are put one under the other
                 if ($event_end_date_array[$keys]) {
                    if (   $month!= $event_end_month_array[$keys]
                        || $year != $event_end_year_array[$keys]) $t .= dpSeperator().$month ;
                    if (   $year != $event_end_year_array[$keys]) $t .= dpSeperator().$year ;
                    if (   $year == $event_end_year_array[$keys] && dpSeperator() == '.') $t.= ".";
                    $t .= "&nbsp;".$plugin_tx['calendar']['event_date_till_date'] . tag('br');
                    $t .= $event_end_date_array[$keys].dpSeperator().$event_end_month_array[$keys].dpSeperator().$event_end_year_array[$keys];

                 } else $t .= dpSeperator()."$month".dpSeperator().$year;

                 $t .= "</td>\n";

                 //time field
                 if (stristr("true",$plugin_cf['calendar']['show_event_time'])){
                    $t .="<td class='event_data event_time'>".$event_time_array[$keys];
                    if ($event_end_time_array[$keys]) {
                        if (!$event_end_date_array[$keys]) {
                         $t .= " ".$plugin_tx['calendar']['event_time_till_time'];
                        }
                        $t .= tag('br').$event_end_time_array[$keys];
                    }
                    $t .="</td>\n";
                 }

                 //event field
                 $t .="<td class='event_data event_event'>".$event_array[$keys]."</td>\n";

                 //location field
                 if (stristr("true",$plugin_cf['calendar']['show_event_location'])){$t .="<td class='event_data event_location'>".$event_location_array[$keys]."</td>\n";}

                 //link field
                 if (stristr("true",$plugin_cf['calendar']['show_event_link']))
                 {
                     if(stristr($event_link_array[$keys], 'ext:'))
                     {
                         $external_site = substr($event_link_array[$keys],4);
                         list($external_site,$external_text) = explode(",",$external_site);
                         if(!$external_text)$external_text=$external_site;
                         $t .="<td class='event_data event_link'><a href='http://".$external_site."' target='_blank' title='".strip_tags($event_array[$keys])."'>$external_text</a></td>\n";
                     }
                     elseif(stristr($event_link_array[$keys], 'int:'))
                     {
                         $internal_page = substr($event_link_array[$keys],4);
                         list($internal_page,$internal_text) = explode(",",$internal_page);
                         if(!$internal_text)$internal_text=$internal_page;
                         $t .="<td class='event_data event_link'><a href='?".$internal_page."' title='".strip_tags($event_array[$keys])."'>$internal_text</a></td>\n";
                     }
                     else
                     {
                        $t .="<td class='event_data event_link'>";
                        if (substr($event_link_array[$keys],0,1)==',')
                        {
                            $t .= substr(strip_tags($event_link_array[$keys]),1)."</td>\n";
                        }
                        else
                        {
                            $t .= strip_tags($event_link_array[$keys])."</td>\n";
                        }
                     }
                 }
                 $t .="</tr>\n";
            }
        }
        $x++;
        if($month==12)
        {
            $year++;$month=1;
        }
        else
        {
            $month++;
        }
    }
    $t .="</table>\n";
    return $t;
}


// ****************************************************************
// *                  Next coming event as marquee                *
// ****************************************************************

function nextevent()
{
    global $plugin_cf,$plugin_tx,$datapath,$sl,$plugin;

    $plugin=basename(dirname(__FILE__),"/");

    $event_date_array = array();
    $event_array = array();
    $event_location_array = array();
    $event_stsl_array = array();

    $t = '';
    $remember_event = '';


    if ($plugin_cf['calendar']['same-event-calendar_for_all_languages']=="true") {
        $eventfile = $datapath."eventcalendar.txt";
    }else {
        $eventfile = $datapath."eventcalendar_".$sl.".txt";
    }

    if(is_file($eventfile)){
    $fp = fopen($eventfile, "r");


    while (!feof($fp)) {
         $line = fgets($fp, 4096);
         $txt = "";
         list($eventdates,$event,$location,,$eventtime) = explode( ";", $line);
         list($event_date_start,$event_end_date,$event_end_time) = explode(",",$eventdates);

         if($event_end_date){

              $txt = $plugin_tx['calendar']['event_date_till_date']." ".tag('br'). $event_end_date ." ". $event_end_time;
              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);

              $txt = $plugin_tx['calendar']['event_event']." ".$plugin_tx['calendar']['event_start'].":".tag('br'). $event_date_start ." ". $eventtime;
              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_end_date);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $event_end_time"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);

         }else{

              list($event_date,$event_month,$event_year) = explode( dpSeperator(), $event_date_start);
              array_push($event_date_array, strtotime("$event_month/$event_date/$event_year $eventtime"));
              array_push($event_array, $event);
              array_push($event_stsl_array, $txt);
              array_push($event_location_array, $location);
             }

        }

    fclose($fp);
    }

    asort($event_date_array);

    $today=strtotime('now');

    foreach($event_date_array as $event_date){
        if($event_date>$today){
            if($remember_event == '')$remember_event=$event_date;
        }
    }
    if($remember_event > $today){
        $i = array_search($remember_event, $event_date_array);
        $t.= "<div class='nextevent_date'>".strftime($plugin_tx['calendar']['event_date_representation_in_next_event_marquee'],$event_date_array[$i]);
        if (strftime('%H:%M',$event_date_array[$i])!="00:00") {
            $t.= " — ".strftime('%H:%M',$event_date_array[$i]);
        }
        $t.= "</div>\n";
        $t.= "<marquee direction='up' scrolldelay=100 scrollamount='1'><div class='nextevent_event'>".$event_array[$i]."</div>\n";
        $t.= "<div class='nextevent_date'>$event_stsl_array[$i]</div>\n";
        $t.= "<div class='nextevent_location'>".$event_location_array[$i]."</div>\n</marquee>\n";
    }

// if no next event - as suggested by oldnema
    elseif ($plugin_tx['calendar']['notice_no_next_event_sceduled'])
    {
        $t.= "<div class='nextevent_date'>" . tag('br') . $plugin_tx['calendar']['notice_no_next_event_sceduled'] . "</div>";
    }

    return $t;
}


// ***
// Helper-function to parse the date-seperator, set in plugin-config,
// to build the config-string for the Date-Picker and to
// check for allowed seperators
// Allowed seperators:
// full-stop ".", forward slash "/" and minus/dash "-"
// ***

function dpSeperator($mode='') {

    global $plugin_cf;

    $sep = $plugin_cf['calendar']['date_delimiter'];
    $dp_sep = ''; //the string to configure the DatePicker

    if ($sep != '/' && $sep != '-') {
        $sep = '.'; //set default
    }

    switch ($sep) {
    case '.':
        $dp_sep = 'dt';
        break;
    case '/':
        $dp_sep = 'sl';
        break;
    case '-':
        $dp_sep = 'ds';
        break;
    }

    if ($mode == 'dp') {
        return $dp_sep;
    }
    else {
        return $sep;
    }
}


// ****************************************************************
// *            Backend, editing the event file                   *
// ****************************************************************

//==========================================================
//makes the form for editing events wide, medium or narrow
//==========================================================
function EventForm($events,$editeventswidth)
{
    global $hjs,$plugin_cf,$plugin_tx,$pth,$datapath,$sl,$plugin,$tx;

    $hjs .= '<script type="text/javascript" src="'
         .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.js">{ "lang":"'.$sl.'" }</script>'."\n";
    $hjs .= tag('link rel="stylesheet" type="text/css" href="'
         .  $pth['folder']['plugins'] . $plugin . '/dp/datepicker.css"')."\n";

    $imageFolder = $pth['folder']['plugins'] . $plugin . "/images";

    $columns = 7;
    if ($editeventswidth=="narrow") {$columns = 6;}
    if ($editeventswidth=="wide") {$columns = 9;}
    $columns = $columns - 1;
    $tableclass="calendar_input_" . $editeventswidth;

    $o = "<form method='POST' action='$sn'>\n";
    $o .= "<input type='hidden' value='saveevents' name='action'>\n";
    $o .= "<table class='calendar_input $tableclass'>\n";
    $o .= "<tr>\n";
    $o .= "<td colspan='$columns'><input class='submit' type='submit' value='"
       .  ucfirst($tx['action']['save'])."' name='send'></td>\n";
    $o .= "<td style='text-align:right;width:16px;'><input type='image' src='"
       .  $imageFolder . "/add.png' style='width:16;height:16;' name='add[0]' value='add' alt='Add entry'>\n</td>\n";
    $o .= "</tr>\n";

    //========================
    //narrow width input table
    //========================
    if ($editeventswidth=="narrow") {
        if($plugin_cf['calendar']['show_event_time']=='true'){
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td class='calendar_input_datefield'>"
               .  $plugin_tx[$plugin]['event_start']   . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td class='calendar_input_datefield'>"
               .  $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
               .  "<td> </td>\n"
               .  "</tr>\n";
        } else {
            $o .= "<tr class='firstline_calendarinput'>\n" .
            "<td colspan='2'>" . $plugin_tx[$plugin]['event_start']   . " " . $plugin_tx[$plugin]['event_date'] . "</td>\n" .
            "<td colspan='2'>" . $plugin_tx[$plugin]['event_end']     . " " . $plugin_tx[$plugin]['event_date'] . "</td>\n" .
            "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n" .
            "<td></td>\n" .
            "</tr>\n";
        }
        $i = 0;
        foreach($events as $entry) {
              if($plugin_cf['calendar']['show_event_time']=='true')
              {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

                  $o .= "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n" ;

                  $o .= "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']  . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n" ;
                                                         //3
                  $o .= "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n" ;
              }
              else
              {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

                  $o .= tag('input type="hidden" value="'. $entry['starttime'] . '" name="starttime['.$i.']"') . "\n" ;

                  $o .= "<td style='width:0'></td>";

                  $o .= "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n" ;

                  $o .= tag('input type="hidden"  value="' . $entry['endtime'] . '" name="endtime['.$i.']"') ."\n" ;

                  $o .= "<td style='width:0'></td>";

              }

              $o .= "<td>" . tag('input class="calendar_input_event event_highlighting" type="normal"  value="'
                 .  $entry['event']     . '" name="event['.$i.']"') . "</td>\n";

                 $o .= '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';

            $o .= "<td>"
               .  tag('input type="image" src="'
               .  $imageFolder . '/delete.png" style="width:16;height:16" name="delete['
               .  $i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n";
            if ($plugin_cf['calendar']['show_event_location']=="true")
            {
                $o .= "<tr>\n"
                   .  "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx[$plugin]['event_location'] ."</td>\n"
                   .  "<td>" . tag('input type="normal"  class="calendar_input_event"  value="'
                   .  $entry['location']  . '" name="location['.$i.']"') . "</td>\n<td></td>\n</tr>\n";
            }
            else
            {
                $o .= tag('input type="hidden" value="'.$entry['location'].'" name="location['-$i.']"');
            }

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "</tr>\n<tr>\n"
                   .  "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx['calendar']['event_link'] . "</td>\n"
                   .  "<td>"
                   .  tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                   .  $entry['linkadr'] . '" name="linkadr['.$i.']"') . "</td>\n<td>&nbsp;</td>\n</tr>\n";

                $o .= "<td class='calendarinput_line2' colspan='4'>"
                   .  $plugin_tx['calendar']['event_link_txt'] . "</td>\n"
                   .  "<td>"
                   .  tag('input type="normal" class="calendar_input_event" colspan="2" value="'
                   .  $entry['linktxt'] . '" name="linktxt['.$i.']"') . "</td>\n<td></td>\n</tr>\n"
                   .  "<tr><td colspan = '6'>&nbsp;</td></tr>\n";
            }
            else
            {
                $o .= tag('input type="hidden" value="'
                   .  $entry['linkadr']  .'" name="linkadr['.$i.']"')
                   .  tag('input type="hidden" value="'.$entry['linktxt']  .'" name="linktxt['.$i.']"');
            }
            $i++;
        }

    //========================
    // wide width input table
    //========================
    }elseif ($editeventswidth=="wide") {

        if($plugin_cf['calendar']['show_event_time']=='true')
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']   . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";


        }
        else
        {

            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td colspan='2'>" . $plugin_tx[$plugin]['event_start']   . " "
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td colspan='2'>" . $plugin_tx[$plugin]['event_end']     . " "
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";
        }

        if ($plugin_cf['calendar']['show_event_location']=="true")
        {
            $o .= "<td>" . $plugin_tx[$plugin]['event_location'] ."</td>\n";
        }
        else
        {
            $o .= "<td style='width:0'></td>";
        }

        if ($plugin_cf['calendar']['show_event_link']=="true")
        {
            $o .= "<td>" . $plugin_tx[$plugin]['event_link'] .    "</td>\n"
               .  "<td>" . $plugin_tx['calendar']['event_link_txt'] . "</td>\n";
        }
        else
        {
            $o .= "<td style='width:0'></td><td style='width:0'></td>";
        }

        $o .= "<td></td>\n</tr>\n";

        $i = 0;
        foreach($events as $entry)
        {
            $o .= "<tr>\n"
               .  "<td class='calendar_input_datefield'>"
               .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
               .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n";

            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                $o .= "<td class='calendar_input_time'>"
                   .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                   .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n";
            }
            else
            {
                $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                   .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n";
            }

            $o .= "<td class='calendar_input_datefield'>"
               .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
               .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n";

            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                $o .= "<td class='calendar_input_time'>"
                   .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                   .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n";
            }
            else
            {
                $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                   .  $entry['endtime'] . '" name="endtime['.$i.']"') . "</td>\n";
            }

            $o .=  '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';

            $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
               .  $entry['event']  . '" name="event['.$i.']"') . "</td>\n";

            if ($plugin_cf['calendar']['show_event_location']=="true")
            {
                $o .=    "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   . $entry['location']  . '" name="location['.$i.']"') . "</td>\n";
            }
            else
            {
                    $o .= "<td style='width:0'>" . tag('input type="hidden" value="'
                       .  $entry['location'] . '" name="location['.$i.']"') . "</td>";

            }

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   .  $entry['linkadr']   . '" name="linkadr['.$i.']"') . "</td>\n";
                $o .= "<td>" . tag('input class="calendar_input_event" type="normal" value="'
                   .  $entry['linktxt']   . '" name="linktxt['.$i.']"') . "</td>\n";
            }
            else
            {
                    $o .= "<td style='width:0'>". tag('input type="hidden" value="'.$entry['linkadr']  .'" name="linkadr['.$i.']"') . "</td>"
                       .  "<td style='width:0'>". tag('input type="hidden" value="'.$entry['linktxt']  .'" name="linktxt['.$i.']"') . "</td>";

            }
            $o .= "<td>"
               .  tag('input type="image" src="'
               .  $imageFolder .'/delete.png" style="width:16;height:16" name="delete['.$i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n";
            $i++;
        }

     }
     else
     //==========================
     // medium width input table
     //==========================
     {
        if($plugin_cf['calendar']['show_event_time']=='true')
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']
               .  tag('br') . $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time']    . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_end']     . tag('br')
               .  $plugin_tx[$plugin]['event_date'] . "</td>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_time'] .    "</td>\n";

            if($plugin_cf['calendar']['show_event_location']=='true')
            {
                $o .=  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
                   .   "<td>" . $plugin_tx[$plugin]['event_location']. "</td>\n";
            }
            else
            {
                $o .=  "<td colspan='2'>"
                   .   $plugin_tx[$plugin]['event_event']
                   .   "</td>\n";
            }
            $o .= "<td> </td>\n</tr>\n";
        }
        else
        {
            $o .= "<tr class='firstline_calendarinput'>\n"
               .  "<td>" . $plugin_tx[$plugin]['event_start']
               .  " " . $plugin_tx[$plugin]['event_date'] . "</td>\n";

            $o .= "<td style='width:0'></td>";

            $o .= "<td>" . $plugin_tx[$plugin]['event_end']
               .  " " . $plugin_tx[$plugin]['event_date'] . "</td>\n";

            $o .= "<td style='width:0'></td>";

            if($plugin_cf['calendar']['show_event_location']=='true') {
                $o .=  "<td>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n"
                   .   "<td>" . $plugin_tx[$plugin]['event_location']. "</td>\n";
            }
            else
            {
                $o .=  "<td colspan='2'>" . $plugin_tx[$plugin]['event_event'] .   "</td>\n";
            }
            $o .= "<td> </td>\n</tr>\n";
        }
        $i = 0;
        foreach($events as $entry)
        {
            if($plugin_cf['calendar']['show_event_time']=='true')
            {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n"
                     .  "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') ."</td>\n"
                     .  "<td class='calendar_input_time'>"
                     .  tag('input type="normal" class="calendar_input_time" maxlength="5"  value="'
                     .  $entry['endtime']   . '" name="endtime['.$i.']"') . "</td>\n";
            }
            else
            {
                  $o .= "<tr>\n"
                     .  "<td class='calendar_input_datefield' >"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['datestart'] . '" name="datestart['.$i.']" id="datestart'.$i.'"') . "</td>\n"
                     .  "<td style='width:0;'>"
                     .  tag('input type="hidden" value="' . $entry['starttime'] . '" name="starttime['.$i.']"') . "</td>\n"
                     .  "<td class='calendar_input_datefield'>"
                     .  tag('input type="normal" class="calendar_input_date" maxlength="10" value="'
                     .  $entry['dateend']   . '" name="dateend['.$i.']" id="dateend'.$i.'"') . "</td>\n"
                     .  "<td style='width:0;'>"
                     .  tag('input type="hidden" value="' . $entry['endtime'] . '" name="endtime['.$i.']"') . "</td>\n";
            }

              $o .=  '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"datestart'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>' .


                    '<script type="text/javascript">
                    // <![CDATA[
                        var opts = {
                            formElements:{"dateend'.$i.'":"d-'.dpSeperator('dp').'-m-'.dpSeperator('dp').'-Y"},
                            showWeeks:true,
                            // Show a status bar and use the format "l-cc-sp-d-sp-F-sp-Y" (e.g. Friday, 25 September 2009)
                            statusFormat:"l-cc-sp-d-sp-F-sp-Y"
                        };
                    datePickerController.createDatePicker(opts);
                    // ]]>
                    </script>';


            if($plugin_cf['calendar']['show_event_location']=='true')
            {
               $o .= "<td>" . tag('input type="normal"  class="calendar_input_event event_highlighting" value="'
                  .  $entry['event']  . '" name="event['.$i.']"') . "</td>\n"
                  .  "<td>" . tag('input type="normal"  class="calendar_input_event" value="'
                  .  $entry['location']. '" name="location['.$i.']"') . "</td>\n";
            }
            else
            {
               $o .= "<td colspan='2'>" . tag('input type="normal"  class="calendar_input_event event_highlighting" value="'
                  .  $entry['event']  . '" name="event['.$i.']"') . "\n"
                  .  tag('input type="hidden" value="' .  $entry['location']. '" name="location['.$i.']"') . "</td>\n";
            }

            $o .= "<td style='text-align:right;'>"
               .  tag('input type="image" src="'.$imageFolder
               .  '/delete.png" style="width:16;height:16" name="delete['.$i.']" value="delete" alt="Delete Entry"') . "\n"
               .  "</td>\n</tr>\n" ;

            if ($plugin_cf['calendar']['show_event_link']=="true")
            {
                $o .= "<tr>\n"
                   . "<td class='calendarinput_line2' colspan='4'>".$plugin_tx['calendar']['event_link']." / "
                   . $plugin_tx['calendar']['event_link_txt'] . "</td>\n"
                   . "<td>" . tag('input type="normal" class="calendar_input_event" value="' . $entry['linkadr']
                   . '" name="linkadr['.$i.']"') . "</td>\n"
                   . "<td>" . tag('input type="normal" class="calendar_input_event" value="'
                   . $entry['linktxt']  . '" name="linktxt['.$i.']"') . "</td>\n"
                   . "<td>&nbsp;</td>\n</tr>\n";
            }
            else
            {
                $o .= "<input type='hidden' value='".$entry['linkadr']  ."' name='linkadr[$i]'>"
                   .  "<input type='hidden' value='".$entry['linktxt']  ."' name='linktxt[$i]'>" ;
            }
            $i++;
        }
    }
    $o .= "<tr>\n";
    $o .= "<td colspan=$columns><input class='submit' type='submit' value='" . ucfirst($tx['action']['save'])."' name='send'></td>\n";
    $o .= "<td><input type='image' src='$imageFolder/add.png' style='width:16;height:16;' name='add[0]' value='add' alt='Add entry'>\n</td>\n";
    $o .= "</tr>\n";
    $o .= "</table>\n";
    $o .= "</form>";
    return $o;
}


//function to edit the eventfile
function EditEvents($editeventswidth)
{
    global $plugin_cf,$plugin_tx,$pth,$sl,$plugin,$tx;
    if (!$editeventswidth) {$editeventswidth = $plugin_cf['calendar']['event-input_memberpages_narrow_medium_or_wide'];}
    $imageFolder = $pth['folder']['plugins'] . $plugin . "/images";
    $events = (new Calendar\EventDataService)->readEvents();

    if(isset($_POST['action']))
      $action = $_POST['action'];
    elseif(isset($_GET['action']))
      $action = $_GET['action'];
    else
      $action = "editevents";


    if ($action == "editevents" || $action == "plugin_text") {
        $o .= EventForm($events,$editeventswidth);
     } elseif($action == 'saveevents') {

          $delete      = isset($_POST['delete'])       ? $_POST['delete']       : '';
          $add         = isset($_POST['add'])          ? $_POST['add']          : '';
          $datestart   = isset($_POST['datestart'])    ? $_POST['datestart']    : '';
          $starttime   = isset($_POST['starttime'])    ? $_POST['starttime']    : '';
          $dateend     = isset($_POST['dateend'])      ? $_POST['dateend']      : '';
          $endtime     = isset($_POST['endtime'])      ? $_POST['endtime']      : '';
          $event       = isset($_POST['event'])        ? $_POST['event']        : '';
          $location    = isset($_POST['location'])     ? $_POST['location']     : '';
          $linkadr     = isset($_POST['linkadr'])      ? $_POST['linkadr']      : '';
          $linktxt     = isset($_POST['linktxt'])      ? $_POST['linktxt']      : '';

          foreach(array('datestart', 'starttime', 'dateend', 'endtime','event', 'location', 'linkadr', 'linktxt') as $var) {
              $$var = array_map('stsl', $$var);
          }

          $deleted = false;
          $added   = false;

          $newevent = array();
          foreach($event as $j => $i){
            if(!isset($delete[$j]) || $delete[$j] == '') {

              //Checking the date format. Some impossible dates can be given, but don't hurt.
              $pattern = '/[\d\d\|\?{1-2}|\-{1-2}]\\'.dpSeperator().'\d\d\\'.dpSeperator().'\d{4}$/';
              if (!preg_match($pattern,$datestart[$j])) $datestart[$j] = "";
              if (!preg_match($pattern,$dateend[$j])) $dateend[$j] = "";

              //Birthday should never have an enddate
              if ($location[$j]=="###") $dateend[$j] = '';

              $entry = array(
                'datestart'  => str_replace(';',' ',$datestart[$j]),
                'starttime'  => str_replace(';',' ',$starttime[$j]),
                'dateend'    => str_replace(';',' ',$dateend[$j]),
                'endtime'    => str_replace(';',' ',$endtime[$j]),
                'event'      => str_replace(';',' ',$event[$j]),
                'location'   => str_replace(';',' ',$location[$j]),
                'linkadr'    => str_replace(';',' ',$linkadr[$j]),
                'linktxt'    => str_replace(';',' ',$linktxt[$j]));
              $newevent[] = $entry;
            } else
              $deleted = true;
          }
          if($add <> '') {
            $entry = array(
              'datestart'   => date("d").dpSeperator().date("m").dpSeperator().date("Y"),
              'starttime'   => "",
              'dateend'     => "",
              'endtime'     => "",
              'event'       => $plugin_tx[$plugin]['event_event'],
              'location'    => "",
              'linkadr'     => "",
              'linktxt'     => "");
            $newevent[] = $entry;
            $added = true;
          }

          if(!$deleted && !$added) {
              // sorting new event inputs, idea of manu, forum-message
              usort($newevent,'dateSort');
              if(!(new Calendar\EventDataService)->writeEvents($newevent)) $o .= "<p><strong>".$plugin_tx['calendar']['eventfile_not_saved']."</strong></p>\n";
              else  $o .= "<p><strong>".$plugin_tx['calendar']['eventfile_saved']."</strong></p>\n";
          }

          $editeventstableclass="calendar_input";
          if ($editeventswidth=="wide") {$editeventstableclass="calendar_input_wide";}

          $o .= EventForm($newevent,$editeventswidth);


    }
    return $o;
}

     // function by manu
    function dateSort($a, $b){
       $pattern = '!(.*)\\'.dpSeperator().'(.*)\\'.dpSeperator().'(.*)!';
       $replace = '\3\2\1';
       $a_i = preg_replace($pattern,$replace,$a['datestart']).$a['starttime'];
       $b_i = preg_replace($pattern,$replace,$b['datestart']).$b['starttime'];
       if ($a_i == $b_i) return 0;
       return ($a_i < $b_i) ? -1 : 1;
    }

?>
