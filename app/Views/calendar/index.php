<?php
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int) date('t', $firstDay);
$startDow = (int) date('w', $firstDay);
$monthName = date('F Y', $firstDay);
$eventsByDay = [];
foreach ($events as $ev) {
    $d = (int) date('j', strtotime($ev['event_date']));
    $eventsByDay[$d][] = $ev;
}
$prevM = $month <= 1 ? 12 : $month - 1;
$prevY = $month <= 1 ? $year - 1 : $year;
$nextM = $month >= 12 ? 1 : $month + 1;
$nextY = $month >= 12 ? $year + 1 : $year;
?>
<div class="section-header" style="margin-bottom:20px">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="<?= url("calendar?year={$prevY}&month={$prevM}") ?>" class="icon-btn"><i class="ti ti-chevron-left"></i></a>
    <h2 style="font-size:16px;font-weight:600"><?= e($monthName) ?></h2>
    <a href="<?= url("calendar?year={$nextY}&month={$nextM}") ?>" class="icon-btn"><i class="ti ti-chevron-right"></i></a>
  </div>
  <button class="btn btn-primary" type="button" data-open="calendarEventModal"><i class="ti ti-plus"></i> Add Event</button>
</div>
<div class="cal-grid">
  <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?><div class="cal-day-head"><?= $d ?></div><?php endforeach; ?>
  <?php
  $prevMonthDays = (int) date('t', mktime(0,0,0,$prevM,1,$prevY));
  for ($i = 0; $i < $startDow; $i++):
    $num = $prevMonthDays - $startDow + $i + 1;
  ?><div class="cal-day other-month"><div class="cal-day-num"><?= $num ?></div></div><?php endfor; ?>
  <?php for ($d = 1; $d <= $daysInMonth; $d++):
    $isToday = ($d == (int)date('j') && $month == (int)date('n') && $year == (int)date('Y'));
  ?>
  <div class="cal-day<?= $isToday ? ' today' : '' ?>">
    <div class="cal-day-num"><?= $d ?></div>
    <?php foreach ($eventsByDay[$d] ?? [] as $ev): ?>
    <div class="cal-event <?= e($ev['color'] ?? 'blue') ?>" style="display:flex;align-items:center;gap:4px">
      <span style="flex:1;overflow:hidden;text-overflow:ellipsis"><?= e($ev['title']) ?></span>
      <button type="button" class="crud-edit" data-entity="calendar-event" data-id="<?= (int)$ev['id'] ?>" data-record="<?= e(json_encode($ev)) ?>" title="Edit" style="border:0;background:none;color:inherit"><i class="ti ti-edit"></i></button>
      <button type="button" class="crud-delete" data-entity="calendar-event" data-id="<?= (int)$ev['id'] ?>" data-label="<?= e($ev['title']) ?>" title="Delete" style="border:0;background:none;color:inherit"><i class="ti ti-trash"></i></button>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endfor; ?>
</div>

<div class="modal-overlay" id="calendarEventModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Add Calendar Event</div><button type="button" class="icon-btn" data-close="calendarEventModal"><i class="ti ti-x"></i></button></div>
    <form class="ajax-form" data-endpoint="/api/calendar-events" data-method="POST">
      <div class="modal-body"><div class="form-grid">
        <div class="form-group form-span-2"><label class="form-label">Title *</label><input class="form-control" name="title" required></div>
        <div class="form-group"><label class="form-label">Type</label><select class="form-control" name="event_type"><option value="meeting">Meeting</option><option value="deadline">Deadline</option><option value="payment_reminder">Payment Reminder</option><option value="other">Other</option></select></div>
        <div class="form-group"><label class="form-label">Date *</label><input class="form-control" type="date" name="event_date" required></div>
        <div class="form-group"><label class="form-label">Start Time</label><input class="form-control" type="time" name="start_time"></div>
        <div class="form-group"><label class="form-label">End Time</label><input class="form-control" type="time" name="end_time"></div>
        <div class="form-group"><label class="form-label">Color</label><select class="form-control" name="color"><option value="blue">Blue</option><option value="green">Green</option><option value="orange">Orange</option></select></div>
        <div class="form-group form-span-2"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      </div></div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="calendarEventModal">Cancel</button><button type="submit" class="btn btn-primary">Save Event</button></div>
    </form>
  </div>
</div>
