<div class="calendar_monthyear">
<?php if ($this->hasPrevNextButtons):?>
  <a href="<?=$this->prevUrl()?>" rel="nofollow" title="<?=$this->text('prev_button_text')?>">&lt;&lt;</a>
<?php endif?>
  <?=$this->caption()?>
<?php if ($this->hasPrevNextButtons):?>
  <a href="<?=$this->nextUrl()?>" rel="nofollow" title="<?=$this->text('next_button_text')?>">&gt;&gt;</a>
<?php endif?>
</div>
<table class="calendar_main">
<?php foreach ($this->rows as $row):?>
  <tr>
<?php   foreach ($row as $cell):?>
    <td class="<?=$this->escape($cell->classname)?>">
<?php       if (isset($cell->href)):?>
      <a href="<?=$this->escape($cell->href)?>" title="<?=$this->escape($cell->title)?>">
<?php       endif?>
        <?=$this->escape($cell->content)?>
<?php       if (isset($cell->href)):?>
      </a>
<?php       endif?>
    </td>
<?php   endforeach?>
  </tr>
<?php endforeach?>
</table>
