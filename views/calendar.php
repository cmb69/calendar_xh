<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<script type="module" src="<?=$jsUrl?>"></script>
<table class="calendar_main">
  <caption class="calendar_monthyear">
  <?php if ($hasPrevNextButtons):?>
    <a href="<?=$prevUrl?>" rel="nofollow" title="<?=$this->text('prev_button_title')?>"><?=$this->text('prev_button_text')?></a>
  <?php endif?>
    <?=$caption?>
  <?php if ($hasPrevNextButtons):?>
    <a href="<?=$nextUrl?>" rel="nofollow" title="<?=$this->text('next_button_title')?>"><?=$this->text('next_button_text')?></a>
  <?php endif?>
  </caption>
  <tr>
<?php foreach ($headRow as $cell):?>
    <th class="<?=$cell['classname']?>"><?=$cell['content']?></th>
<?php endforeach?>
  <tr>
<?php foreach ($rows as $row):?>
  <tr>
<?php   foreach ($row as $cell):?>
    <td class="<?=$cell['classname']?>">
<?php       if (isset($cell['href'])):?>
      <a href="<?=$cell['href']?>" title="<?=$cell['title']?>">
<?php       endif?>
        <?=$cell['content']?>
<?php       if (isset($cell['href'])):?>
      </a>
<?php       endif?>
    </td>
<?php   endforeach?>
  </tr>
<?php endforeach?>
</table>
