<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var Plib\View $this
 * @var string $caption
 * @var bool $hasPrevNextButtons
 * @var string $prevUrl
 * @var string $nextUrl
 * @var list<array{classname:string,content:string}> $headRow
 * @var list<list<array{classname:string,content:string,href?:string,title?:string}>> $rows
 * @var string $jsUrl
 */
?>

<script type="module" src="<?=$jsUrl?>"></script>
<table class="calendar_main">
  <caption class="calendar_monthyear">
<?if ($hasPrevNextButtons):?>
    <a href="<?=$this->esc($prevUrl)?>" rel="nofollow" title="<?=$this->text('prev_button_title')?>"><?=$this->text('prev_button_text')?></a>
<?endif?>
    <?=$this->esc($caption)?>
<?if ($hasPrevNextButtons):?>
    <a href="<?=$this->esc($nextUrl)?>" rel="nofollow" title="<?=$this->text('next_button_title')?>"><?=$this->text('next_button_text')?></a>
<?endif?>
  </caption>
  <tr>
<?foreach ($headRow as $cell):?>
    <th class="<?=$this->esc($cell['classname'])?>"><?=$this->esc($cell['content'])?></th>
<?endforeach?>
  <tr>
<?foreach ($rows as $row):?>
  <tr>
<?  foreach ($row as $cell):?>
    <td class="<?=$this->esc($cell['classname'])?>">
<?    if (isset($cell['href'], $cell['title'])):?>
      <a href="<?=$this->esc($cell['href'])?>" title="<?=$this->raw($cell['title'])?>">
<?    endif?>
        <?=$this->esc($cell['content'])?>
<?    if (isset($cell['href'])):?>
      </a>
<?    endif?>
    </td>
<?  endforeach?>
  </tr>
<?endforeach?>
</table>
