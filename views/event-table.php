<?php

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var string $selected
 * @var list<array{start_date:string,end_date:string,summary:string}> $events
 * @var string $jsUrl
 */
?>

<script type="module" src="<?=$jsUrl?>"></script>
<form method="get" class="calendar_overview">
  <input type="hidden" name="selected" value="<?=$selected?>">
<?if ($selected === 'calendar'):?>
  <input type="hidden" name="admin" value="plugin_main">
<?endif?>
  <p>
    <button name="action" value="create"><?=$this->text('label_new')?></button>
    <button name="action" value="update"><?=$this->text('label_edit')?></button>
    <button name="action" value="delete"><?=$this->text('label_delete')?></button>
  </p>
  <table>
<?foreach ($events as $id => $event):?>
    <tr>
      <td><?=$event['start_date']?></td>
      <td><?=$event['end_date']?></td>
      <td><?=$event['summary']?></td>
      <td><input type="radio" name="event_id" value="<?=$id?>"></td>
    </tr>
<?endforeach?>
  </table>
</form>
