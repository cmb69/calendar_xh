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

<?if ($showHeading):?>
<p class="period_of_events"><?=$this->raw($heading)?></p>
<?endif?>
<?foreach ($events as $event):?>
<figure>
  <figcaption class="event_monthyear"><?=$this->esc($event->headline->month_year)?></figcaption>
  <ol class="calendar_eventlist">
<?  foreach ($event->rows as $row):?>
<?    if ($row->is_birthday):?>
<?      assert($row instanceof BirthdayRow)?>
    <li class="birthday_data_row">
      <p class="event_data">
        <span class="event_date"><?=$this->esc($row->date)?></span>
        <span class="event_time"></span>
      </p>
      <p class="event_data event_summary"><?=$this->esc($row->summary)?> <?=$this->plural('age', $row->age)?></p>
      <p class="event_data event_location"><?=$this->text('birthday_text')?></p>
      <p class="event_data event_link"><?=$this->raw($row->link)?></p>
    </li>
<?    else:?>
  <?      assert($row instanceof EventRow)?>
    <li class="event_data_row <?=$this->esc($row->past_event_class)?>">
      <p class="event_data">
        <span class="event_date"><?=$this->esc($row->date)?></span>
        <span class="event_time"><?=$this->esc($row->time)?></span>
      </p>
      <p class="event_data event_summary"><?=$this->esc($row->summary)?></p>
      <p class="event_data event_location"><?=$this->esc($row->location)?></p>
      <p class="event_data event_link"><?=$this->raw($row->link)?></p>
    </li>
<?    endif?>
<?  endforeach?>
<?endforeach?>
  </ol>
</figure>
