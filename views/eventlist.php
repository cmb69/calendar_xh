<?php

use Calendar\Dto\BirthdayRow;
use Calendar\Dto\EventRow;
use Calendar\Dto\HeaderRow;
use Plib\View;

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var View $this
 * @var bool $showHeading
 * @var string $heading
 * @var list<object{headline:HeaderRow,rows:list<BirthdayRow|EventRow>}> $events
 */
?>

<table class="calendar_eventlist">
<?if ($showHeading):?>
  <caption class="period_of_events"><?=$this->raw($heading)?></caption>
<?endif?>
<?foreach ($events as $event):?>
  <tr>
    <th class="event_monthyear" colspan="<?=$event->headline->tablecols?>"><?=$this->esc($event->headline->month_year)?></th>
  </tr>
  <tr class="event_heading_row">
    <th class="event_heading event_date"><?=$this->text('event_date')?></th>
<?  if ($event->headline->show_time):?>
    <th class="event_heading event_time"><?=$this->text('event_time')?></th>
<?  endif?>
    <th class="event_heading event_summary"><?=$this->text('event_summary')?></th>
<?  if ($event->headline->show_location):?>
    <th class="event_heading event_location"><?=$this->text('event_location')?></th>
<?  endif?>
<?  if ($event->headline->show_link):?>
    <th class="event_heading event_link"><?=$this->text('event_link_etc')?></th>
<?  endif?>
  </tr>
<?  foreach ($event->rows as $row):?>
<?    if ($row->is_birthday):?>
<?      assert($row instanceof BirthdayRow)?>
  <tr class="birthday_data_row">
    <td class="event_data event_date"><?=$this->esc($row->date)?></td>
<?       if ($row->show_time):?>
    <td class="event_data event_time"></td>
<?      endif?>
    <td class="event_data event_summary"><?=$this->esc($row->summary)?> <?=$this->plural('age', $row->age)?></td>
<?      if ($row->show_location):?>
    <td class="event_data event_location"><?=$this->text('birthday_text')?></td>
<?      endif?>
<?      if ($row->show_link):?>
    <td class="event_data event_link"><?=$this->raw($row->link)?></td>
<?      endif?>
  </tr>
<?    else:?>
<?      assert($row instanceof EventRow)?>
  <tr class="event_data_row <?=$this->esc($row->past_event_class)?>">
    <td class="event_data event_date"><?=$this->esc($row->date)?></td>
<?      if ($row->show_time):?>
    <td class="event_data event_time"><?=$this->esc($row->time)?></td>
<?      endif?>
    <td class="event_data event_summary"><?=$this->esc($row->summary)?></td>
<?      if ($row->show_location):?>
    <td class="event_data event_location"><?=$this->esc($row->location)?></td>
<?      endif?>
<?      if ($row->show_link):?>
    <td class="event_data event_link"><?=$this->raw($row->link)?></td>
<?      endif?>
  </tr>
<?    endif?>
<?  endforeach?>
<?endforeach?>
</table>
