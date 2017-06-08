<?php
/*
CMSimple - Calendar Plugin - 1.2.10 by svasti 03/2013, version 1.2 modified by Holger, version 1.1 by svasti 3/2011, version 0.9 Mod by Tory 15-01-2008 - Modified by Bob (cmsimple.fr), 02/2008.
*/

if ((!function_exists('sv')) || (strlen($sl) !== 2) || preg_match('/admin.php/i', sv('PHP_SELF')))die('Access denied');

if($calendar){

    $eventfile = $pth['folder']['plugins'].$plugin."/content/eventcalendar_".$sl.".txt";
    if(!is_file($eventfile)){$handle = fopen($eventfile, "w");
    fclose($handle);}

    $o.=print_plugin_admin('on');

    $calendar_credits = "<h2>Calendar plugin version 1.2.10 </h2>\n"
                      . '<p>1.2.10: Maintenance release 03/2013 fixing magic quotes problems, suggested by <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
                      . '1.2.9: Maintenance release 05/2012, on suggestion of <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
                      . '1.2.8: Security release 03/2012, on suggestion of  <a href="http://3-magi.net" target="_blank">cmb</a>' . tag('br')
                      . '1.2.1 - 1.2.7 (10/2011): Maintenance releases  by <a href="http://svasti.de" target="_blank">svasti</a>' . tag('br')
                      . '1.2 with functionality and security added by <a href="http://cmsimple.holgerirmler.de" target="_blank">Holger</a>' . tag('br')
                      . '1.1 (03/2011) with major changes by <a href="http://svasti.de" target="_blank">svasti</a>' . tag('br')
                      . '0.6 - 1.0 by Tory  (and in between mod 02/2008 by Bob (cmsimple.fr))' . tag('br')
                      . '0.1 - 0.5 (2005-2006) by Michael Svarrer</p>';

    if($admin<>'plugin_main'){$o.=plugin_admin_common($action,$admin,$plugin);}

    if($admin=='') {
        $o .= $calendar_credits;
    }

    if($admin=='plugin_main'){
    	$o.=$plugin_tx['calendar']['admin_text_start'];
        $o.=EditEvents($plugin_cf['calendar']['event-input_backend_narrow_medium_or_wide']);
    }
}
?>
