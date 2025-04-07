<?php

use Calendar\Dto\BirthdayRow;
use Calendar\Dto\EventRow;
use Calendar\Dto\HeaderRow;
use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("404 Not found"); exit;}

/**
 * @var View $this
 * @var bool $showHeading
 * @var string $heading
 * @var list<object{headline:HeaderRow,rows:list<BirthdayRow|EventRow>}> $events
 */
?>

<div class="calendar_eventlist">
<?if ($showHeading):?>
  <div class="period_of_events"><?=$this->raw($heading)?></div>
<?endif?>
<?foreach ($events as $event):?>
  <div class="event_monthyear"><?=$this->esc($event->headline->month_year)?></div>
<?  foreach ($event->rows as $row):?>
<?    if ($row->is_birthday):?>
<?      assert($row instanceof BirthdayRow)?>
  <div class="birthday_data_row">
    <div class="event_data event_date"><?=$this->esc($row->date)?></div>
<?       if ($row->show_time):?>
    <div class="event_data event_time"></div>
<?      endif?>
    <div class="event_data event_summary"><?=$this->esc($row->summary)?> <?=$this->plural('age', $row->age)?></div>
<?      if ($row->show_location):?>
    <div class="event_data event_location"><?=$this->text('birthday_text')?></div>
<?      endif?>
<?      if ($row->show_link):?>
    <div class="event_data event_link"><?=$this->raw($row->link)?></div>
<?      endif?>
</div>
<?    else:?>
  <?      assert($row instanceof EventRow)?>
  <div class="event_data_row <?=$this->esc($row->past_event_class)?>">
    <div class="event_data event_date"><?=$this->esc($row->date)?></div>
<?      if ($row->show_time):?>
    <div class="event_data event_time"><?=$this->esc($row->time)?></div>
<?      endif?>
    <div class="event_data event_summary"><?=$this->esc($row->summary)?></div>
<?      if ($row->show_location):?>
    <div class="event_data event_location"><?=$this->esc($row->location)?></div>
<?      endif?>
<?      if ($row->show_link):?>
    <div class="event_data event_link"><?=$this->raw($row->link)?></div>
<?      endif?>
  </div>
<?    endif?>
<?  endforeach?>
<?endforeach?>
</div>
