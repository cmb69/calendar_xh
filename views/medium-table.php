<?php if ($this->showEventTime):?>
<tr class="firstline_calendarinput">
    <td><?=$this->text('event_start')?><br><?=$this->text('event_date')?></td>
    <td><?=$this->text('event_time')?></td>
    <td><?=$this->text('event_end')?><br><?=$this->text('event_date')?></td>
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
    <td><?=$this->text('event_start')?> <?=$this->text('event_date')?></td>
    <td style="width: 0"></td>
    <td><?=$this->text('event_end')?> <?=$this->text('event_date')?></td>
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
        <input type="normal" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event['datestart'])?>" id="datestart<?=$this->escape($i)?>">
    </td>
    <td class="calendar_input_time">
        <input type="normal" class="calendar_input_time" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['starttime'])?>">
    </td>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event['dateend'])?>" id="dateend<?=$this->escape($i)?>">
    </td>
    <td class="calendar_input_time">
        <input type="normal" class="calendar_input_time" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['endtime'])?>">
    </td>
<?php   else:?>
<tr>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event['datestart'])?>" id="datestart<?=$this->escape($id)?>">
    </td>
    <td style="width: 0">
        <input type="hidden" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['starttime'])?>">
    </td>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event['dateend'])?>" id="dateend<?=$this->escape($i)?>">
    </td>
    <td style="width: 0">
        <input type="hidden" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['endtime'])?>">
    </td>
<?php   endif?>
    <?=$this->datePickerScripts[$i]?>
<?php   if ($this->showEventLocation):?>
    <td>
        <input type="normal" class="calendar_input_event event_highlighting" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event['event'])?>">
    </td>
    <td>
        <input type="normal" class="calendar_input_event" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event['location'])?>">
    </td>
<?php   else:?>
    <td colspan="2">
        <input type="normal" class="calendar_input_event event_highlighting" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event['event'])?>">
        <input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event['location'])?>">
    </td>
<?php   endif?>
    <td style="text-align: right">
        <input type="image" src="<?=$this->deleteIcon()?>" style="width: 16px; height: 16px" name="delete[<?=$this->escape($i)?>]" value="delete" alt="Delete Entry">
    </td>
</tr>
<?php   if ($this->showEventLink):?>
<tr>
    <td class="calendarinput_line2" colspan="4"><?=$this->text('event_link')?> / <?=$this->text('event_link_txt')?></td>
    <td>
        <input type="normal" class="calendar_input_event" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linkadr'])?>">
    </td>
    <td>
        <input type="normal" class="calendar_input_event" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linktxt'])?>">
    </td>
    <td>&nbsp;</td>
</tr>
<?php   else:?>
<input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linkadr'])?>">
<input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linktxt'])?>">
<?php   endif?>
<?php endforeach?>
