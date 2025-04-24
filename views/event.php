<?php

use Plib\View;

if (!isset($this)) {header("404 Not found"); exit;}

/**
 * @var View $this
 * @var string $summary
 * @var string $date_time
 * @var string $location
 * @var string $description
 * @var array<mixed> $data
 */
?>

<article class="calendar_event">
  <h1><?=$this->esc($summary)?></h1>
  <p class="event_date_time"><?=$this->raw($date_time)?></p>
  <p class="event_location"><?=$this->esc($location)?></p>
  <div class="event_description"><?=$this->raw($description)?></div>
  <script type="application/ld+json"><?=$this->json($data)?></script>
</article>
