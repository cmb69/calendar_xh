<?php if ($this->showHeading):?>
<p class="period_of_events"><?=$this->text('text_announcing_overall_period')?> <?=$this->start()?> <?=$this->text('event_date_till_date')?> <?=$this->end()?></p>
<?php endif?>
<table border="0" width="100%">
<?php foreach ($this->monthEvents as $monthEvent):?>
    <?=$this->escape($monthEvent)?>
<?php endforeach?>
</table>
