// ================================================
//  Vexogen ERP — Main JavaScript
// ================================================

// ---- Navigation ----
function navigate(page, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');

  const titles = {
    dashboard:  'Dashboard',
    clients:    'Clients',
    projects:   'Projects',
    tasks:      'Tasks',
    payments:   'Payments',
    invoices:   'Invoices',
    quotations: 'Quotations',
    expenses:   'Expenses',
    employees:  'Employees',
    calendar:   'Calendar',
    reports:    'Reports',
    settings:   'Settings'
  };

  document.getElementById('pageTitle').textContent   = titles[page] || page;
  document.getElementById('breadcrumb').textContent  = titles[page] || page;

  if (el) {
    document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
  }

  if (page === 'reports') setTimeout(initReportCharts, 100);
}

// ---- Modals ----
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(o =>
  o.addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('open');
  })
);

// ---- Notification panel ----
function toggleNotif() {
  document.getElementById('notifPanel').classList.toggle('open');
}

document.addEventListener('click', e => {
  if (!e.target.closest('.icon-btn') && !e.target.closest('#notifPanel')) {
    document.getElementById('notifPanel').classList.remove('open');
  }
});

// ---- Tab switching (Projects kanban/list) ----
function switchTab(el, showId) {
  el.closest('.main, .content').querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('kanban-view').style.display = showId === 'kanban-view' ? 'block' : 'none';
  document.getElementById('list-view').style.display   = showId === 'list-view'   ? 'block' : 'none';
}

// ---- Dashboard Charts (loaded on page load) ----
window.addEventListener('load', function () {
  const months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const revenue  = [220,280,310,285,340,380,320,410,360,390,420,380];
  const expenses = [110,130,145,128,155,168,142,182,160,175,190,168];

  // Revenue & Expenses bar chart
  const rCtx = document.getElementById('revenueChart');
  if (rCtx) new Chart(rCtx, {
    type: 'bar',
    data: {
      labels: months,
      datasets: [
        { label: 'Revenue',  data: revenue,  backgroundColor: '#0F62FE', borderRadius: 4, borderSkipped: false },
        { label: 'Expenses', data: expenses, backgroundColor: '#E2E8F0', borderRadius: 4, borderSkipped: false }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top', labels: { font: { family: 'Inter', size: 11 }, usePointStyle: true, pointStyleWidth: 8 } }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } },
        y: { grid: { color: '#F1F5F9' }, ticks: { font: { family: 'Inter', size: 11 }, callback: v => '₹' + v + 'K' } }
      }
    }
  });

  // Project status donut chart
  const sCtx = document.getElementById('statusChart');
  if (sCtx) new Chart(sCtx, {
    type: 'doughnut',
    data: {
      labels: ['Active','Completed','Review','Lead'],
      datasets: [{ data: [11,38,5,7], backgroundColor: ['#0F62FE','#16A34A','#F59E0B','#E2E8F0'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: {
      responsive: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw } }
      }
    }
  });
});

// ---- Reports Charts (lazy-loaded when Reports page is opened) ----
function initReportCharts() {
  const months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const revenue  = [220,280,310,285,340,380,320,410,360,390,420,380];
  const expenses = [110,130,145,128,155,168,142,182,160,175,190,168];

  // Monthly revenue vs expenses line chart
  const rc = document.getElementById('reportChart');
  if (rc && !rc.dataset.init) {
    rc.dataset.init = '1';
    new Chart(rc, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          { label: 'Revenue',  data: revenue,  borderColor: '#0F62FE', backgroundColor: 'rgba(15,98,254,0.06)',  fill: true, tension: 0.35, pointRadius: 3, borderWidth: 2 },
          { label: 'Expenses', data: expenses, borderColor: '#F59E0B', backgroundColor: 'rgba(245,158,11,0.06)', fill: true, tension: 0.35, pointRadius: 3, borderWidth: 2 }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top', labels: { font: { family: 'Inter', size: 11 }, usePointStyle: true } } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } },
          y: { grid: { color: '#F1F5F9' }, ticks: { font: { family: 'Inter', size: 11 }, callback: v => '₹' + v + 'K' } }
        }
      }
    });
  }

  // Service-wise revenue horizontal bar chart
  const sc = document.getElementById('serviceChart');
  if (sc && !sc.dataset.init) {
    sc.dataset.init = '1';
    new Chart(sc, {
      type: 'bar',
      data: {
        labels: ['Web Dev','Branding','SEO','Photography','Video','Packaging','Social'],
        datasets: [{
          data: [980,720,420,380,340,290,210],
          backgroundColor: ['#0F62FE','#6D28D9','#16A34A','#F59E0B','#DC2626','#0891B2','#D97706'],
          borderRadius: 5,
          borderSkipped: false
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: '#F1F5F9' }, ticks: { font: { family: 'Inter', size: 10 }, callback: v => '₹' + v + 'K' } },
          y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } }
        }
      }
    });
  }
}
