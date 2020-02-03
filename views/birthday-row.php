<tr class="birthday_data_row">
    <td class="event_data event_date"><?=$this->date()?></td>
<?php if ($this->showTime):?>
    <td class="event_data event_time"></td>
<?php endif?>
    <td class="event_data event_event"><?=$this->escape($this->event->event)?> <?=$this->plural('age', $this->age)?></td>
<?php if ($this->showLocation):?>
    <td class="event_data event_location"><?=$this->text('birthday_text')?></td>
<?php endif?>
<?php if ($this->showLink):?>
    <?=$this->link()?>
<?php endif?>
</tr>
