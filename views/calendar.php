<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var Plib\View $this
 * @var string $caption
 * @var bool $hasPrevNextButtons
 * @var string $prevUrl
 * @var string $nextUrl
 * @var string $prevId
 * @var string $nextId
 * @var list<array{classname:string,content:string,full_name:string}> $headRow
 * @var list<list<array{classname:string,content:string,id?:string,href?:string,title?:string}>> $rows
 * @var string $jsUrl
 */
?>

<script type="module" src="<?=$jsUrl?>"></script>
<table class="calendar_main" role="presentation">
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
    <th class="<?=$this->esc($cell['classname'])?>" scope="col">
      <abbr title="<?=$this->esc($cell['full_name'])?>"><?=$this->esc($cell['content'])?></abbr>
    </th>
<?endforeach?>
  <tr>
<?foreach ($rows as $row):?>
  <tr>
<?  foreach ($row as $cell):?>
    <td class="<?=$this->esc($cell['classname'])?>">
<?    if (isset($cell['href'], $cell['title'], $cell['id'])):?>
      <a href="<?=$this->esc($cell['href'])?>" aria-describedby="<?=$this->esc($cell['id'])?>")>
<?    endif?>
        <span><?=$this->esc($cell['content'])?></span>
<?    if (isset($cell['href'], $cell['title'], $cell['id'])):?>
        <span role="tooltip" id="<?=$this->esc($cell['id'])?>"><?=$this->raw($cell['title'])?></span>
      </a>
  <?  endif?>
  </td>
<?  endforeach?>
  </tr>
<?endforeach?>
</table>
