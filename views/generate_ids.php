<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $csrf_token
 * @var int $count
 */
?>

<form method="post" class="calendar_input">
  <p class="xh_info"><?=$this->plural("message_ids", $count)?></p>
<?if ($count):?>
  <p><?=$this->text("message_generate_ids")?></p>
  <p class="calendar_buttons">
    <button name="calendar_do"><?=$this->text("label_ids")?></button>
  </p>
<?endif?>
  <input type="hidden" name="calendar_token" value="<?=$this->esc($csrf_token)?>">
</form>
