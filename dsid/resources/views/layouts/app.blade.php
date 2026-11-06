<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace de pilotage') · DSID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--navy:#10283f;--blue:#1d6b8a;--teal:#2ba58f;--ink:#243746;--muted:#758795;--line:#e6edf1;--paper:#f5f8fa}
        *{box-sizing:border-box}body{font-family:'DM Sans',sans-serif;background:var(--paper);color:var(--ink);font-size:.94rem}h1,h2,h3,h4,.brand{font-family:Manrope,sans-serif}a{text-decoration:none}
        .topbar{height:72px;background:var(--navy);color:#fff;display:flex;align-items:center;padding:0 clamp(1rem,4vw,3.75rem);gap:1rem;position:sticky;top:0;z-index:20;box-shadow:0 4px 18px #10283f20}
        .brand{font-weight:800;letter-spacing:-.04em;color:#fff;display:flex;align-items:center;gap:.75rem}.brand-mark{width:36px;height:36px;border-radius:12px;background:linear-gradient(140deg,#45c5a4,#2490a7);display:grid;place-items:center;font-weight:800}.topbar .push{flex:1}.topbar .user-name{color:#c7d8e2}.topbar a{color:#e4eef3}.topbar a:hover{color:#fff}
        .shell{max-width:1500px;margin:0 auto;padding:2rem clamp(1rem,4vw,3.75rem) 4rem}.page-head{display:flex;align-items:end;justify-content:space-between;gap:1rem;margin-bottom:1.5rem}.eyebrow{text-transform:uppercase;font-size:.72rem;font-weight:700;letter-spacing:.13em;color:var(--teal)}h1{font-size:clamp(1.55rem,2.5vw,2.15rem);font-weight:800;letter-spacing:-.045em;color:var(--navy);margin:.25rem 0}.subhead{color:var(--muted)}
        .panel,.stat-card{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 6px 20px #183b5210}.panel{padding:1.35rem}.stat-card{padding:1.25rem;height:100%;position:relative;overflow:hidden}.stat-card:after{content:'';position:absolute;width:70px;height:70px;border-radius:50%;right:-25px;top:-25px;background:var(--tint,#e7f4f1)}.stat-label{color:var(--muted);font-size:.82rem;font-weight:600}.stat-value{font-family:Manrope,sans-serif;font-size:2rem;font-weight:800;color:var(--navy);margin-top:.45rem}.stat-icon{color:var(--accent,var(--teal));font-size:1.2rem}.section-title{font-family:Manrope,sans-serif;font-weight:700;color:var(--navy);font-size:1rem}.table{--bs-table-bg:transparent}.table th{font-size:.75rem;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em;border-bottom-color:var(--line);white-space:nowrap}.table td{vertical-align:middle;border-bottom-color:#eef2f4}.table tr:last-child td{border-bottom:0}.form-control,.form-select{border-color:#dce5ea;border-radius:10px;padding:.65rem .8rem}.form-control:focus,.form-select:focus{border-color:#68b9a5;box-shadow:0 0 0 .2rem #2ba58f20}.form-label{font-weight:600;color:#435867;font-size:.85rem}.btn{border-radius:10px;font-weight:600;padding:.62rem 1rem}.btn-primary{background:var(--blue);border-color:var(--blue)}.btn-primary:hover{background:#15536e;border-color:#15536e}.btn-success{background:var(--teal);border-color:var(--teal)}.btn-outline-secondary{border-color:#cfdae0;color:#526978}.badge{font-weight:600;padding:.48em .7em}.badge-a_demarrer{background:#edf1f4;color:#5f7180}.badge-en_cours{background:#e2f2f8;color:#1d6985}.badge-en_attente{background:#fff2d8;color:#98620d}.badge-termine{background:#e4f5eb;color:#27734a}.badge-annule{background:#fae9e8;color:#a14543}.badge-priority{background:#edf4f4;color:#4b6967}.muted{color:var(--muted)}.alert{border:0;border-radius:12px}.nav-links{display:flex;align-items:center;gap:1.1rem}.nav-links a{font-size:.87rem;color:#c7d8e2}.nav-links a.active,.nav-links a:hover{color:#fff}.filters{display:grid;grid-template-columns:2fr repeat(4,1fr) auto;gap:.65rem;align-items:end}.empty{padding:3rem 1rem;text-align:center;color:var(--muted)}.detail-label{font-size:.74rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;font-weight:700}.detail-value{font-weight:600;color:var(--navy);margin-top:.3rem}.task-row{border:1px solid var(--line);border-radius:13px;padding:1rem}.progress{height:8px;background:#eaf0f3}.progress-bar{background:var(--teal)}.footer{color:#93a2ac;font-size:.78rem;text-align:center;margin-top:3rem}
        @media(max-width:900px){.topbar{height:auto;min-height:72px;flex-wrap:wrap;padding-block:.65rem;gap:.5rem 1rem}.nav-links{order:3;width:100%;margin:0!important;overflow-x:auto;gap:1.25rem;padding:.35rem 0 .2rem;scrollbar-width:thin}.nav-links a{white-space:nowrap}.topbar .push{flex:1}.filters{grid-template-columns:1fr 1fr}.filters .search{grid-column:1/-1}.filters .btn{width:100%}}@media(max-width:600px){.topbar{min-height:62px;padding-inline:1rem}.topbar .user-name{display:none}.shell{padding:1.35rem 1rem 3rem}.page-head{align-items:start;flex-direction:column}.filters{grid-template-columns:1fr}.filters .search{grid-column:auto}.panel{padding:1rem}.hide-mobile{display:none}}
        @media print{.topbar,.no-print,.footer{display:none!important}body{background:#fff}.shell{max-width:none;padding:0}.panel,.stat-card{box-shadow:none}}
    </style>
    <style>
        .app-layout{min-height:100vh}.sidebar{position:fixed;inset:0 auto 0 0;width:248px;background:var(--navy);color:#fff;padding:1.4rem 1rem;display:flex;flex-direction:column;z-index:30;box-shadow:4px 0 18px #10283f18}.sidebar .brand{padding:0 .35rem 1.7rem}.sidebar .nav-links{display:flex;flex-direction:column;align-items:stretch;gap:.35rem;margin:0!important}.sidebar .nav-links a{display:flex;align-items:center;gap:.8rem;padding:.8rem .85rem;border-radius:10px;font-size:.9rem;color:#c7d8e2}.sidebar .nav-links a:hover{color:#fff;background:#ffffff12}.sidebar .nav-links a.active{color:#fff;background:var(--blue);font-weight:700}.nav-icon{width:1.2rem;text-align:center}.sidebar-bottom{margin-top:auto;border-top:1px solid #ffffff20;padding-top:1rem;display:grid;gap:.8rem}.sidebar .user-name{color:#c7d8e2;font-size:.85rem;padding:0 .35rem}.sidebar-bottom .btn{width:100%}.workspace{margin-left:248px;min-height:100vh}.topbar{height:72px;background:#fff;color:var(--ink);display:flex;align-items:center;padding:0 clamp(1rem,4vw,3.75rem);gap:1rem;position:sticky;top:0;z-index:20;box-shadow:0 4px 18px #10283f0c;border-bottom:1px solid var(--line)}.topbar .user-name{color:var(--muted)}
        @media(max-width:900px){.sidebar{width:210px}.workspace{margin-left:210px}.topbar{padding-inline:1.25rem}.shell{padding-inline:1.25rem}.filters{grid-template-columns:1fr 1fr}.filters .search{grid-column:1/-1}.filters .btn{width:100%}}
        @media(max-width:600px){.sidebar{width:68px;padding:.9rem .5rem}.sidebar .brand{justify-content:center;padding:0 0 1.2rem}.sidebar .brand>span:last-child,.sidebar .nav-label,.sidebar .user-name{display:none}.sidebar .nav-links a{justify-content:center;padding:.8rem .3rem}.sidebar-bottom{justify-items:center}.sidebar-bottom form{width:100%}.sidebar-bottom .btn{font-size:0;padding:.6rem}.sidebar-bottom .btn:after{content:'↪';font-size:1rem}.workspace{margin-left:68px}.topbar{height:62px;padding-inline:.8rem}.topbar .user-name{display:none}.shell{padding:1.35rem .8rem 3rem}.page-head{align-items:start;flex-direction:column}.filters{grid-template-columns:1fr}.filters .search{grid-column:auto}.panel{padding:1rem}.hide-mobile{display:none}}
        @media print{.sidebar,.topbar,.no-print,.footer{display:none!important}body{background:#fff}.workspace{margin:0}.shell{max-width:none;padding:0}.panel,.stat-card{box-shadow:none}}
        .topbar{position:fixed;inset:0 auto 0 0;width:248px;height:100vh;padding:1.4rem 1rem;display:flex;flex-direction:column;align-items:stretch;gap:0;background:var(--navy);color:#fff;box-shadow:4px 0 18px #10283f18}.topbar .brand{padding:0 .35rem 1.7rem}.topbar .nav-links{display:flex;flex-direction:column;align-items:stretch;gap:.35rem;margin:0!important}.topbar .nav-links a{display:block;padding:.8rem .85rem;border-radius:10px;font-size:.9rem;color:#c7d8e2}.topbar .nav-links a.active{color:#fff;background:var(--blue);font-weight:700}.topbar .nav-links a:hover{color:#fff;background:#ffffff12}.topbar .push{display:none}.topbar .user-name{margin-top:auto;padding:.8rem .35rem 0;border-top:1px solid #ffffff20;color:#c7d8e2}.topbar form{margin-top:.8rem}.topbar .btn{width:100%}.shell{max-width:none;margin-left:248px}.footer{padding-bottom:1rem}
        @media(max-width:900px){.topbar{width:210px;padding:.9rem .75rem}.shell{margin-left:210px;padding-inline:1.25rem}}
        @media(max-width:600px){.topbar{width:68px;padding:.9rem .45rem}.topbar .brand{justify-content:center;padding:0 0 1.2rem}.topbar .brand>span:last-child,.topbar .nav-links a{font-size:0}.topbar .nav-links a{position:relative;height:46px;padding:.6rem}.topbar .nav-links a:before{content:'•';font-size:1.2rem}.topbar .user-name{display:none}.topbar form{margin-top:auto}.topbar .btn{font-size:0;padding:.6rem}.topbar .btn:after{content:'↪';font-size:1rem}.shell{margin-left:68px;padding:1.35rem .8rem 3rem}.page-head{align-items:start;flex-direction:column}.filters{grid-template-columns:1fr}.filters .search{grid-column:auto}.panel{padding:1rem}.hide-mobile{display:none}}
        @media print{.topbar,.no-print,.footer{display:none!important}body{background:#fff}.shell{margin:0;padding:0}.panel,.stat-card{box-shadow:none}}
    </style>
    @stack('head')
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">D</span><span>DSID <small class="d-block fw-normal" style="font:500 .64rem 'DM Sans';color:#aac0cc;letter-spacing:.08em">PILOTAGE DES ACTIVITÉS</small></span></a>
    <nav class="nav-links ms-4">
        <a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Vue d’ensemble</a>
        <a class="{{ request()->routeIs('activites.*')?'active':'' }}" href="{{ route('activites.index') }}">Activités</a>
        @if(auth()->user()->hasRole('administrateur','directeur','chef_service','lecteur'))<a class="{{ request()->routeIs('rapports.*')?'active':'' }}" href="{{ route('rapports.index') }}">Rapports</a>@endif
        @if(auth()->user()->hasRole('administrateur'))
            <a href="{{ route('admin.users.index') }}">Administration</a>
            <a class="{{ request()->routeIs('admin.domaines.*')?'active':'' }}" href="{{ route('admin.domaines.index') }}">Domaines</a>
            <a class="{{ request()->routeIs('admin.services.*')?'active':'' }}" href="{{ route('admin.services.index') }}">Services</a>
            <a href="{{ route('admin.journal.index') }}">Journal</a>
        @endif
    </nav>
    <div class="push"></div>
    <span class="user-name">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}" class="no-print">@csrf<button class="btn btn-sm btn-outline-light">Déconnexion</button></form>
</header>
<main class="shell">
    @if(session('success'))<div class="alert alert-success bg-success-subtle text-success-emphasis mb-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger bg-danger-subtle text-danger-emphasis mb-4"><strong>Vérifiez les champs signalés.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
    <div class="footer">Direction des Systèmes d’Information et de la Digitalisation · {{ now()->year }}</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
