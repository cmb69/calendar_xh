<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<table class="calendar_eventlist">
<?php if ($this->showHeading):?>
  <caption class="period_of_events"><?=$this->heading()?></caption>
<?php endif?>
<?php foreach ($this->monthEvents as $monthEvent):?>
  <tr>
    <th class="event_monthyear" colspan="<?=$this->escape($monthEvent['headline']['tablecols'])?>"><?=$this->escape($monthEvent['headline']['monthYear'])?></th>
  </tr>
  <tr class="event_heading_row">
    <th class="event_heading event_date"><?=$this->text('event_date')?></th>
<?php   if ($monthEvent['headline']['showTime']):?>
    <th class="event_heading event_time"><?=$this->text('event_time')?></th>
<?php   endif?>
    <th class="event_heading event_summary"><?=$this->text('event_summary')?></th>
<?php   if ($monthEvent['headline']['showLocation']):?>
    <th class="event_heading event_location"><?=$this->text('event_location')?></th>
<?php   endif?>
<?php   if ($monthEvent['headline']['showLink']):?>
    <th class="event_heading event_link"><?=$this->text('event_link_etc')?></th>
<?php   endif?>
  </tr>
<?php   foreach ($monthEvent['rows'] as $row):?>
<?php       if ($row['is_birthday']):?>
  <tr class="birthday_data_row">
    <td class="event_data event_date"><?=$this->escape($row['date'])?></td>
<?php           if ($row['showTime']):?>
    <td class="event_data event_time"></td>
<?php           endif?>
    <td class="event_data event_summary"><?=$this->escape($row['summary'])?> <?=$this->plural('age', $row['age'])?></td>
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
    <td class="event_data event_summary"><?=$this->escape($row['summary'])?></td>
<?php           if ($row['showLocation']):?>
    <td class="event_data event_location"><?=$this->escape($row['location'])?></td>
<?php           endif?>
<?php           if ($row['showLink']):?>
    <td class="event_data event_link"><?=$this->escape($row['link'])?></td>
<?php           endif?>
  </tr>
<?php       endif?>
<?php   endforeach?>
<?php endforeach?>
</table>
