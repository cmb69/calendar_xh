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
<div>
  <p class="event_monthyear"><?=$this->esc($event->headline->month_year)?></p>
  <ol class="calendar_eventlist">
<?  foreach ($event->rows as $row):?>
<?    if ($row->is_birthday):?>
<?      assert($row instanceof BirthdayRow)?>
    <li class="birthday_data_row" itemprop="event" itemscope itemtype="https://schema.org/Event">
      <p class="event_data">
        <meta itemprop="startDate" content="<?=$this->esc($row->start_date)?>">
        <meta itemprop="endDate" content="<?=$this->esc($row->end_date)?>">
        <span class="event_date"><?=$this->raw($row->date)?></span>
      </p>
      <p class="event_data event_summary" itemprop="name"><?=$this->esc($row->summary)?> <?=$this->plural('age', $row->age)?></p>
      <p class="event_data event_location"><?=$this->text('birthday_text')?></p>
      <meta itemprop="url" content="<?=$this->esc($row->url)?>">
      <div class="event_data event_link" itemprop="description"><?=$this->raw($row->link)?></div>
    </li>
<?    else:?>
  <?      assert($row instanceof EventRow)?>
    <li class="event_data_row <?=$this->esc($row->past_event_class)?>" itemprop="event" itemscope itemtype="https://schema.org/Event">
      <p class="event_data">
        <meta itemprop="startDate" content="<?=$this->esc($row->start_date)?>">
        <meta itemprop="endDate" content="<?=$this->esc($row->end_date)?>">
        <span><?=$this->raw($row->date_time)?></span>
      </p>
      <p class="event_data event_summary" itemprop="name"><?=$this->esc($row->summary)?></p>
      <p class="event_data event_location" itemprop="location"><?=$this->esc($row->location)?></p>
      <meta itemprop="url" content="<?=$this->esc($row->url)?>">
      <div class="event_data event_link" itemprop="description"><?=$this->raw($row->link)?></div>
    </li>
<?    endif?>
<?  endforeach?>
  </ol>
</div>
<?endforeach?>
