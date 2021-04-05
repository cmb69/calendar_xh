<form method="get" class="calendar_overview">
  <input type="hidden" name="selected" value="<?=$this->selected()?>">
<?php if ($this->selected === 'calendar'):?>
  <input type="hidden" name="admin" value="plugin_main">
<?php endif?>
  <div>
    <button name="action" value="create"><?=$this->text('label_new')?></button>
    <button name="action" value="update"><?=$this->text('label_edit')?></button>
    <button name="action" value="delete"><?=$this->text('label_delete')?></button>
  </div>
  <table>
<?php foreach ($this->events as $id => $event):?>
    <tr>
      <td><?=$this->escape($event->getDateStart())?></td>
      <td><?=$this->escape($event->getDateEnd())?></td>
      <td><?=$this->escape($event->event)?></td>
      <td><input type="radio" name="event_id" value="<?=$this->escape($id)?>"></td>
    </tr>
<?php endforeach?>
  </table>
</form>
