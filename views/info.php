<h1>Calendar</h1>
<img src="<?=$this->logo()?>" class="calendar_logo" alt="<?=$this->text('alt_logo')?>">
<p>
    Version: <?=$this->version()?>
</p>
<p>
    Copyright © 2005-2006 Michael Svarrer<br>
    Copyright © 2007-2008 Tory<br>
    Copyright © 2008      Patrick Varlet<br>
    Copyright © 2011      Holger Irmler<br>
    Copyright © 2011-2013 Frank Ziesing<br>
    Copyright © 2017      Christoph M. Becker
</p>
<p class="calendar_license">
    Calendar_XH is free software: you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation, either version 3 of the License, or (at your option) any later
    version.
</p>
<p class="calendar_license">
    Calendar_XH is distributed in the hope that it will be useful, but <em>without any
    warranty</em>; without even the implied warranty of <em>merchantability</em>
    or <em>fitness for a particular purpose</em>. See the GNU General Public
    License for more details.
</p>
<p class="calendar_license">
    You should have received a copy of the GNU General Public License along with
    Calendar_XH. If not, see <a href="http://www.gnu.org/licenses/"
    target="_blank">http://www.gnu.org/licenses/</a>.
</p>
<div class="calendar_syscheck">
    <h2><?php echo $this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
    <p class="xh_<?php echo $this->escape($check->state)?>"><?php echo $this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
