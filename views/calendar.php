<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<script type="module" src="<?=$jsUrl?>"></script>
<table class="calendar_main">
  <caption class="calendar_monthyear">
<?if ($hasPrevNextButtons):?>
    <a href="<?=$prevUrl?>" rel="nofollow" title="<?=$this->text('prev_button_title')?>"><?=$this->text('prev_button_text')?></a>
<?endif?>
    <?=$caption?>
<?if ($hasPrevNextButtons):?>
    <a href="<?=$nextUrl?>" rel="nofollow" title="<?=$this->text('next_button_title')?>"><?=$this->text('next_button_text')?></a>
<?endif?>
  </caption>
  <tr>
<?foreach ($headRow as $cell):?>
    <th class="<?=$cell['classname']?>"><?=$cell['content']?></th>
<?endforeach?>
  <tr>
<?foreach ($rows as $row):?>
  <tr>
<?  foreach ($row as $cell):?>
    <td class="<?=$cell['classname']?>">
<?    if (isset($cell['href'])):?>
      <a href="<?=$cell['href']?>" title="<?=$cell['title']?>">
<?    endif?>
        <?=$cell['content']?>
<?    if (isset($cell['href'])):?>
      </a>
<?    endif?>
    </td>
<?  endforeach?>
  </tr>
<?endforeach?>
</table>
