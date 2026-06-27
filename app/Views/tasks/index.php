<?php
$cols = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
$grouped = array_fill_keys(array_keys($cols), []);
foreach ($tasks as $t) { $grouped[$t['status']][] = $t; }
?>
<div class="section-header" style="margin-bottom:16px">
  <div class="section-title">Task Board</div>
        <button class="btn btn-primary" type="button" data-open="taskModal"><i class="ti ti-plus"></i> New Task</button>
</div>
<div class="kanban-board">
<?php foreach ($cols as $key => $label): ?>
  <div class="kanban-col">
    <div class="kanban-header"><span class="kanban-title"><?= e($label) ?></span><span class="kanban-count"><?= count($grouped[$key]) ?></span></div>
    <div class="kanban-body">
      <?php foreach ($grouped[$key] as $t): ?>
      <div class="kanban-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
          <div class="kanban-card-title"><?= e($t['title']) ?></div>
          <div class="table-actions">
            <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="task" data-id="<?= (int)$t['id'] ?>" data-record="<?= e(json_encode($t)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
            <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="task" data-id="<?= (int)$t['id'] ?>" data-label="<?= e($t['title']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
          </div>
        </div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:8px"><?= e($t['project_name'] ?? 'General') ?></div>
        <div class="kanban-card-meta">
          <span class="badge <?= status_badge_class($t['priority']) ?>" style="font-size:10.5px"><?= e(ucfirst($t['priority'])) ?></span>
          <span style="font-size:11px;color:var(--text-muted)"><?= format_date($t['due_date']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
