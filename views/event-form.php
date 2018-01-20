<form method="post" action="">
    <input type="hidden" name="action" value="saveevents">
    <div class="calendar_input">
        <div>
            <button name="send" value="<?=$this->saveLabel()?>" title="<?=$this->saveLabel()?>"><?=$this->saveLabel()?></button>
            <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus fa-fw"></span></button>
        </div>
<?php foreach ($this->events as $i => $event):?>
        <div>
            <div class="calendar_input_datefield">
                <?=$this->text('event_date_start')?><br>
                <input type="date" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event->datestart)?>" id="datestart<?=$this->escape($i)?>">
            </div>
<?php   if ($this->showEventTime):?>
            <div class="calendar_input_time">
                <?=$this->text('event_time')?><br>
                <input type="time" class="calendar_input_time" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
            </div>
<?php   else:?>
            <input type="hidden" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
<?php   endif?>
            <div class="calendar_input_datefield">
                <?=$this->text('event_date_end')?><br>
                <input type="date" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event->dateend)?>" id="dateend<?=$this->escape($i)?>">
            </div>
<?php   if ($this->showEventTime):?>
            <div class="calendar_input_time">
                <?=$this->text('event_time')?><br>
                <input type="time" class="calendar_input_time" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
            </div>
<?php   else:?>
            <input type="hidden" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
<?php   endif?>
            <div>
                <?=$this->text('event_event')?><br>
                <input class="calendar_input_event event_highlighting" type="text" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event->event)?>">
            </div>
<?php   if ($this->showEventLocation):?>
            <div>
                <?=$this->text('event_location')?><br>
                <input type="text" class="calendar_input_event" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
            </div>
<?php   else:?>
            <input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
<?php   endif?>
<?php   if ($this->showEventLink):?>
            <div>
                <?=$this->text('event_link')?><br>
                <input type="text" class="calendar_input_event" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
            </div>
            <div>
                <?=$this->text('event_link_txt')?><br>
                <input type="text" class="calendar_input_event" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
            </div>
<?php   else:?>
            <input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
            <input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
<?php   endif?>
            <div>
                &nbsp;<br>
                <button name="delete[<?=$this->escape($i)?>]" value="delete" title="<?=$this->text('label_delete_event')?>"><span class="fa fa-trash fa-fw"></span></button>
            </div>
        </div>
<?php endforeach?>
        <div>
            <button name="send" value="<?=$this->saveLabel()?>" title="<?=$this->saveLabel()?>"><?=$this->saveLabel()?></button>
            <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus fa-fw"></span></button>
        </div>
    </div>
</form>
