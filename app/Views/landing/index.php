<?php
$dashboardUrl = url('dashboard');
$loginUrl = url('login');
$primaryUrl = $isLoggedIn ? $dashboardUrl : $loginUrl;
$primaryLabel = $isLoggedIn ? 'Open Dashboard' : 'Start Free Trial';
$secondaryLabel = $isLoggedIn ? 'View Reports' : 'Schedule Demo';
$secondaryUrl = $isLoggedIn ? url('reports') : $loginUrl;
$modules = [
    ['icon' => 'ti-users', 'title' => 'CRM', 'text' => 'Manage leads, clients, contacts, follow-ups, and full communication history.'],
    ['icon' => 'ti-cash', 'title' => 'Sales', 'text' => 'Track quotations, orders, pipelines, collections, and business performance.'],
    ['icon' => 'ti-receipt-tax', 'title' => 'Accounting', 'text' => 'Create GST-ready invoices, record payments, monitor dues, and control expenses.'],
    ['icon' => 'ti-box', 'title' => 'Inventory', 'text' => 'Stay ahead of stock movement, purchase planning, and item-level visibility.'],
    ['icon' => 'ti-user-check', 'title' => 'HR & Payroll', 'text' => 'Organize employees, roles, attendance context, and team productivity.'],
    ['icon' => 'ti-briefcase', 'title' => 'Project Management', 'text' => 'Plan work, assign tasks, monitor progress, and deliver on time.'],
    ['icon' => 'ti-chart-bar', 'title' => 'Reports & Analytics', 'text' => 'Turn daily operations into clean dashboards, reports, and decisions.'],
];
$features = [
    'Real-time Dashboard',
    'GST Billing',
    'Multi-user Access',
    'Cloud Based',
    'Mobile Friendly',
    'Role Management',
    'Data Security',
    'Automated Reports',
];
$plans = [
    ['name' => 'Starter', 'price' => '₹2,999', 'tag' => 'For growing teams', 'items' => ['CRM and Sales', 'GST Invoices', 'Basic Reports', '5 Users']],
    ['name' => 'Professional', 'price' => '₹7,999', 'tag' => 'Most popular', 'items' => ['All Core Modules', 'Advanced Analytics', 'Role Management', '25 Users'], 'featured' => true],
    ['name' => 'Enterprise', 'price' => 'Custom', 'tag' => 'For larger operations', 'items' => ['Custom Workflows', 'Priority Support', 'Dedicated Setup', 'Unlimited Scale']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Vexogen ERP') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="<?= asset('images/vexogen-logo.png') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --forest: #1D4ED8;
    --emerald: #38BDF8;
    --sky: #06B6D4;
    --charcoal: #111827;
    --muted: #64748B;
    --soft: #F8FAFC;
    --card: #FFFFFF;
    --border: #E5E7EB;
    --line: rgba(29, 78, 216, .13);
    --dark: #07111F;
    --gradient: linear-gradient(135deg, #1D4ED8 0%, #38BDF8 100%);
    --shadow: 0 24px 70px rgba(29, 78, 216, .14);
    --shadow-soft: 0 18px 50px rgba(17, 24, 39, .08);
  }
  html { scroll-behavior: smooth; scroll-padding-top: 86px; }
  body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: var(--charcoal); background: var(--soft); line-height: 1.6; overflow-x: hidden; }
  a { color: inherit; text-decoration: none; }
  button { font-family: inherit; }
  .site-shell { min-height: 100vh; background:
    radial-gradient(circle at 16% 9%, rgba(56,189,248,.16), transparent 30%),
    radial-gradient(circle at 88% 5%, rgba(29,78,216,.13), transparent 24%),
    var(--soft);
  }
  .container { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
  .nav { position: sticky; top: 0; z-index: 30; background: rgba(248,250,252,.78); border-bottom: 1px solid rgba(229,231,235,.74); backdrop-filter: blur(18px); }
  .nav-inner { height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 22px; }
  .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; }
  .brand img { width: 42px; height: 42px; border-radius: 16px; display: block; box-shadow: 0 12px 24px rgba(29,78,216,.18); }
  .brand strong { display: block; font-family: Poppins, sans-serif; font-size: 18px; line-height: 1.1; }
  .brand span { display: block; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
  .nav-links { display: flex; align-items: center; gap: 26px; color: #475569; font-size: 14px; font-weight: 700; }
  .nav-actions, .hero-actions, .cta-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
  .btn { min-height: 46px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 16px; padding: 0 18px; font-size: 14px; font-weight: 800; transition: transform .2s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease; cursor: pointer; }
  .btn-primary { color: #fff; background: var(--gradient); box-shadow: 0 14px 34px rgba(29,78,216,.24); }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 18px 42px rgba(29,78,216,.3); }
  .btn-outline { background: rgba(255,255,255,.7); border-color: var(--border); color: var(--charcoal); }
  .btn-outline:hover { transform: translateY(-2px); border-color: rgba(29,78,216,.28); box-shadow: var(--shadow-soft); }
  .hero { position: relative; isolation: isolate; overflow: hidden; padding: 88px 0 70px; color: #fff; background:
    radial-gradient(circle at 18% 18%, rgba(56,189,248,.34), transparent 28%),
    radial-gradient(circle at 82% 8%, rgba(6,182,212,.28), transparent 26%),
    linear-gradient(135deg, #07111F 0%, #123A8C 48%, #1D4ED8 100%);
  }
  .hero::before { content: ""; position: absolute; inset: 0; z-index: -1; background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 54px 54px; mask-image: linear-gradient(to bottom, rgba(0,0,0,.9), transparent 86%); }
  .hero::after { content: ""; position: absolute; left: 50%; bottom: -170px; z-index: -1; width: min(980px, 92vw); height: 330px; transform: translateX(-50%); border-radius: 50%; background: rgba(56,189,248,.24); filter: blur(70px); }
  .hero-grid { display: flex; flex-direction: column; align-items: center; gap: 42px; }
  .hero-copy { max-width: 900px; text-align: center; margin: 0 auto; }
  .eyebrow { display: inline-flex; align-items: center; gap: 8px; color: #E0F2FE; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22); border-radius: 999px; padding: 8px 13px; font-size: 12px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; backdrop-filter: blur(14px); }
  h1, h2, h3 { font-family: Poppins, Inter, sans-serif; letter-spacing: 0; }
  .hero h1 { margin: 22px auto 0; font-family: "Plus Jakarta Sans", Poppins, sans-serif; font-size: clamp(46px, 7vw, 54px); line-height: .96; font-weight: 800; max-width: 900px; }
  .hero-lede { margin: 24px auto 0; max-width: 690px; color: rgba(255,255,255,.78); font-size: 19px; }
  .hero-actions { justify-content: center; margin-top: 32px; }
  .hero .btn-outline { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.26); color: #fff; }
  .hero .btn-outline:hover { border-color: rgba(255,255,255,.48); background: rgba(255,255,255,.16); }
  .hero-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin: 34px auto 0; max-width: 670px; }
  .stat-chip { padding: 17px; border-radius: 20px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); box-shadow: 0 18px 50px rgba(0,0,0,.16); backdrop-filter: blur(16px); }
  .stat-chip strong { display: block; font-family: "Plus Jakarta Sans", sans-serif; font-size: 25px; line-height: 1; }
  .stat-chip span { display: block; margin-top: 8px; color: rgba(255,255,255,.72); font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
  .dashboard-wrap { position: relative; width: min(1040px, 100%); min-height: 0; margin: 0 auto; }
  .dashboard-card { position: relative; color: var(--charcoal); border: 1px solid rgba(255,255,255,.72); border-radius: 20px; background: rgba(255,255,255,.82); box-shadow: 0 34px 90px rgba(0,0,0,.28); backdrop-filter: blur(20px); overflow: hidden; }
  .dash-top { height: 58px; display: flex; align-items: center; justify-content: space-between; padding: 0 18px; border-bottom: 1px solid var(--border); background: rgba(255,255,255,.72); }
  .dash-dots { display: flex; gap: 7px; }
  .dash-dots span { width: 10px; height: 10px; border-radius: 50%; background: #CBD5E1; }
  .dash-dots span:nth-child(2) { background: var(--emerald); }
  .dash-dots span:nth-child(3) { background: var(--sky); }
  .dash-search { width: 190px; height: 34px; border-radius: 999px; background: #F1F5F9; border: 1px solid var(--border); }
  .dash-body { display: grid; grid-template-columns: 170px 1fr; min-height: 482px; }
  .dash-sidebar { padding: 18px 14px; background: #0B1730; color: rgba(255,255,255,.78); }
  .dash-logo { display: flex; align-items: center; gap: 9px; color: #fff; font-weight: 900; margin-bottom: 22px; }
  .dash-logo img { width: 30px; height: 30px; border-radius: 12px; }
  .dash-nav { display: grid; gap: 8px; }
  .dash-nav span { display: flex; align-items: center; gap: 8px; border-radius: 14px; padding: 10px; font-size: 12px; font-weight: 800; }
  .dash-nav span:first-child { background: rgba(56,189,248,.22); color: #fff; }
  .dash-main { padding: 18px; min-width: 0; }
  .dash-title-row { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 16px; }
  .dash-title-row h2 { font-size: 20px; }
  .dash-badge { color: var(--forest); background: rgba(56,189,248,.14); border-radius: 999px; padding: 7px 10px; font-size: 11px; font-weight: 900; }
  .metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 11px; margin-bottom: 13px; }
  .metric, .panel, .screen-card, .testimonial, .pricing-card, .module-card, .why-card, .faq details { border: 1px solid rgba(229,231,235,.82); border-radius: 20px; background: rgba(255,255,255,.78); box-shadow: var(--shadow-soft); backdrop-filter: blur(16px); }
  .metric { padding: 14px; }
  .metric span { color: var(--muted); font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
  .metric strong { display: block; margin-top: 6px; font-family: "Plus Jakarta Sans", sans-serif; font-size: 23px; }
  .dash-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 11px; }
  .panel { padding: 15px; }
  .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; font-weight: 900; }
  .bars { height: 150px; display: flex; align-items: end; gap: 10px; }
  .bars span { flex: 1; min-width: 9px; border-radius: 10px 10px 4px 4px; background: rgba(29,78,216,.15); animation: rise .9s ease both; }
  .bars span:nth-child(2), .bars span:nth-child(5), .bars span:nth-child(7) { background: var(--gradient); }
  .inventory-list { display: grid; gap: 11px; }
  .inventory-row { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; font-size: 12px; font-weight: 800; }
  .progress { grid-column: 1 / -1; height: 8px; border-radius: 999px; background: #E2E8F0; overflow: hidden; }
  .progress span { display: block; height: 100%; border-radius: inherit; background: var(--gradient); }
  .float-card { position: absolute; right: -12px; bottom: 18px; width: 220px; padding: 16px; border-radius: 20px; background: rgba(255,255,255,.83); border: 1px solid rgba(255,255,255,.88); box-shadow: 0 22px 48px rgba(29,78,216,.2); backdrop-filter: blur(18px); animation: floaty 4s ease-in-out infinite; }
  .float-card i { color: var(--sky); font-size: 22px; }
  .float-card strong { display: block; margin-top: 8px; font-size: 14px; }
  .float-card span { color: var(--muted); font-size: 12px; }
  .section { padding: 84px 0; }
  .section.alt { background: rgba(255,255,255,.48); border-block: 1px solid rgba(229,231,235,.7); }
  .section-head { max-width: 760px; margin-bottom: 34px; }
  .center { text-align: center; margin-inline: auto; }
  .section-kicker { color: var(--forest); font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; }
  .section-head h2 { margin-top: 10px; font-size: clamp(30px, 4vw, 48px); line-height: 1.08; }
  .section-head p { margin-top: 12px; color: var(--muted); font-size: 16px; }
  .logo-strip { overflow: hidden; border: 1px solid var(--border); border-radius: 20px; background: rgba(255,255,255,.7); box-shadow: var(--shadow-soft); }
  .logo-track { min-width: max-content; display: flex; gap: 18px; padding: 18px; animation: marquee 28s linear infinite; }
  .logo-pill { min-width: 170px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; border-radius: 16px; border: 1px solid var(--border); background: #fff; padding: 14px 18px; color: #334155; font-weight: 900; }
  .logo-pill i { color: var(--forest); }
  .module-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
  .module-card { min-height: 220px; padding: 23px; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
  .module-card:hover, .pricing-card:hover, .testimonial:hover, .why-card:hover { transform: translateY(-5px); border-color: rgba(56,189,248,.48); box-shadow: var(--shadow); }
  .module-icon, .why-icon { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 18px; background: rgba(56,189,248,.14); color: var(--forest); font-size: 24px; margin-bottom: 22px; }
  .module-card h3, .why-card h3 { font-size: 18px; }
  .module-card p, .why-card p, .testimonial p { margin-top: 9px; color: var(--muted); font-size: 14px; }
  .feature-cloud { display: grid; grid-template-columns: repeat(4, 1fr); gap: 13px; }
  .feature-pill { display: flex; align-items: center; gap: 10px; min-height: 58px; padding: 13px 15px; border-radius: 18px; background: #fff; border: 1px solid var(--border); box-shadow: var(--shadow-soft); font-weight: 800; }
  .feature-pill i { color: var(--emerald); font-size: 20px; }
  .why-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
  .why-card { padding: 24px; min-height: 210px; }
  .screens { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 22px; align-items: end; }
  .screen-card { padding: 18px; overflow: hidden; }
  .screen-desktop { aspect-ratio: 16 / 9; display: grid; grid-template-rows: auto 1fr; }
  .screen-mobile { width: min(100%, 300px); aspect-ratio: 9 / 17; margin-inline: auto; }
  .mini-top { height: 36px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 7px; margin: -18px -18px 16px; padding: 0 14px; background: #F8FAFC; }
  .mini-top span { width: 8px; height: 8px; border-radius: 50%; background: #CBD5E1; }
  .screen-layout { display: grid; grid-template-columns: .7fr 1.3fr; gap: 12px; height: 100%; }
  .screen-side, .screen-chart, .screen-list { border-radius: 16px; background: #F1F5F9; }
  .screen-chart { background: linear-gradient(135deg, rgba(29,78,216,.16), rgba(56,189,248,.18)); }
  .phone-ui { display: grid; gap: 12px; }
  .phone-pill, .phone-card { border-radius: 18px; background: #F1F5F9; min-height: 54px; }
  .phone-card:nth-child(2) { min-height: 130px; background: linear-gradient(135deg, rgba(29,78,216,.18), rgba(56,189,248,.18)); }
  .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .pricing-card { padding: 26px; position: relative; transition: transform .2s ease, box-shadow .2s ease; }
  .pricing-card.featured { border-color: rgba(56,189,248,.58); box-shadow: var(--shadow); }
  .plan-tag { color: var(--forest); font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; }
  .price { margin: 12px 0 18px; font-family: "Plus Jakarta Sans", sans-serif; font-size: 38px; font-weight: 800; }
  .pricing-card ul, .footer-links { list-style: none; }
  .pricing-card li { display: flex; gap: 9px; margin: 11px 0; color: var(--muted); font-weight: 600; }
  .pricing-card li i { color: var(--emerald); margin-top: 4px; }
  .pricing-card .btn { width: 100%; margin-top: 18px; }
  .testimonials { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .testimonial { padding: 24px; }
  .stars { color: #F59E0B; letter-spacing: 2px; }
  .person { display: flex; align-items: center; gap: 12px; margin-top: 20px; }
  .avatar { width: 42px; height: 42px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; color: #fff; background: var(--gradient); font-weight: 900; }
  .person strong { display: block; }
  .person span { color: var(--muted); font-size: 12px; }
  .faq { display: grid; gap: 12px; max-width: 860px; margin: 0 auto; }
  .faq details { padding: 18px 20px; }
  .faq summary { cursor: pointer; font-weight: 900; list-style: none; display: flex; justify-content: space-between; gap: 18px; }
  .faq summary::-webkit-details-marker { display: none; }
  .faq summary::after { content: "+"; color: var(--forest); font-size: 22px; line-height: 1; }
  .faq details[open] summary::after { content: "-"; }
  .faq p { margin-top: 12px; color: var(--muted); }
  .cta { padding: 84px 0; }
  .cta-panel { border-radius: 20px; padding: 58px; color: #fff; background:
    radial-gradient(circle at 80% 20%, rgba(59,130,246,.32), transparent 32%),
    var(--gradient);
    box-shadow: 0 26px 70px rgba(29,78,216,.28);
  }
  .cta-panel h2 { font-size: clamp(32px, 5vw, 54px); line-height: 1.05; max-width: 760px; }
  .cta-panel p { color: rgba(255,255,255,.78); margin-top: 14px; max-width: 640px; }
  .cta-actions { margin-top: 28px; }
  .cta-panel .btn-outline { background: rgba(255,255,255,.12); color: #fff; border-color: rgba(255,255,255,.36); }
  .footer { padding: 38px 0; border-top: 1px solid var(--border); background: rgba(255,255,255,.56); }
  .footer-grid { display: grid; grid-template-columns: 1.1fr .9fr auto; gap: 28px; align-items: start; }
  .footer p { color: var(--muted); margin-top: 10px; max-width: 360px; }
  .footer-links { display: flex; flex-wrap: wrap; gap: 14px 22px; color: #475569; font-weight: 800; }
  .social { display: flex; gap: 10px; }
  .social a { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; border: 1px solid var(--border); background: #fff; color: var(--forest); font-size: 20px; transition: transform .2s ease; }
  .social a:hover { transform: translateY(-3px); }
  .copyright { margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border); color: var(--muted); font-size: 13px; }
  @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
  @keyframes rise { from { transform: scaleY(.45); transform-origin: bottom; opacity: .45; } to { transform: scaleY(1); opacity: 1; } }
  @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
  @media (prefers-color-scheme: dark) {
    :root { --charcoal: #F8FAFC; --muted: #A7B4C4; --soft: #07111F; --card: #0D1B2F; --border: rgba(255,255,255,.12); }
    .site-shell { background: radial-gradient(circle at 15% 7%, rgba(56,189,248,.18), transparent 32%), radial-gradient(circle at 85% 4%, rgba(29,78,216,.18), transparent 25%), #07111F; }
    .nav, .section.alt, .footer { background: rgba(7,17,31,.76); }
    .btn-outline, .stat-chip, .dashboard-card, .dash-top, .metric, .panel, .module-card, .feature-pill, .why-card, .screen-card, .pricing-card, .testimonial, .faq details, .logo-strip, .logo-pill { background: rgba(13,27,47,.78); color: var(--charcoal); }
    .dash-main, .mini-top { background: #0A1628; }
    .dash-search, .screen-side, .screen-list, .phone-pill, .phone-card { background: rgba(255,255,255,.08); }
    .nav-links, .footer-links { color: #C8D3DF; }
  }
  @media (max-width: 1040px) {
    .nav-links { display: none; }
    .hero-grid, .screens { grid-template-columns: 1fr; }
    .dashboard-wrap { min-height: auto; }
    .module-grid { grid-template-columns: repeat(2, 1fr); }
    .feature-cloud, .why-grid { grid-template-columns: repeat(2, 1fr); }
    .footer-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 720px) {
    .container { width: min(100% - 28px, 1180px); }
    .nav-inner { min-height: 76px; height: auto; padding: 12px 0; flex-wrap: wrap; }
    .nav-actions { width: 100%; }
    .nav-actions .btn { flex: 1; padding-inline: 12px; }
    .hero { padding-top: 54px; }
    .hero h1 { font-size: 43px; }
    .hero-stats, .metric-grid, .dash-grid, .module-grid, .feature-cloud, .why-grid, .pricing-grid, .testimonials { grid-template-columns: 1fr; }
    .dash-body { grid-template-columns: 1fr; }
    .dash-sidebar, .dash-search, .float-card { display: none; }
    .screens { gap: 16px; }
    .cta-panel { padding: 34px 22px; }
    .footer-links { display: grid; grid-template-columns: repeat(2, 1fr); }
  }
</style>
</head>
<body>
<div class="site-shell">
  <header class="nav">
    <div class="container nav-inner">
      <a class="brand" href="<?= url() ?>" aria-label="Vexogen ERP home">
        <img src="<?= asset('images/vexogen-logo.png') ?>" alt="Vexogen">
        <span><strong>Vexogen ERP</strong><span>Business Management Platform</span></span>
      </a>
      <nav class="nav-links" aria-label="Landing page sections">
        <a href="#modules">Modules</a>
        <a href="#features">Features</a>
        <a href="#screenshots">Screenshots</a>
        <a href="#pricing">Pricing</a>
        <a href="#faq">FAQ</a>
      </nav>
      <div class="nav-actions">
        <a class="btn btn-outline" href="<?= e($loginUrl) ?>">Login</a>
        <a class="btn btn-primary" href="<?= e($primaryUrl) ?>"><?= e($primaryLabel) ?><i class="ti ti-arrow-right"></i></a>
      </div>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="eyebrow"><i class="ti ti-sparkles"></i> Premium ERP for modern teams</div>
          <h1>Manage Your Entire Business With One Powerful ERP</h1>
          <p class="hero-lede">Accounting, CRM, Inventory, Sales, HR, Projects, and Analytics - everything your business needs in a single platform.</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e($primaryUrl) ?>"><?= e($primaryLabel) ?><i class="ti ti-arrow-right"></i></a>
            <a class="btn btn-outline" href="<?= e($secondaryUrl) ?>"><?= e($secondaryLabel) ?><i class="ti ti-calendar"></i></a>
          </div>
          <div class="hero-stats" aria-label="Business outcomes">
            <div class="stat-chip"><strong data-count="38">0</strong><span>Faster operations</span></div>
            <div class="stat-chip"><strong data-count="74">0</strong><span>Less manual work</span></div>
            <div class="stat-chip"><strong data-count="99">0</strong><span>Cloud uptime</span></div>
          </div>
        </div>

        <div class="dashboard-wrap" aria-label="Vexogen ERP dashboard preview">
          <div class="dashboard-card">
            <div class="dash-top">
              <div class="dash-dots"><span></span><span></span><span></span></div>
              <div class="dash-search"></div>
            </div>
            <div class="dash-body">
              <aside class="dash-sidebar">
                <div class="dash-logo"><img src="<?= asset('images/vexogen-logo.png') ?>" alt=""> Vexogen</div>
                <div class="dash-nav">
                  <span><i class="ti ti-layout-dashboard"></i> Dashboard</span>
                  <span><i class="ti ti-users"></i> CRM</span>
                  <span><i class="ti ti-receipt-tax"></i> Invoices</span>
                  <span><i class="ti ti-box"></i> Inventory</span>
                  <span><i class="ti ti-chart-bar"></i> Analytics</span>
                </div>
              </aside>
              <div class="dash-main">
                <div class="dash-title-row">
                  <h2>Business Overview</h2>
                  <span class="dash-badge">Live ERP</span>
                </div>
                <div class="metric-grid">
                  <div class="metric"><span>Revenue</span><strong>₹48.6L</strong></div>
                  <div class="metric"><span>Sales</span><strong>1,284</strong></div>
                  <div class="metric"><span>Stock Alerts</span><strong>18</strong></div>
                </div>
                <div class="dash-grid">
                  <div class="panel">
                    <div class="panel-head"><span>Sales Charts</span><i class="ti ti-trending-up"></i></div>
                    <div class="bars"><span style="height:48%"></span><span style="height:72%"></span><span style="height:52%"></span><span style="height:84%"></span><span style="height:96%"></span><span style="height:64%"></span><span style="height:88%"></span></div>
                  </div>
                  <div class="panel">
                    <div class="panel-head"><span>Inventory Tracking</span><i class="ti ti-box"></i></div>
                    <div class="inventory-list">
                      <div class="inventory-row"><span>Raw Material</span><strong>82%</strong><div class="progress"><span style="width:82%"></span></div></div>
                      <div class="inventory-row"><span>Finished Goods</span><strong>68%</strong><div class="progress"><span style="width:68%"></span></div></div>
                      <div class="inventory-row"><span>Invoices Paid</span><strong>91%</strong><div class="progress"><span style="width:91%"></span></div></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="float-card">
            <i class="ti ti-users-group"></i>
            <strong>Team Performance</strong>
            <span>Projects, HR, tasks, and accountability in one clear view.</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section" aria-label="Trusted businesses">
      <div class="container">
        <div class="section-head center">
          <div class="section-kicker">Trusted By Businesses</div>
          <h2>Built for SMEs, manufacturers, agencies, and enterprises.</h2>
        </div>
        <div class="logo-strip">
          <div class="logo-track">
            <?php foreach (['Apex Manufacturing', 'Nexa Retail', 'BluePeak Agency', 'CoreBuild', 'Prime Foods', 'Vector Labs', 'Apex Manufacturing', 'Nexa Retail', 'BluePeak Agency', 'CoreBuild', 'Prime Foods', 'Vector Labs'] as $logo): ?>
              <span class="logo-pill"><i class="ti ti-building"></i><?= e($logo) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="section alt" id="modules">
      <div class="container">
        <div class="section-head">
          <div class="section-kicker">Core Modules</div>
          <h2>One connected workspace for the departments that move your business.</h2>
          <p>Vexogen ERP brings operations, finance, sales, people, and reporting into a single source of truth.</p>
        </div>
        <div class="module-grid">
          <?php foreach ($modules as $module): ?>
            <article class="module-card">
              <div class="module-icon"><i class="ti <?= e($module['icon']) ?>"></i></div>
              <h3><?= e($module['title']) ?></h3>
              <p><?= e($module['text']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section" id="features">
      <div class="container">
        <div class="section-head center">
          <div class="section-kicker">Features</div>
          <h2>Everyday ERP tools, polished for speed and control.</h2>
          <p>Practical features your team can use daily without adding operational noise.</p>
        </div>
        <div class="feature-cloud">
          <?php foreach ($features as $feature): ?>
            <div class="feature-pill"><i class="ti ti-circle-check"></i><?= e($feature) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section alt">
      <div class="container">
        <div class="section-head">
          <div class="section-kicker">Why Choose Vexogen ERP</div>
          <h2>Designed to help teams move faster with fewer loose ends.</h2>
        </div>
        <div class="why-grid">
          <article class="why-card"><div class="why-icon"><i class="ti ti-bolt"></i></div><h3>Faster Operations</h3><p>Automate routine steps and keep every department working from the same live records.</p></article>
          <article class="why-card"><div class="why-icon"><i class="ti ti-robot"></i></div><h3>Reduce Manual Work</h3><p>Replace repeated spreadsheet updates with connected workflows and clean approvals.</p></article>
          <article class="why-card"><div class="why-icon"><i class="ti ti-chart-dots"></i></div><h3>Better Decision Making</h3><p>Use real-time analytics to understand sales, cash flow, stock, and delivery health.</p></article>
          <article class="why-card"><div class="why-icon"><i class="ti ti-building-skyscraper"></i></div><h3>Scalable for Any Business</h3><p>Start small and expand across locations, users, departments, and workflows.</p></article>
        </div>
      </div>
    </section>

    <!-- <section class="section" id="screenshots">
      <div class="container">
        <div class="section-head">
          <div class="section-kicker">Screenshots</div>
          <h2>Desktop and mobile dashboards that keep the business visible.</h2>
          <p>A clean interface for leadership reviews, finance tracking, sales monitoring, and field updates.</p>
        </div>
        <div class="screens">
          <div class="screen-card screen-desktop">
            <div class="mini-top"><span></span><span></span><span></span></div>
            <div class="screen-layout">
              <div class="screen-side"></div>
              <div class="screen-chart"></div>
            </div>
          </div>
          <div class="screen-card screen-mobile">
            <div class="mini-top"><span></span><span></span><span></span></div>
            <div class="phone-ui">
              <div class="phone-pill"></div>
              <div class="phone-card"></div>
              <div class="phone-pill"></div>
              <div class="phone-pill"></div>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    <section class="section alt" id="pricing">
      <div class="container">
        <div class="section-head center">
          <div class="section-kicker">Pricing</div>
          <h2>Choose the plan that fits your operating stage.</h2>
        </div>
        <div class="pricing-grid">
          <?php foreach ($plans as $plan): ?>
            <article class="pricing-card <?= !empty($plan['featured']) ? 'featured' : '' ?>">
              <div class="plan-tag"><?= e($plan['tag']) ?></div>
              <h3><?= e($plan['name']) ?></h3>
              <div class="price"><?= e($plan['price']) ?></div>
              <ul>
                <?php foreach ($plan['items'] as $item): ?>
                  <li><i class="ti ti-check"></i><?= e($item) ?></li>
                <?php endforeach; ?>
              </ul>
              <a class="btn <?= !empty($plan['featured']) ? 'btn-primary' : 'btn-outline' ?>" href="<?= e($primaryUrl) ?>">Get Started</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center">
          <div class="section-kicker">Testimonials</div>
          <h2>Customer success stories from modern operators.</h2>
        </div>
        <div class="testimonials">
          <article class="testimonial"><div class="stars">★★★★★</div><p>Vexogen ERP helped us replace scattered sheets with one controlled system for billing, stock, and sales.</p><div class="person"><span class="avatar">AR</span><span><strong>Aarav Rao</strong><span>Director, Apex Manufacturing</span></span></div></article>
          <article class="testimonial"><div class="stars">★★★★★</div><p>The dashboard gives our team the same view of clients, projects, invoices, and pending work every morning.</p><div class="person"><span class="avatar">NS</span><span><strong>Nisha Shah</strong><span>Founder, BluePeak Agency</span></span></div></article>
          <article class="testimonial"><div class="stars">★★★★★</div><p>Role access and reports made the biggest difference. Leadership sees numbers without chasing every department.</p><div class="person"><span class="avatar">KM</span><span><strong>Karan Mehta</strong><span>COO, Nexa Retail</span></span></div></article>
        </div>
      </div>
    </section>

    <section class="section alt" id="faq">
      <div class="container">
        <div class="section-head center">
          <div class="section-kicker">FAQ</div>
          <h2>Answers before you get started.</h2>
        </div>
        <div class="faq">
          <details open><summary>Can Vexogen ERP support multiple teams and roles?</summary><p>Yes. Vexogen ERP includes multi-user access and role management so each department can work with the right permissions.</p></details>
          <details><summary>Is it useful for manufacturers and agencies?</summary><p>Yes. The platform is structured for SMEs, manufacturers, agencies, and enterprise teams that need CRM, finance, inventory, projects, and reporting together.</p></details>
          <details><summary>Does it support GST billing?</summary><p>Yes. The ERP includes GST-ready billing workflows for quotations, invoices, payments, and financial tracking.</p></details>
          <details><summary>Can we use it on mobile?</summary><p>Yes. The landing experience and ERP interface are built with responsive layouts for desktop, tablet, and mobile access.</p></details>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="container">
        <div class="cta-panel">
          <h2>Ready to Transform Your Business?</h2>
          <p>Bring CRM, accounting, inventory, sales, HR, projects, and analytics into one premium ERP platform.</p>
          <div class="cta-actions">
            <a class="btn btn-primary" href="<?= e($primaryUrl) ?>">Get Started Today<i class="ti ti-arrow-right"></i></a>
            <a class="btn btn-outline" href="#pricing">View Pricing</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <a class="brand" href="<?= url() ?>" aria-label="Vexogen Technologies">
            <img src="<?= asset('images/vexogen-logo.png') ?>" alt="Vexogen">
            <span><strong>Vexogen Technologies</strong><span>Premium ERP Platform</span></span>
          </a>
          <p>Corporate, trustworthy, and modern business management software for teams ready to operate with clarity.</p>
        </div>
        <ul class="footer-links">
          <li><a href="#modules">About</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#pricing">Pricing</a></li>
          <li><a href="<?= e($loginUrl) ?>">Contact</a></li>
          <li><a href="<?= e($loginUrl) ?>">Privacy Policy</a></li>
        </ul>
        <div class="social" aria-label="Social links">
          <a href="#" aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>
          <a href="#" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
        </div>
      </div>
      <div class="copyright">&copy; <?= date('Y') ?> Vexogen Technologies. All rights reserved.</div>
    </div>
  </footer>
</div>
<script>
  const counters = document.querySelectorAll('[data-count]');
  const runCounter = (counter) => {
    const target = Number(counter.dataset.count || 0);
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 48));
    const timer = window.setInterval(() => {
      current = Math.min(target, current + step);
      counter.textContent = current + (target === 99 || target === 74 || target === 38 ? '%' : '');
      if (current >= target) window.clearInterval(timer);
    }, 24);
  };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting || entry.target.dataset.done) return;
        entry.target.dataset.done = 'true';
        runCounter(entry.target);
      });
    }, { threshold: .45 });
    counters.forEach((counter) => observer.observe(counter));
  } else {
    counters.forEach(runCounter);
  }
</script>
</body>
</html>
