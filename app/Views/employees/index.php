<div class="section-header" style="margin-bottom:20px">
  <div></div>
  <button class="btn btn-primary" type="button" data-open="employeeModal"><i class="ti ti-plus"></i> Add Employee</button>
</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
<?php
$colors = ['var(--primary)','#6D28D9','#16A34A','#F59E0B','#DC2626','#0891B2'];
foreach ($employees as $i => $emp):
  $parts = explode(' ', $emp['name']);
  $ini = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
  $color = $colors[$i % count($colors)];
  $skills = json_decode($emp['skills'] ?? '[]', true) ?: [];
?>
  <div class="chart-card" style="display:flex;gap:14px;align-items:flex-start">
    <div style="width:48px;height:48px;border-radius:12px;background:<?= $color ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0"><?= e($ini) ?></div>
    <div style="flex:1;min-width:0">
      <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start">
        <div style="font-weight:600;font-size:14px"><?= e($emp['name']) ?></div>
        <div class="table-actions">
          <?php $employeeRecord = $emp; $employeeRecord['skills'] = implode(', ', $skills); ?>
          <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="employee" data-id="<?= (int)$emp['id'] ?>" data-record="<?= e(json_encode($employeeRecord)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
          <button type="button" class="btn btn-ghost btn-sm crud-delete" data-entity="employee" data-id="<?= (int)$emp['id'] ?>" data-label="<?= e($emp['name']) ?>" title="Delete"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
        </div>
      </div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px"><?= e($emp['designation'] ?? '') ?></div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php foreach (array_slice($skills, 0, 3) as $sk): ?><span class="badge badge-blue"><?= e($sk) ?></span><?php endforeach; ?>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-secondary)"><i class="ti ti-briefcase"></i> <?= (int)($emp['active_projects']??0) ?> active projects</div>
    </div>
  </div>
<?php endforeach; ?>
</div>
