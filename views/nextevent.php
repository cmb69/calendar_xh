<div class="nextevent_date">
<?php if (isset($this->event)):?>
    <?=$this->date?>
</div>
<div class="calendar_marquee_outer">
    <div class="calendar_marquee">
        <div class="nextevent_event"><?=$this->event->event?></div>
        <div class="nextevent_date"><?=$this->event_text?></div>
        <div class="nextevent_location"><?=$this->event->location?></div>
    </div>
</div>
<?php else:?>
    <br><?=$this->text('notice_no_next_event')?>
</div>
<?php endif?>
