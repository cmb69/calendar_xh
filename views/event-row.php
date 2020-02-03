<tr class="event_data_row<?=$this->event->past_event? ' past_event':''?>">
    <td class="event_data event_date"><?=$this->date()?></td>
<?php if ($this->showTime):?>
    <td class="event_data event_time"><?=$this->time()?></td>
<?php endif?>
    <td class="event_data event_event"><?=$this->escape($this->event->event)?></td>
<?php if ($this->showLocation):?>
    <td class="event_data event_location"><?=$this->escape($this->event->location)?></td>
<?php endif?>
<?php if ($this->showLink):?>
    <td class="event_data event_link"><?=$this->link()?></td>
<?php endif?>
</tr>
