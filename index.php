<?php
/*
CMSimple - Calendar Plugin (based on v. 0.5 from 2006 by Michael Svarrer)
*/

if ((!function_exists('sv')) || (strlen($sl) !== 2) || preg_match('/calendar'.DIRECTORY_SEPARATOR.'index.php/i', sv('PHP_SELF')))die('Access denied');

if (!$plugin_cf['calendar']['filepath_data']){
    $datapath = $pth['folder']['plugins'].$plugin."/content/";
} else {
    $datapath = $plugin_cf['calendar']['filepath_data'];
}

/*function to display a calendar in the template or on a page*/
function calendar($year = '', $month = '', $eventpage = '')
{
    ob_start();
    (new Calendar\CalendarController($year, $month, $eventpage))->defaultAction();
    return ob_get_clean();
}

// ****************************************************************
// *                  Display of the event list                   *
// ****************************************************************

//function to display the list of events on a page
function events($month, $year, $end_month, $past_month)
{
    ob_start();
    (new Calendar\EventListController($month, $year, $end_month, $past_month))->defaultAction();
    return ob_get_clean();
}


// ****************************************************************
// *                  Next coming event as marquee                *
// ****************************************************************

function nextevent()
{
    ob_start();
    (new Calendar\NextEventController)->defaultAction();
    return ob_get_clean();
}




// ****************************************************************
// *            Backend, editing the event file                   *
// ****************************************************************



//function to edit the eventfile
function EditEvents($editeventswidth)
{
    ob_start();
    (new Calendar\EditEventsController($editeventswidth))->defaultAction();
    return ob_get_clean();
}

(new Calendar\Plugin)->run();
