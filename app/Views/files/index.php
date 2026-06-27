<div class="section-header" style="margin-bottom:20px">
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap">
    <select name="project_id" class="form-control" style="width:200px;padding:7px 11px" onchange="this.form.submit()">
      <option value="">All Projects</option>
      <?php foreach ($projects as $p): ?>
      <option value="<?= $p['id'] ?>" <?= ($filters['project_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="client_id" class="form-control" style="width:180px;padding:7px 11px" onchange="this.form.submit()">
      <option value="">All Clients</option>
      <?php foreach ($clients as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ($filters['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['company_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="search" name="q" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search files…" class="form-control" style="width:200px;padding:7px 11px">
  </form>
  <button type="button" class="btn btn-primary" data-open="uploadModal"><i class="ti ti-upload"></i> Upload File</button>
</div>
<div class="table-card"><div class="table-wrap"><table>
  <thead><tr><th>File</th><th>Project</th><th>Client</th><th>Size</th><th>Uploaded</th><th>By</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($files as $f): ?>
    <tr>
      <td><div style="display:flex;align-items:center;gap:8px"><i class="ti <?= e(\App\Models\FileModel::iconClass($f['extension'] ?? '')) ?>" style="color:var(--primary)"></i><span style="font-weight:500"><?= e($f['original_name']) ?></span></div></td>
      <td><?= e($f['project_name'] ?? '—') ?></td>
      <td><?= e($f['client_name'] ?? '—') ?></td>
      <td><?= number_format(($f['file_size'] ?? 0) / 1024, 1) ?> KB</td>
      <td><?= format_date($f['created_at']) ?></td>
      <td><?= e($f['uploader_name'] ?? '—') ?></td>
      <td><div class="table-actions">
        <a href="<?= url('files/download/' . $f['id']) ?>" class="btn btn-ghost btn-sm"><i class="ti ti-download"></i></a>
        <button type="button" class="btn btn-ghost btn-sm crud-edit" data-entity="file" data-id="<?= (int)$f['id'] ?>" data-record="<?= e(json_encode($f)) ?>" title="Edit"><i class="ti ti-edit"></i></button>
        <button type="button" class="btn btn-ghost btn-sm delete-file" data-id="<?= $f['id'] ?>"><i class="ti ti-trash" style="color:var(--danger)"></i></button>
      </div></td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($files)): ?><tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted)">No files uploaded yet</td></tr><?php endif; ?>
  </tbody>
</table></div></div>

<div class="modal-overlay" id="uploadModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">Upload File</div><button type="button" class="icon-btn" data-close="uploadModal"><i class="ti ti-x"></i></button></div>
    <form id="uploadForm" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group" style="grid-column:1/-1"><label class="form-label">File *</label><input type="file" name="file" class="form-control" required accept=".ai,.psd,.cdr,.pdf,.png,.jpg,.jpeg,.zip,.docx,.mp4,.webp,.svg"></div>
          <div class="form-group"><label class="form-label">Project</label><select name="project_id" class="form-control"><option value="">None</option><?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Client</label><select name="client_id" class="form-control"><option value="">None</option><?php foreach ($clients as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['company_name']) ?></option><?php endforeach; ?></select></div>
        </div>
        <p style="font-size:12px;color:var(--text-muted);margin-top:10px">Allowed: AI, PSD, CDR, PDF, PNG, JPEG, ZIP, DOCX, MP4 (max <?= config('app.upload_max_mb') ?>MB)</p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" data-close="uploadModal">Cancel</button><button type="submit" class="btn btn-primary">Upload</button></div>
    </form>
  </div>
</div>
