<?php

use Calendar\Dto\BigCell;

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var Plib\View $this
 * @var string $caption
 * @var bool $hasPrevNextButtons
 * @var string $prevUrl
 * @var string $nextUrl
 * @var string $prevId
 * @var string $nextId
 * @var list<object{classname:string,content:string,full_name:string}> $headRow
 * @var list<list<BigCell>> $rows
 * @var string $jsUrl
 */
?>

<table class="calendar_main calendar_big" role="presentation">
  <caption class="calendar_monthyear">
<?if ($hasPrevNextButtons):?>
    <a href="<?=$this->esc($prevUrl)?>" aria-labelledby="<?=$this->esc($prevId)?>">
      <span><?=$this->text('prev_button_text')?></span>
      <span role="tooltip" id="<?=$this->esc($prevId)?>"><?=$this->text('prev_button_title')?></span>
    </a>
<?endif?>
    <span><?=$this->esc($caption)?></span>
<?if ($hasPrevNextButtons):?>
    <a href="<?=$this->esc($nextUrl)?>" aria-labelledby="<?=$this->esc($nextId)?>">
      <span><?=$this->text('next_button_text')?></span>
      <span role="tooltip" id="<?=$this->esc($nextId)?>"><?=$this->text('next_button_title')?></span>
    </a>
<?endif?>
  </caption>
  <tr>
<?foreach ($headRow as $cell):?>
    <th class="<?=$this->esc($cell->classname)?>" scope="col"><?=$this->esc($cell->full_name)?></th>
<?endforeach?>
  <tr>
<?foreach ($rows as $row):?>
  <tr>
<?  foreach ($row as $cell):?>
    <td class="<?=$this->esc($cell->classname)?>">
      <div>
        <span><?=$cell->day?></span>
<?    foreach ($cell->events as $event):?>
        <a href="<?=$this->esc($event->url)?>"><?=$this->esc($event->summary)?></a>
<?    endforeach?>
      </div>
    </td>
<?  endforeach?>
  </tr>
<?endforeach?>
</table>
