<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<table class="calendar_eventlist">
<?if ($showHeading):?>
  <caption class="period_of_events"><?=$heading?></caption>
<?endif?>
<?foreach ($monthEvents as $monthEvent):?>
  <tr>
    <th class="event_monthyear" colspan="<?=$monthEvent['headline']['tablecols']?>"><?=$monthEvent['headline']['monthYear']?></th>
  </tr>
  <tr class="event_heading_row">
    <th class="event_heading event_date"><?=$this->text('event_date')?></th>
<?  if ($monthEvent['headline']['showTime']):?>
    <th class="event_heading event_time"><?=$this->text('event_time')?></th>
<?  endif?>
    <th class="event_heading event_summary"><?=$this->text('event_summary')?></th>
<?  if ($monthEvent['headline']['showLocation']):?>
    <th class="event_heading event_location"><?=$this->text('event_location')?></th>
<?  endif?>
<?  if ($monthEvent['headline']['showLink']):?>
    <th class="event_heading event_link"><?=$this->text('event_link_etc')?></th>
<?  endif?>
  </tr>
<?  foreach ($monthEvent['rows'] as $row):?>
<?    if ($row['is_birthday']):?>
  <tr class="birthday_data_row">
    <td class="event_data event_date"><?=$row['date']?></td>
<?       if ($row['showTime']):?>
    <td class="event_data event_time"></td>
<?      endif?>
    <td class="event_data event_summary"><?=$row['summary']?> <?=$this->plural('age', $row['age'])?></td>
<?      if ($row['showLocation']):?>
    <td class="event_data event_location"><?=$this->text('birthday_text')?></td>
<?      endif?>
<?      if ($row['showLink']):?>
    <td class="event_data event_link"><?=$row['link']?></td>
<?      endif?>
  </tr>
<?    else:?>
  <tr class="event_data_row <?=$row['past_event_class']?>">
    <td class="event_data event_date"><?=$row['date']?></td>
<?      if ($row['showTime']):?>
    <td class="event_data event_time"><?=$row['time']?></td>
<?      endif?>
    <td class="event_data event_summary"><?=$row['summary']?></td>
<?      if ($row['showLocation']):?>
    <td class="event_data event_location"><?=$row['location']?></td>
<?      endif?>
<?      if ($row['showLink']):?>
    <td class="event_data event_link"><?=$row['link']?></td>
<?      endif?>
  </tr>
<?    endif?>
<?  endforeach?>
<?endforeach?>
</table>
