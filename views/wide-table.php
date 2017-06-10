<?php if ($this->showEventTime):?>
<tr class="firstline_calendarinput">
    <td><?=$this->text('event_start')?><br><?=$this->text('event_date')?></td>
    <td><?=$this->text('event_time')?></td>
    <td><?=$this->text('event_end')?><br><?=$this->text('event_date')?></td>
    <td><?=$this->text('event_time')?></td>
    <td><?=$this->text('event_event')?></td>
<?php else:?>
<tr class="firstline_calendarinput">
    <td colspan="2"><?=$this->text('event_start')?> <?=$this->text('event_date')?></td>
    <td colspan="2"><?=$this->text('event_end')?> <?=$this->text('event_date')?></td>
    <td><?=$this->text('event_event')?></td>
<?php endif?>
<?php if ($this->showEventLocation):?>
    <td><?=$this->text('event_location')?></td>
<?php else:?>
    <td style="width: 0"></td>
<?php endif?>
<?php if ($this->showEventLink):?>
    <td><?=$this->text('event_link')?></td>
    <td><?=$this->text('event_link_txt')?></td>
<?php else:?>
    <td style="width: 0"></td>
    <td style="width: 0"></td>
<?php endif?>
    <td></td>
</tr>
<?php foreach ($this->events as $i => $event):?>
<tr>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event->datestart)?>" id="datestart<?=$this->escape($i)?>">
    </td>
<?php   if ($this->showEventTime):?>
    <td class="calendar_input_time">
        <input type="normal" class="calendar_input_time" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
    </td>
<?php   else:?>
    <td style="width: 0">
        <input type="hidden" value="<?=$this->escape($event->starttime)?>" name="starttime[<?=$this->escape($i)?>]">
    </td>
<?php   endif?>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event->dateend)?>" id="dateend<?=$this->escape($i)?>">
    </td>
<?php   if ($this->showEventTime):?>
    <td class="calendar_input_time">
        <input type="normal" class="calendar_input_time" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
    </td>
<?php   else:?>
    <td style="width: 0">
        <input type="hidden" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
    </td>
<?php   endif?>
    <?=$this->datePickerScripts[$i]?>
    <td>
        <input class="calendar_input_event" type="normal" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event->event)?>">
    </td>
<?php   if ($this->showEventLocation):?>
    <td>
        <input class="calendar_input_event" type="normal" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
    </td>
<?php   else:?>
    <td style="width: 0">
        <input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
    </td>
<?php   endif?>
<?php   if ($this->showEventLink):?>
    <td>
        <input class="calendar_input_event" type="normal" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
    </td>
    <td>
        <input class="calendar_input_event" type="normal" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
    </td>
<?php   else:?>
    <td style="width: 0">
        <input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
    </td>
    <td style="width: 0">
        <input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
    </td>
<?php   endif?>
    <td>
        <input type="image" src="<?=$this->deleteIcon()?>" style="width: 16px; height: 16px" name="delete[<?=$this->escape($i)?>]" value="delete" alt="Delete Entry">
    </td>
</tr>
<?php endforeach?>
