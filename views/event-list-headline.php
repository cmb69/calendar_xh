<tr>
    <td class="event_monthyear" colspan="<?=$this->tablecols()?>"><?=$this->textmonth()?> <?=$this->year()?><br></td>
</tr>
<tr class="event_heading_row">
    <td class="event_heading event_date"><?=$this->text('event_date')?></td>
<?php if ($this->showTime):?>
    <td class="event_heading event_time"><?=$this->text('event_time')?></td>
<?php endif?>
    <td class="event_heading event_event"><?=$this->text('event_event')?></td>
<?php if ($this->showLocation):?>
    <td class="event_heading event_location"><?=$this->text('event_location')?></td>
<?php endif?>
<?php if ($this->showLink):?>
    <td class="event_heading event_link"><?=$this->text('event_link_etc')?></td>
<?php endif?>
</tr>
