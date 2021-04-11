<div class="calendar_import">
  <h1>Calendar – <?=$this->text('label_import')?></h1>
  <form action="<?=$this->url?>" method="POST">
<?php foreach ($this->files as $file):?>
    <button name="calendar_ics" value="<?=$file?>"><?=$this->text('label_import_button', $file)?></button>
<?php endforeach?>
  </form>
</div>
