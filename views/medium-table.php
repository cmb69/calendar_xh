<?php if ($this->showEventTime):?>
<tr class="firstline_calendarinput">
    <td><?=$this->text('event_date_start')?></td>
    <td><?=$this->text('event_time')?></td>
    <td><?=$this->text('event_date_end')?></td>
    <td><?=$this->text('event_time')?></td>
<?php   if ($this->showEventLocation):?>
    <td><?=$this->text('event_event')?></td>
    <td><?=$this->text('event_location')?></td>
<?php   else:?>
    <td colspan="2"><?=$this->text('event_event')?></td>
<?php   endif?>
    <td> </td>
</tr>
<?php else:?>
<tr class="firstline_calendarinput">
    <td><?=$this->text('event_date_start')?></td>
    <td style="width: 0"></td>
    <td><?=$this->text('event_date_end')?></td>
    <td style="width: 0"></td>
<?php   if ($this->showEventLocation):?>
    <td><?=$this->text('event_event')?></td>
    <td><?=$this->text('event_location')?></td>
<?php   else:?>
    <td colspan="2"><?=$this->text('event_event')?></td>
<?php   endif?>
    <td> </td>
</tr>
<?php endif?>
<?php foreach ($this->events as $i => $event):?>
<?php   if ($this->showEventTime):?>
<tr>
    <td class="calendar_input_datefield">
        <input type="date" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event->datestart)?>" id="datestart<?=$this->escape($i)?>">
    </td>
    <td class="calendar_input_time">
        <input type="time" class="calendar_input_time" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
    </td>
    <td class="calendar_input_datefield">
        <input type="date" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event->dateend)?>" id="dateend<?=$this->escape($i)?>">
    </td>
    <td class="calendar_input_time">
        <input type="time" class="calendar_input_time" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
    </td>
<?php   else:?>
<tr>
    <td class="calendar_input_datefield">
        <input type="date" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event->datestart)?>" id="datestart<?=$this->escape($id)?>">
    </td>
    <td style="width: 0">
        <input type="hidden" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
    </td>
    <td class="calendar_input_datefield">
        <input type="date" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event->dateend)?>" id="dateend<?=$this->escape($i)?>">
    </td>
    <td style="width: 0">
        <input type="hidden" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
    </td>
<?php   endif?>
<?php   if ($this->showEventLocation):?>
    <td>
        <input type="text" class="calendar_input_event event_highlighting" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event->event)?>">
    </td>
    <td>
        <input type="text" class="calendar_input_event" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
    </td>
<?php   else:?>
    <td colspan="2">
        <input type="text" class="calendar_input_event event_highlighting" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event->event)?>">
        <input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
    </td>
<?php   endif?>
    <td style="text-align: right">
        <button name="delete[<?=$this->escape($i)?>]" value="delete" title="<?=$this->text('label_delete_event')?>"><span class="fa fa-trash"></span></button>
    </td>
</tr>
<?php   if ($this->showEventLink):?>
<tr>
    <td class="calendarinput_line2" colspan="4"><?=$this->text('event_link')?> / <?=$this->text('event_link_txt')?></td>
    <td>
        <input type="text" class="calendar_input_event" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
    </td>
    <td>
        <input type="text" class="calendar_input_event" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
    </td>
    <td>&nbsp;</td>
</tr>
<?php   else:?>
<input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
<input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
<?php   endif?>
<?php endforeach?>
