<div class="nextevent_date">
<?php if (isset($this->event)):?>
    <?=$this->date?>
</div>
<marquee direction="up" scrolldelay="100" scrollamount="1">
    <div class="nextevent_event"><?=$this->event->event?></div>
    <div class="nextevent_date"><?=$this->event->text?></div>
    <div class="nextevent_location"><?=$this->event->location?></div>
</marquee>
<?php else:?>
    <br><?=$this->text('notice_no_next_event')?>
</div>
<?php endif?>
