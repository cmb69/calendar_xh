<?php if (!isset($this)) {header("404 Not found"); exit;}?>

<script type="module" src="<?=$this->jsUrl()?>"></script>
<form method="get" class="calendar_overview">
  <input type="hidden" name="selected" value="<?=$this->selected()?>">
<?php if ($this->selected === 'calendar'):?>
  <input type="hidden" name="admin" value="plugin_main">
<?php endif?>
  <p>
    <button name="action" value="create"><?=$this->text('label_new')?></button>
    <button name="action" value="update"><?=$this->text('label_edit')?></button>
    <button name="action" value="delete"><?=$this->text('label_delete')?></button>
  </p>
  <table>
<?php foreach ($this->events as $id => $event):?>
    <tr>
      <td><?=$this->escape($event['start_date'])?></td>
      <td><?=$this->escape($event['end_date'])?></td>
      <td><?=$this->escape($event['summary'])?></td>
      <td><input type="radio" name="event_id" value="<?=$this->escape($id)?>"></td>
    </tr>
<?php endforeach?>
  </table>
</form>
