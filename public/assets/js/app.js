(function () {
  const base = window.APP?.baseUrl || '';
  const csrf = window.APP?.csrf || '';

  function api(path, method = 'GET', body = null) {
    const opts = {
      method,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
      },
      credentials: 'same-origin',
    };
    if (body) {
      if (body instanceof FormData) {
        if (!body.has('_csrf')) body.append('_csrf', csrf);
        opts.body = body;
      } else {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify({ ...body, _csrf: csrf });
      }
    }
    return fetch(base + path, opts).then(r => r.json());
  }

  function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'toast toast-' + type;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 3200);
  }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
  }

  function formatMoney(value) {
    return 'Rs ' + Number(value || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
  }

  function formatDate(value, fallback = '-') {
    if (!value) return fallback;
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? fallback : date.toLocaleDateString();
  }

  function statusBadge(status) {
    const map = {
      done: 'badge-green',
      completed: 'badge-green',
      delivered: 'badge-green',
      paid: 'badge-green',
      review: 'badge-orange',
      revision: 'badge-purple',
      in_progress: 'badge-blue',
      design: 'badge-blue',
      development: 'badge-blue',
      high: 'badge-red',
      urgent: 'badge-red',
      overdue: 'badge-red',
      todo: 'badge-gray',
    };
    return map[status] || 'badge-gray';
  }

  function labelize(value) {
    return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  function syncScrollLock() {
    const modalOpen = Boolean(document.querySelector('.modal-overlay.open'));
    const mobileSidebarOpen = document.body.classList.contains('sidebar-open') && window.innerWidth <= 700;
    document.body.classList.toggle('scroll-locked', modalOpen || mobileSidebarOpen);
  }

  const panels = ['companyPanel', 'invoicePanel', 'notifyPanel', 'backupPanel', 'usersPanel'];

  function showPanel(id) {
    panels.forEach(p => {
      const el = document.getElementById(p);
      if (el) el.style.display = p === id ? 'block' : 'none';
    });
  }

  // Modals
  document.querySelectorAll('[data-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.id === 'projectAddTaskBtn' && window.__PROJECTS_SELECTED_ID__) {
        const projectSelect = document.querySelector('#taskModal select[name="project_id"]');
        if (projectSelect) projectSelect.value = String(window.__PROJECTS_SELECTED_ID__);
      }
      if (window.__PROJECTS_CLIENT_ID__) {
        const modalId = btn.getAttribute('data-open');
        const modal = document.getElementById(modalId);
        const clientSelect = modal?.querySelector('select[name="client_id"]');
        if (clientSelect) clientSelect.value = String(btn.dataset.clientId || window.__PROJECTS_CLIENT_ID__);

        if (modalId === 'paymentModal') {
          const projectInput = modal.querySelector('[name="project_id"]');
          projectInput.value = btn.dataset.projectId || '';
        }

        if (modalId === 'projectEditModal') {
          const form = document.getElementById('projectEditForm');
          if (form && btn.dataset.projectId) {
            form.dataset.endpoint = `/api/projects/${btn.dataset.projectId}/update`;
            form.querySelector('[name="name"]').value = btn.dataset.name || '';
            form.querySelector('[name="category"]').value = btn.dataset.category || '';
            form.querySelector('[name="status"]').value = btn.dataset.status || '';
            form.querySelector('[name="priority"]').value = btn.dataset.priority || 'medium';
            form.querySelector('[name="assigned_employee_id"]').value = btn.dataset.assigned || '';
            form.querySelector('[name="expected_delivery"]').value = btn.dataset.delivery || '';
            form.querySelector('[name="completion_percentage"]').value = btn.dataset.progress || '0';
            form.querySelector('[name="estimated_cost"]').value = btn.dataset.cost || '0';
            form.querySelector('[name="selling_price"]').value = btn.dataset.price || '0';
            form.querySelector('[name="advance"]').value = btn.dataset.advance || '0';
            form.querySelector('[name="balance"]').value = btn.dataset.balance || '0';
            form.querySelector('[name="description"]').value = btn.dataset.description || '';
          }
        }
      }
      document.getElementById(btn.getAttribute('data-open'))?.classList.add('open');
      syncScrollLock();
    });
  });
  document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById(btn.getAttribute('data-close'))?.classList.remove('open');
      syncScrollLock();
    });
  });
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) {
        overlay.classList.remove('open');
        syncScrollLock();
      }
    });
  });

  const crudSchemas = {
    client: [
      ['company_name', 'Company Name', 'text', true],
      ['contact_person', 'Contact Person'], ['phone', 'Phone'], ['email', 'Email', 'email'],
      ['industry', 'Industry'], ['website', 'Website'], ['gst_number', 'GST Number'],
      ['status', 'Status', 'select', false, ['active', 'follow_up', 'lead', 'inactive']],
      ['address', 'Address', 'textarea'], ['notes', 'Notes', 'textarea'],
    ],
    task: [
      ['title', 'Task Title', 'text', true],
      ['project_id', 'Project', 'lookup', false, 'projects'],
      ['assigned_to', 'Assigned To', 'lookup', false, 'employees'],
      ['status', 'Status', 'select', false, ['todo', 'in_progress', 'review', 'done']],
      ['priority', 'Priority', 'select', false, ['low', 'medium', 'high', 'urgent']],
      ['due_date', 'Due Date', 'date'], ['description', 'Description', 'textarea'],
    ],
    payment: [
      ['client_id', 'Client', 'lookup', true, 'clients'],
      ['project_id', 'Project', 'lookup', false, 'projects'],
      ['invoice_id', 'Invoice', 'lookup', false, 'invoices'],
      ['amount', 'Amount', 'number', true],
      ['payment_stage', 'Stage', 'select', false, ['advance', '25', '50', '75', 'final', 'other']],
      ['payment_method', 'Method', 'select', false, ['upi', 'neft', 'rtgs', 'cash', 'cheque', 'card', 'other']],
      ['payment_date', 'Payment Date', 'date'], ['transaction_id', 'Transaction ID'],
      ['notes', 'Notes', 'textarea'],
    ],
    invoice: [
      ['invoice_date', 'Invoice Date', 'date', true], ['due_date', 'Due Date', 'date'],
      ['status', 'Status', 'select', false, ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled']],
      ['billing_address', 'Billing Address', 'textarea'],
      ['notes', 'Notes', 'textarea'],
    ],
    quotation: [
      ['subject', 'Subject', 'text', true], ['valid_until', 'Valid Until', 'date'],
      ['status', 'Status', 'select', false, ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted']],
      ['terms', 'Terms', 'textarea'], ['notes', 'Notes', 'textarea'],
    ],
    expense: [
      ['category', 'Category', 'select', false, ['office_rent', 'salary', 'internet', 'fuel', 'software', 'printing', 'equipment', 'photography', 'miscellaneous']],
      ['amount', 'Amount', 'number', true], ['expense_date', 'Date', 'date'],
      ['paid_via', 'Paid Via', 'select', false, ['bank_transfer', 'upi', 'cash', 'credit_card', 'auto_debit', 'cheque']],
      ['description', 'Description', 'textarea', true],
    ],
    employee: [
      ['name', 'Name', 'text', true], ['designation', 'Designation'], ['department', 'Department'],
      ['email', 'Email', 'email'], ['phone', 'Phone'], ['skills', 'Skills'],
      ['salary', 'Salary', 'number'], ['join_date', 'Join Date', 'date'],
      ['status', 'Status', 'select', false, ['active', 'inactive', 'on_leave']],
    ],
    file: [
      ['original_name', 'File Name', 'text', true],
      ['project_id', 'Project', 'lookup', false, 'projects'],
      ['client_id', 'Client', 'lookup', false, 'clients'],
    ],
    user: [
      ['name', 'Name', 'text', true], ['email', 'Email', 'email', true],
      ['role', 'Role', 'select', false, ['admin', 'manager', 'designer', 'developer', 'marketing', 'accounts', 'client']],
      ['is_active', 'Status', 'select', false, [{ value: '1', label: 'Active' }, { value: '0', label: 'Inactive' }]],
    ],
    'calendar-event': [
      ['title', 'Title', 'text', true],
      ['event_type', 'Type', 'select', false, ['deadline', 'meeting', 'payment_reminder', 'birthday', 'leave', 'photo_shoot', 'video_shoot', 'printing', 'other']],
      ['event_date', 'Date', 'date', true], ['start_time', 'Start Time', 'time'], ['end_time', 'End Time', 'time'],
      ['color', 'Color', 'select', false, ['blue', 'green', 'orange']],
      ['description', 'Description', 'textarea'],
    ],
  };

  function fieldMarkup(field, record) {
    const [name, label, type = 'text', required = false, options = []] = field;
    const value = record[name] ?? '';
    const requiredAttr = required ? ' required' : '';
    let control = '';
    if (type === 'textarea') {
      control = `<textarea class="form-control" name="${name}" rows="3"${requiredAttr}>${escapeHtml(value)}</textarea>`;
    } else if (type === 'select' || type === 'lookup') {
      const rows = type === 'lookup'
        ? (window.CRUD_LOOKUPS?.[options] || [])
        : options.map(item => typeof item === 'object' ? item : ({ value: item, label: labelize(item) }));
      control = `<select class="form-control" name="${name}"${requiredAttr}><option value="">None</option>${rows.map(item =>
        `<option value="${escapeHtml(item.value)}"${String(item.value) === String(value) ? ' selected' : ''}>${escapeHtml(item.label)}</option>`
      ).join('')}</select>`;
    } else {
      const step = type === 'number' ? ' step="0.01"' : '';
      control = `<input class="form-control" name="${name}" type="${type}" value="${escapeHtml(value)}"${step}${requiredAttr}>`;
    }
    return `<div class="form-group${type === 'textarea' ? ' form-span-2' : ''}"><label class="form-label">${escapeHtml(label)}</label>${control}</div>`;
  }

  document.querySelectorAll('.crud-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const entity = btn.dataset.entity;
      const id = btn.dataset.id;
      const record = JSON.parse(btn.dataset.record || '{}');
      const schema = crudSchemas[entity];
      if (!schema) return;
      document.getElementById('crudEditTitle').textContent = `Edit ${labelize(entity)}`;
      document.getElementById('crudEditFields').innerHTML = schema.map(field => fieldMarkup(field, record)).join('');
      const resource = entity === 'calendar-event' ? 'calendar-events' : `${entity}s`;
      document.getElementById('crudEditForm').dataset.endpoint = `/api/${resource}/${id}/update`;
      document.getElementById('crudEditModal').classList.add('open');
      syncScrollLock();
    });
  });

  document.querySelectorAll('.crud-delete').forEach(btn => {
    btn.addEventListener('click', async () => {
      const label = btn.dataset.label || 'this record';
      if (!confirm(`Delete ${label}? This action cannot be undone.`)) return;
      const resource = btn.dataset.entity === 'calendar-event' ? 'calendar-events' : `${btn.dataset.entity}s`;
      const res = await api(`/api/${resource}/${btn.dataset.id}`, 'DELETE', new FormData());
      if (res.success) {
        toast(res.message || 'Deleted successfully');
        setTimeout(() => location.reload(), 600);
      } else {
        toast(res.message || 'Delete failed', 'error');
      }
    });
  });

  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  const sidebar = document.querySelector('.sidebar');
  if (localStorage.getItem('vexogenSidebarCollapsed') === '1' && window.innerWidth > 900) {
    document.body.classList.add('sidebar-collapsed');
  }
  sidebarCollapseBtn?.addEventListener('click', () => {
    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('vexogenSidebarCollapsed', collapsed ? '1' : '0');
    sidebarCollapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    sidebarCollapseBtn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
    window.dispatchEvent(new Event('resize'));
  });
  const closeSidebar = () => {
    document.body.classList.remove('sidebar-open');
    syncScrollLock();
  };
  mobileMenuBtn?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
    syncScrollLock();
  });
  sidebarBackdrop?.addEventListener('click', closeSidebar);
  sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeSidebar));

  const paymentModal = document.getElementById('paymentModal');
  if (paymentModal) {
    const clientSelect = paymentModal.querySelector('[name="client_id"]');
    const projectSelect = paymentModal.querySelector('[name="project_id"]');
    const invoiceSelect = paymentModal.querySelector('[name="invoice_id"]');
    const amountInput = paymentModal.querySelector('[name="amount"]');
    const filterPaymentOptions = () => {
      const clientId = clientSelect?.value || '';
      [projectSelect, invoiceSelect].forEach(select => {
        if (!select) return;
        Array.from(select.options).forEach((option, index) => {
          if (index === 0) return;
          option.hidden = Boolean(clientId && option.dataset.client !== clientId);
        });
        if (select.selectedOptions[0]?.hidden) select.value = '';
      });
    };
    clientSelect?.addEventListener('change', filterPaymentOptions);
    invoiceSelect?.addEventListener('change', () => {
      const option = invoiceSelect.selectedOptions[0];
      if (!option?.value) return;
      if (option.dataset.client && clientSelect) clientSelect.value = option.dataset.client;
      filterPaymentOptions();
      if (option.dataset.project && projectSelect) projectSelect.value = option.dataset.project;
      if (option.dataset.pending && amountInput && !amountInput.value) amountInput.value = option.dataset.pending;
    });
  }

  // AJAX forms (supports multipart)
  document.querySelectorAll('.ajax-form').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const endpoint = form.dataset.endpoint;
      const method = form.dataset.method || 'POST';
      const fd = new FormData(form);
      const btn = form.querySelector('[type=submit]');
      if (btn) btn.disabled = true;
      try {
        const res = await api(endpoint, method, fd);
        if (res.success) {
          toast(res.message || 'Saved successfully');
          form.closest('.modal-overlay')?.classList.remove('open');
          syncScrollLock();
          setTimeout(() => location.reload(), 800);
        } else {
          toast(res.message || 'Something went wrong', 'error');
        }
      } catch {
        toast('Network error', 'error');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  });

  // Invoice line items
  document.getElementById('addInvoiceLine')?.addEventListener('click', () => {
    const wrap = document.getElementById('invoiceLineItems');
    const row = document.createElement('div');
    row.className = 'invoice-line form-grid';
    row.style.marginBottom = '8px';
    row.innerHTML = '<div class="form-group"><input class="form-control" name="item_service[]" placeholder="Service name" required></div><div class="form-group"><input class="form-control" name="item_qty[]" type="number" value="1" min="1" step="0.01"></div><div class="form-group"><input class="form-control" name="item_rate[]" type="number" placeholder="Rate" step="0.01" required></div>';
    wrap.insertBefore(row, document.getElementById('addInvoiceLine'));
  });

  // File upload
  document.getElementById('uploadForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await api('/api/files', 'POST', fd);
    if (res.success) {
      toast(res.message);
      document.getElementById('uploadModal')?.classList.remove('open');
      setTimeout(() => location.reload(), 800);
    } else toast(res.message || 'Upload failed', 'error');
  });

  document.querySelectorAll('.delete-file').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this file?')) return;
      const res = await api('/api/files/' + btn.dataset.id, 'DELETE');
      if (res.success) { toast(res.message); setTimeout(() => location.reload(), 600); }
      else toast(res.message || 'Failed', 'error');
    });
  });

  // Communication form
  document.getElementById('commForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await api('/api/communications', 'POST', fd);
    if (res.success) { toast(res.message); setTimeout(() => location.reload(), 600); }
    else toast(res.message || 'Failed', 'error');
  });

  // Document email / WhatsApp
  document.querySelectorAll('.doc-email').forEach(btn => {
    btn.addEventListener('click', async () => {
      const type = btn.dataset.type;
      const id = btn.dataset.id;
      const email = prompt('Send to email address (leave blank for client default):') ?? '';
      const fd = new FormData();
      if (email) fd.append('email', email);
      const res = await api(`/api/${type}s/${id}/email`, 'POST', fd);
      if (res.success) toast(res.message);
      else toast(res.message || 'Email failed', 'error');
    });
  });

  document.querySelectorAll('.doc-whatsapp').forEach(btn => {
    btn.addEventListener('click', async () => {
      const type = btn.dataset.type;
      const id = btn.dataset.id;
      const phone = prompt('WhatsApp number (leave blank for client default):') ?? '';
      const fd = new FormData();
      if (phone) fd.append('phone', phone);
      const res = await api(`/api/${type}s/${id}/whatsapp`, 'POST', fd);
      if (res.success && res.url) window.open(res.url, '_blank');
      else toast(res.message || 'Failed', 'error');
    });
  });

  // Backup
  document.getElementById('createBackupBtn')?.addEventListener('click', async () => {
    const res = await api('/api/backup', 'POST', new FormData());
    if (res.success) { toast(res.message); setTimeout(() => location.reload(), 800); }
    else toast(res.message || 'Backup failed', 'error');
  });

  document.getElementById('generateTokenBtn')?.addEventListener('click', async () => {
    const res = await api('/api/backup/token', 'POST', new FormData());
    if (res.success) {
      toast('Cron token generated');
      const box = document.getElementById('cronUrlBox');
      const text = document.getElementById('cronUrlText');
      if (box && text) { text.textContent = res.cron_url; box.style.display = 'block'; }
    } else toast(res.message || 'Failed', 'error');
  });

  // Project tabs
  document.querySelectorAll('#proj-tabs .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('#proj-tabs .tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const view = tab.dataset.view;
      document.getElementById('kanban-view').style.display = view === 'kanban-view' ? 'block' : 'none';
      document.getElementById('list-view').style.display = view === 'list-view' ? 'block' : 'none';
    });
  });

  // Projects: master-detail workspace
  async function loadProjectWorkspace(projectId) {
    if (!projectId) return;
    const res = await api('/api/projects/' + projectId, 'GET');
    if (!res.success) {
      toast(res.message || 'Failed to load project', 'error');
      return;
    }

    const statusEl = document.getElementById('projStatus');
    const assigneeEl = document.getElementById('projAssignee');
    const progressEl = document.getElementById('projProgress');
    const progressValEl = document.getElementById('projProgressVal');
    const deliveryEl = document.getElementById('projDelivery');
    const priorityEl = document.getElementById('projPriority');
    const tasksBody = document.getElementById('projectTasksBody');
    const financeBox = document.getElementById('projectFinance');
    const commentsBox = document.getElementById('projectComments');
    const documentsBox = document.getElementById('projectDocuments');
    const taskCountEl = document.getElementById('pmTaskCount');
    const taskSubEl = document.getElementById('pmTaskSub');
    const taskHealthEl = document.getElementById('pmTaskHealth');
    const pendingAmountEl = document.getElementById('pmPendingAmount');
    const financeSubEl = document.getElementById('pmFinanceSub');
    const fileCountEl = document.getElementById('pmFileCount');

    if (statusEl) statusEl.value = res.project.status;
    if (assigneeEl) assigneeEl.value = res.project.assigned_employee_id || '';
    if (progressEl) progressEl.value = res.project.completion_percentage || 0;
    if (progressValEl) progressValEl.textContent = (res.project.completion_percentage || 0) + '%';
    if (deliveryEl) deliveryEl.value = res.project.expected_delivery || '';
    if (priorityEl) priorityEl.value = res.project.priority || 'medium';

    const taskSummary = res.task_summary || {};
    const taskCount = (res.tasks || []).length;
    const doneCount = Number(taskSummary.done || 0);
    const overdueCount = Number(taskSummary.overdue || 0);
    if (taskCountEl) taskCountEl.textContent = String(taskCount);
    if (taskSubEl) taskSubEl.textContent = `${doneCount} done, ${Math.max(taskCount - doneCount, 0)} open`;
    if (taskHealthEl) {
      taskHealthEl.textContent = overdueCount > 0
        ? `${overdueCount} overdue task${overdueCount === 1 ? '' : 's'} need attention`
        : `${doneCount}/${taskCount} tasks complete`;
    }
    if (fileCountEl) fileCountEl.textContent = String((res.files || []).length);

    if (tasksBody) {
      const rows = (res.tasks || []).map(t => {
        const due = formatDate(t.due_date);
        const isOverdue = t.due_date && t.due_date < new Date().toISOString().slice(0, 10) && t.status !== 'done';
        const doneBtn = t.status !== 'done'
          ? `<button type="button" class="btn btn-ghost btn-sm task-done" data-id="${t.id}" title="Mark done"><i class="ti ti-check"></i></button>`
          : '';
        return `<tr>
          <td style="font-weight:600">${escapeHtml(t.title)}</td>
          <td>${escapeHtml(t.assignee_name || 'Unassigned')}</td>
          <td><span class="badge ${statusBadge(t.status)}">${escapeHtml(labelize(t.status))}</span></td>
          <td class="${isOverdue ? 'pm-danger-text' : ''}">${escapeHtml(due)}</td>
          <td style="text-align:right">${doneBtn}</td>
        </tr>`;
      }).join('');
      tasksBody.innerHTML = rows || `<tr><td colspan="5" style="padding:18px;color:var(--text-muted);text-align:center">No tasks yet</td></tr>`;
      tasksBody.querySelectorAll('.task-done').forEach(btn => {
        btn.addEventListener('click', async () => {
          const fd = new FormData();
          fd.append('status', 'done');
          const r = await api('/api/tasks/' + btn.dataset.id + '/status', 'POST', fd);
          if (r.success) loadProjectWorkspace(projectId);
          else toast(r.message || 'Failed', 'error');
        });
      });
    }

    if (financeBox) {
      const billed = Number(res.totals?.billed || 0);
      const received = Number(res.totals?.received || 0);
      const pending = Number(res.totals?.pending || 0);
      if (pendingAmountEl) pendingAmountEl.textContent = formatMoney(pending);
      if (financeSubEl) financeSubEl.textContent = `${formatMoney(received)} received`;
      financeBox.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div><div class="stat-label">Billed</div><div style="font-weight:700;font-size:16px">${formatMoney(billed)}</div></div>
          <div><div class="stat-label">Received</div><div style="font-weight:700;font-size:16px;color:var(--success)">${formatMoney(received)}</div></div>
          <div><div class="stat-label">Pending</div><div style="font-weight:700;font-size:16px;color:var(--danger)">${formatMoney(pending)}</div></div>
          <div><div class="stat-label">Invoices</div><div style="font-weight:700;font-size:16px">${(res.invoices||[]).length}</div></div>
        </div>
        <div style="margin-top:12px" class="pm-doc-list">
          ${(res.payments || []).slice(0, 3).map(p => `
            <div class="pm-doc-item">
              <div><strong>${formatMoney(p.amount)}</strong><span>${escapeHtml(labelize(p.payment_method))} ${escapeHtml(formatDate(p.payment_date))}</span></div>
              <span>${escapeHtml(p.transaction_id || '')}</span>
            </div>
          `).join('') || '<div style="color:var(--text-muted);font-size:12px">No payments recorded yet.</div>'}
        </div>`;
    }

    if (commentsBox) {
      const items = (res.comments || []).map(c => {
        const when = c.created_at ? new Date(c.created_at.replace(' ', 'T')).toLocaleString() : '';
        return `<div class="pm-timeline-item">
          <div class="pm-timeline-top">
            <div style="font-weight:600">${escapeHtml(c.user_name || 'System')}</div>
            <div style="font-size:11px;color:var(--text-muted)">${escapeHtml(when)}</div>
          </div>
          <div style="margin-top:4px;color:var(--text-secondary)">${escapeHtml(c.comment)}</div>
        </div>`;
      }).join('');
      commentsBox.innerHTML = items || `<div style="padding:12px 0;color:var(--text-muted)">No updates yet.</div>`;
    }

    if (documentsBox) {
      const files = (res.files || []).slice(0, 5).map(f => `
        <div class="pm-doc-item">
          <div><strong>${escapeHtml(f.original_name || f.filename)}</strong><span>${escapeHtml(formatDate(f.created_at))}</span></div>
          <span>${escapeHtml((f.extension || '').toUpperCase())}</span>
        </div>
      `).join('');
      const invoices = (res.invoices || []).slice(0, 4).map(inv => `
        <div class="pm-doc-item">
          <div><strong>${escapeHtml(inv.invoice_number)}</strong><span>${escapeHtml(formatDate(inv.invoice_date))} - ${escapeHtml(labelize(inv.status))}</span></div>
          <span>${formatMoney(inv.pending_amount)} due</span>
        </div>
      `).join('');
      documentsBox.innerHTML = `
        <div class="pm-doc-list">
          ${files || '<div style="color:var(--text-muted);font-size:12px">No files uploaded yet.</div>'}
        </div>
        <div class="pm-section-label" style="margin-top:14px">Invoices</div>
        <div class="pm-doc-list">
          ${invoices || '<div style="color:var(--text-muted);font-size:12px">No invoices generated yet.</div>'}
        </div>`;
    }
  }

  // Click project in left list
  document.querySelectorAll('.project-item').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.projectId;
      document.querySelectorAll('.project-item').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      await loadProjectWorkspace(id);
      window.__PROJECTS_SELECTED_ID__ = Number(id);
    });
  });

  // Controls (status/assignee/progress/etc)
  const projectControls = ['projStatus','projAssignee','projDelivery','projPriority'];
  projectControls.forEach(cid => {
    document.getElementById(cid)?.addEventListener('change', async () => {
      const pid = window.__PROJECTS_SELECTED_ID__;
      if (!pid) return;
      const fd = new FormData();
      fd.append('status', document.getElementById('projStatus')?.value || '');
      fd.append('assigned_employee_id', document.getElementById('projAssignee')?.value || '');
      fd.append('expected_delivery', document.getElementById('projDelivery')?.value || '');
      fd.append('priority', document.getElementById('projPriority')?.value || '');
      fd.append('completion_percentage', document.getElementById('projProgress')?.value || '0');
      const r = await api('/api/projects/' + pid + '/update', 'POST', fd);
      if (r.success) toast('Saved');
      else toast(r.message || 'Failed', 'error');
    });
  });

  document.getElementById('projProgress')?.addEventListener('input', () => {
    const v = document.getElementById('projProgress').value;
    const out = document.getElementById('projProgressVal');
    if (out) out.textContent = v + '%';
  });
  document.getElementById('projProgress')?.addEventListener('change', async () => {
    const pid = window.__PROJECTS_SELECTED_ID__;
    if (!pid) return;
    const fd = new FormData();
    fd.append('completion_percentage', document.getElementById('projProgress')?.value || '0');
    fd.append('status', document.getElementById('projStatus')?.value || '');
    const r = await api('/api/projects/' + pid + '/update', 'POST', fd);
    if (!r.success) toast(r.message || 'Failed', 'error');
  });

  document.getElementById('projectCompleteBtn')?.addEventListener('click', async () => {
    const pid = window.__PROJECTS_SELECTED_ID__;
    if (!pid) return;
    if (!confirm('Mark this project as completed?')) return;
    const r = await api('/api/projects/' + pid + '/complete', 'POST', new FormData());
    if (r.success) { toast(r.message); loadProjectWorkspace(pid); }
    else toast(r.message || 'Failed', 'error');
  });

  document.getElementById('projectCommentForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const pid = window.__PROJECTS_SELECTED_ID__;
    if (!pid) return;
    const val = document.getElementById('projectComment')?.value?.trim();
    if (!val) return;
    const fd = new FormData();
    fd.append('comment', val);
    const r = await api('/api/projects/' + pid + '/comment', 'POST', fd);
    if (r.success) {
      document.getElementById('projectComment').value = '';
      loadProjectWorkspace(pid);
    } else toast(r.message || 'Failed', 'error');
  });

  // Initial load for selected project
  if (window.__PROJECTS_SELECTED_ID__) {
    loadProjectWorkspace(window.__PROJECTS_SELECTED_ID__);
  }

  // Settings tabs
  document.querySelectorAll('#settingsTabs .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('#settingsTabs .tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      showPanel(tab.dataset.panel);
    });
  });

  // Convert quotation
  document.querySelectorAll('.convert-quote').forEach(btn => {
    btn.addEventListener('click', async () => {
      const res = await api('/api/quotations/' + btn.dataset.id + '/convert', 'POST', new FormData());
      if (res.success) { toast(res.message); setTimeout(() => location.reload(), 800); }
      else toast(res.message || 'Failed', 'error');
    });
  });

  // Global search
  const searchInput = document.getElementById('searchInput');
  const searchResults = document.getElementById('searchResults');
  let searchTimer;
  if (searchInput && searchResults) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      const q = searchInput.value.trim();
      if (q.length < 2) { searchResults.style.display = 'none'; return; }
      searchTimer = setTimeout(async () => {
        const res = await api('/api/search?q=' + encodeURIComponent(q));
        searchResults.innerHTML = !res.results?.length
          ? '<div class="search-empty">No results</div>'
          : res.results.map(r => {
              const href = r.type === 'client' ? base + '/clients/' + r.id
                : r.type === 'project' ? base + '/projects'
                : r.type === 'invoice' ? base + '/invoices?preview=' + r.id
                : r.type === 'file' ? base + '/files'
                : base + '/' + (r.type === 'employee' ? 'employees' : r.type + 's');
              return `<a href="${href}" class="search-item"><span class="search-type">${r.type}</span><strong>${r.title}</strong><small>${r.subtitle || ''}</small></a>`;
            }).join('');
        searchResults.style.display = 'block';
      }, 250);
    });
    document.addEventListener('click', e => {
      if (!e.target.closest('#globalSearch')) searchResults.style.display = 'none';
    });
  }

  // Notifications
  const notifBtn = document.getElementById('notifBtn');
  const notifPanel = document.getElementById('notifPanel');
  const notifList = document.getElementById('notifList');
  if (notifBtn && notifPanel) {
    notifBtn.addEventListener('click', async e => {
      e.stopPropagation();
      notifPanel.classList.toggle('open');
      if (notifPanel.classList.contains('open')) {
        const res = await api('/api/notifications');
        if (notifList) {
          notifList.innerHTML = (res.data || []).map(n =>
            `<div class="notif-item"><div class="notif-text">${n.title}</div><div class="notif-time">${n.message}</div></div>`
          ).join('') || '<div style="padding:16px;color:var(--text-muted);font-size:13px">No notifications</div>';
        }
      }
    });
    document.getElementById('markAllRead')?.addEventListener('click', async () => {
      await api('/api/notifications/read', 'POST', new FormData());
      document.querySelector('#notifBtn .notif-dot')?.remove();
      notifPanel.classList.remove('open');
    });
    document.addEventListener('click', e => {
      if (!e.target.closest('#notifBtn') && !e.target.closest('#notifPanel')) notifPanel.classList.remove('open');
    });
  }

  // Auto-populate invoice billing address on client selection
  document.querySelector('#invoiceForm select[name="client_id"]')?.addEventListener('change', (e) => {
    const selectedOption = e.target.options[e.target.selectedIndex];
    const address = selectedOption.getAttribute('data-address') || '';
    const textarea = document.getElementById('invoiceBillingAddress');
    if (textarea) textarea.value = address;
  });

  // Auto-calculate project balance from selling price and advance
  function bindProjectFinanceCalc(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    const priceInput = form.querySelector('[name="selling_price"]');
    const advanceInput = form.querySelector('[name="advance"]');
    const balanceInput = form.querySelector('[name="balance"]');
    if (!priceInput || !advanceInput || !balanceInput) return;

    const calc = () => {
      const price = parseFloat(priceInput.value) || 0;
      const advance = parseFloat(advanceInput.value) || 0;
      balanceInput.value = (price - advance).toFixed(2);
    };

    priceInput.addEventListener('input', calc);
    advanceInput.addEventListener('input', calc);
  }

  bindProjectFinanceCalc('#projectForm');
  bindProjectFinanceCalc('#projectEditForm');
})();
