<?php if ($this->showHeading):?>
<p class="period_of_events"><?=$this->heading()?></p>
<?php endif?>
<table border="0" width="100%">
<?php foreach ($this->monthEvents as $monthEvent):?>
    <tr>
        <td class="event_monthyear" colspan="<?=$this->escape($monthEvent['headline']['tablecols'])?>"><?=$this->escape($monthEvent['headline']['monthYear'])?></td>
    </tr>
    <tr class="event_heading_row">
        <td class="event_heading event_date"><?=$this->text('event_date')?></td>
<?php   if ($monthEvent['headline']['showTime']):?>
        <td class="event_heading event_time"><?=$this->text('event_time')?></td>
<?php   endif?>
        <td class="event_heading event_summary"><?=$this->text('event_summary')?></td>
<?php   if ($monthEvent['headline']['showLocation']):?>
        <td class="event_heading event_location"><?=$this->text('event_location')?></td>
<?php   endif?>
<?php   if ($monthEvent['headline']['showLink']):?>
        <td class="event_heading event_link"><?=$this->text('event_link_etc')?></td>
<?php   endif?>
    </tr>
<?php   foreach ($monthEvent['rows'] as $row):?>
<?php       if ($row['is_birthday']):?>
    <tr class="birthday_data_row">
        <td class="event_data event_date"><?=$this->escape($row['date'])?></td>
<?php           if ($row['showTime']):?>
        <td class="event_data event_time"></td>
<?php           endif?>
        <td class="event_data event_summary"><?=$this->escape($row['event']->summary)?> <?=$this->plural('age', $row['age'])?></td>
<?php           if ($row['showLocation']):?>
        <td class="event_data event_location"><?=$this->text('birthday_text')?></td>
<?php           endif?>
<?php           if ($row['showLink']):?>
        <td class="event_data event_link"><?=$this->escape($row['link'])?></td>
<?php           endif?>
    </tr>
<?php       else:?>
    <tr class="event_data_row <?=$this->escape($row['past_event_class'])?>">
        <td class="event_data event_date"><?=$this->escape($row['date'])?></td>
<?php           if ($row['showTime']):?>
        <td class="event_data event_time"><?=$this->escape($row['time'])?></td>
<?php           endif?>
        <td class="event_data event_summary"><?=$this->escape($row['event']->summary)?></td>
<?php           if ($row['showLocation']):?>
        <td class="event_data event_location"><?=$this->escape($row['event']->location)?></td>
<?php           endif?>
<?php           if ($row['showLink']):?>
        <td class="event_data event_link"><?=$this->escape($row['link'])?></td>
<?php           endif?>
    </tr>
<?php       endif?>
<?php   endforeach?>
<?php endforeach?>
</table>
