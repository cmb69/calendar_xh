<?php if ($this->showEventTime):?>
<tr class="firstline_calendarinput">
    <td class="calendar_input_datefield"><?=$this->text('event_start')?><br><?=$this->text('event_date')?></td>
    <td><?=$this->text('event_time')?></td>
    <td class="calendar_input_datefield"><?=$this->text('event_end')?><br><?=$this->text('event_date')?></td>
    <td><?=$this->text('event_time')?></td>
    <td><?=$this->text('event_event')?></td>
    <td> </td>
</tr>
<?php else:?>
<tr class="firstline_calendarinput">
    <td colspan="2"><?=$this->text('event_start')?> <?=$this->text('event_date')?></td>
    <td colspan="2"><?=$this->text('event_end')?> <?=$this->text('event_date')?></td>
    <td><?=$this->text('event_event')?></td>
    <td></td>
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
        <input type="normal" class="calendar_input_date" maxlength="10" value="<?=$this->escape($event['datestart'])?>" name="datestart[<?=$this->escape($i)?>]" id="datestart<?=$this->escape($i)?>">
    </td>
    <input type="hidden" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['starttime'])?>">
    <td style="width: 0"></td>
    <td class="calendar_input_datefield">
        <input type="normal" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event['dateend'])?>" id="dateend<?=$this->escape($i)?>">
    </td>
    <input type="hidden" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event['endtime'])?>">
    <td style="width: 0"></td>
<?php   endif?>
    <td>
        <input class="calendar_input_event event_highlighting" type="normal" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event['event'])?>">
    </td>
    <?=$this->escape($this->datePickerScripts[$i])?>
    <td>
        <input type="image" src="<?=$this->deleteIcon()?>" style="width: 16px; height: 16px" name="delete[<?=$this->escape($i)?>]" value="delete" alt="Delete Entry">
    </td>
</tr>
<?php   if ($this->showEventLocation):?>
<tr>
    <td class="calendarinput_line2" colspan="4"><?=$this->text('event_location')?></td>
    <td>
        <input type="normal" class="calendar_input_event" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event['location'])?>">
    </td>
    <td></td>
</tr>
<?php   else:?>
<input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event['location'])?>">
<?php   endif?>
<?php   if ($this->showEventLink):?>
</tr>
<tr>
    <td class="calendarinput_line2" colspan="4"><?=$this->text('event_link')?></td>
    <td>
        <input type="normal" class="calendar_input_event" colspan="2" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linkadr'])?>">
    </td>
    <td>&nbsp;</td>
</tr>
    <td class="calendarinput_line2" colspan="4"><?=$this->text('event_link_txt')?></td>
    <td>
        <input type="normal" class="calendar_input_event" colspan="2" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linktxt'])?>">
    </td>
    <td></td>
</tr>
<tr>
    <td colspan="6">&nbsp;</td>
</tr>
<?php   else:?>
<input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linkadr'])?>">
<input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event['linktxt'])?>">
<?php   endif?>
<?php endforeach?>
