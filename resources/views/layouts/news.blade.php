<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'بوابة الأخبار والنشاطات الرسمية - الشركة العامة لموانئ العراق')</title>
    <meta name="description" content="@yield('meta_description', 'البوابة الإعلامية والإخبارية الرسمية لمكتب السيد المدير العام - الشركة العامة لموانئ العراق')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <!-- Theme & Icons -->
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --primary-navy: #0d1b2a;
            --primary-light: #3b82f6;
            --secondary: #0284c7;
            --accent-gold: #d97706;
            --accent-gold-light: #fef3c7;
            --urgent-red: #dc2626;
            --success-green: #059669;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.06);
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-full: 9999px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            direction: rtl;
            text-align: right;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: var(--transition);
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .container {
            width: 100%;
            max-width: 1240px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 20px;
            padding-right: 20px;
        }

        /* Top Official Government Bar */
        .gov-top-bar {
            background: linear-gradient(90deg, #0d1b2a 0%, #1e293b 100%);
            color: #e2e8f0;
            font-size: 0.85rem;
            padding: 8px 0;
            border-bottom: 2px solid var(--accent-gold);
        }

        .gov-top-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gov-flag-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .gov-meta-items {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .admin-login-btn {
            background: rgba(217, 119, 6, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(217, 119, 6, 0.4);
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            flex-shrink: 0;
            transition: var(--transition);
        }

        .admin-login-btn:hover {
            background: var(--accent-gold);
            color: #ffffff;
            border-color: var(--accent-gold);
            box-shadow: 0 0 10px rgba(217, 119, 6, 0.4);
        }

        /* Main Header Brand Bar */
        .main-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .brand-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0 10px 0;
            gap: 20px;
        }

        .brand-identity {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo-img {
            width: 76px;
            height: 76px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.08));
        }

        .brand-texts h1 {
            font-family: 'Cairo', sans-serif;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--primary-dark);
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .brand-texts h1 span {
            color: var(--primary);
        }

        .brand-texts p {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .header-search-form {
            position: relative;
            max-width: 340px;
            width: 100%;
        }

        .header-search-input {
            width: 100%;
            padding: 10px 42px 10px 16px;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            color: var(--text-main);
            outline: none;
            transition: var(--transition);
        }

        .header-search-input:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.12);
        }

        .header-search-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 1.1rem;
        }

        /* Header Categories Navigation */
        .header-categories-nav {
            border-top: 1px solid #f1f5f9;
            padding: 8px 0 10px 0;
            overflow-x: auto;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        .header-categories-nav::-webkit-scrollbar {
            height: 3px;
        }

        .header-categories-nav::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .header-categories-list {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: nowrap;
        }

        .header-cat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
            white-space: nowrap;
            text-decoration: none;
            flex-shrink: 0;
        }

        .header-cat-pill:hover {
            background: #e2e8f0;
            color: var(--primary-dark);
            border-color: #cbd5e1;
        }

        .header-cat-pill.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
        }

        .header-cat-pill .pill-count {
            background: rgba(0, 0, 0, 0.08);
            color: #475569;
            padding: 1px 7px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .header-cat-pill.active .pill-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .header-cat-pill .pill-icon {
            flex-shrink: 0;
        }

        /* Breaking News Ticker */
        .ticker-bar {
            background: #0d1b2a;
            color: #ffffff;
            border-top: 1px solid rgba(255,255,255,0.08);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
        }

        .ticker-inner {
            display: flex;
            align-items: center;
        }

        .ticker-badge {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            letter-spacing: 0.5px;
            box-shadow: 2px 0 8px rgba(220, 38, 38, 0.3);
            z-index: 2;
        }

        .ticker-badge-pulse {
            width: 8px;
            height: 8px;
            background: #ffffff;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }

        .ticker-marquee {
            flex-grow: 1;
            overflow: hidden;
            white-space: nowrap;
            padding: 6px 0;
            font-size: 0.9rem;
            color: #e2e8f0;
            position: relative;
            direction: rtl;
        }

        .ticker-track {
            display: flex;
            width: max-content;
            align-items: center;
            animation: rtlTickerMove 65s linear infinite;
            will-change: transform;
        }

        .ticker-marquee:hover .ticker-track,
        .ticker-marquee:active .ticker-track {
            animation-play-state: paused;
        }

        .ticker-track-group {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        @keyframes rtlTickerMove {
            0% {
                transform: translate3d(0, 0, 0);
            }
            100% {
                transform: translate3d(50%, 0, 0);
            }
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 38px;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .ticker-item:hover {
            color: var(--accent-gold);
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .ticker-badge {
                padding: 7px 12px;
                font-size: 0.78rem;
            }
            .ticker-marquee {
                font-size: 0.84rem;
            }
        }

        /* Navigation Menu & Categories */
        .nav-categories-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 10px 0;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .nav-categories-list {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .nav-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: var(--radius-full);
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-muted);
            background: #f1f5f9;
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .nav-pill:hover {
            background: #e2e8f0;
            color: var(--primary-dark);
        }

        .nav-pill.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
        }

        .nav-pill .badge-count {
            background: rgba(0,0,0,0.08);
            font-size: 0.75rem;
            padding: 2px 7px;
            border-radius: var(--radius-full);
            font-weight: 800;
        }

        .nav-pill.active .badge-count {
            background: rgba(255,255,255,0.25);
            color: #ffffff;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: var(--radius-sm);
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-category {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-urgent {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-important {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-routine {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Main Content */
        .site-content {
            flex-grow: 1;
            padding: 16px 0 50px 0;
        }

        /* Footer */
        .site-footer {
            background: #0d1b2a;
            color: #94a3b8;
            border-top: 3px solid var(--accent-gold);
            padding: 40px 0 20px 0;
            margin-top: auto;
            font-size: 0.9rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-col h4 {
            font-family: 'Cairo', sans-serif;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 14px;
            position: relative;
            padding-bottom: 8px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 2px;
            background: var(--accent-gold);
        }

        .footer-col p {
            line-height: 1.7;
            margin-bottom: 14px;
            color: #cbd5e1;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 8px;
        }

        .footer-links a {
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-links a:hover {
            color: var(--accent-gold);
            transform: translateX(-4px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.8rem;
        }

        /* Lightbox Modal for Images */
        .lightbox-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .lightbox-modal.active {
            display: flex;
        }

        .lightbox-content {
            max-width: 90vw;
            max-height: 85vh;
            position: relative;
            text-align: center;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: var(--radius-md);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            margin: 0 auto;
        }

        .lightbox-caption {
            color: #ffffff;
            margin-top: 12px;
            font-size: 1rem;
            font-weight: 600;
        }

        .lightbox-close {
            position: absolute;
            top: -40px;
            left: 0;
            background: rgba(255,255,255,0.2);
            border: none;
            color: #ffffff;
            font-size: 1.5rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .lightbox-close:hover {
            background: var(--urgent-red);
        }

        @media (max-width: 900px) {
            .brand-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-search-form {
                max-width: 100%;
            }
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .gov-top-inner {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- الترويسة الحكومية الرسمية -->
    <div class="gov-top-bar">
        <div class="container">
            <div class="gov-top-inner">
                <div class="gov-flag-title">
                    <span>🇮🇶</span>
                    <span>جمهورية العراق &bull; وزارة النقل &bull; الشركة العامة لموانئ العراق</span>
                </div>
                <div class="gov-meta-items">
                    <span id="live-datetime"></span>
                    @auth
                        <a href="{{ url('/news/admin') }}" class="admin-login-btn">
                            <x-heroicon-o-cog-6-tooth style="width: 15px; height: 15px;" />
                            <span>لوحة النشاطات ({{ auth()->user()->name }})</span>
                        </a>
                        <a href="{{ url('/ports/admin') }}" class="admin-login-btn" style="background: rgba(30, 58, 138, 0.2); border-color: rgba(59, 130, 246, 0.4);">
                            <x-heroicon-o-chart-bar style="width: 15px; height: 15px;" />
                            <span>لوحة الموانئ</span>
                        </a>
                    @else
                        <a href="{{ url('/news/admin/login') }}" class="admin-login-btn">
                            <x-heroicon-o-lock-closed style="width: 15px; height: 15px;" />
                            <span>دخول لوحة النشاطات</span>
                        </a>
                        <a href="{{ url('/ports/admin/login') }}" class="admin-login-btn" style="background: rgba(30, 58, 138, 0.2); border-color: rgba(59, 130, 246, 0.4);">
                            <x-heroicon-o-chart-bar style="width: 15px; height: 15px;" />
                            <span>دخول لوحة الموانئ</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- الهيدر الرئيسي مع الشعار والبحث والتصنيفات -->
    <header class="main-header">
        <div class="container">
            <div class="brand-row">
                <a href="{{ route('news.index') }}" class="brand-identity">
                    <img src="{{ asset('images/logo.png') }}" class="brand-logo-img" alt="شعار الشركة العامة لموانئ العراق">
                    <div class="brand-texts">
                        <h1>بوابة <span>الأنشطة والأخبار</span> الرسمية</h1>
                        <p>مكتب السيد المدير العام &bull; الشركة العامة لموانئ العراق</p>
                    </div>
                </a>

                <form action="{{ route('news.index') }}" method="GET" class="header-search-form">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        class="header-search-input"
                        placeholder="ابحث في الأخبار والنشاطات..."
                        aria-label="بحث في الأخبار والنشاطات"
                    >
                    <x-heroicon-o-magnifying-glass class="header-search-icon" style="width: 19px; height: 19px;" />
                </form>
            </div>

            <!-- شريط تصفية الأقسام والتصنيفات مدمج في الهيدر -->
            <nav class="header-categories-nav" aria-label="أقسام وتصنيفات الأنشطة">
                <ul class="header-categories-list">
                    <li>
                        <a
                            href="{{ route('news.index') }}"
                            class="header-cat-pill {{ !request('category') ? 'active' : '' }}"
                        >
                            <x-heroicon-o-squares-2x2 class="pill-icon" style="width: 16px; height: 16px;" />
                            <span>كافة النشاطات</span>
                            <span class="pill-count" id="totalPublicBadge">{{ $totalPublicActivities ?? 0 }}</span>
                        </a>
                    </li>
                    @if(isset($categoriesStats) && is_array($categoriesStats))
                        @foreach($categoriesStats as $catName => $count)
                            <li>
                                <a
                                    href="{{ route('news.index', ['category' => $catName]) }}"
                                    class="header-cat-pill {{ request('category') === $catName ? 'active' : '' }}"
                                >
                                    @if($catName === 'اجتماع رسمي')
                                        <x-heroicon-o-briefcase class="pill-icon" style="width: 16px; height: 16px;" />
                                    @elseif($catName === 'زيارة ميدانية')
                                        <x-heroicon-o-truck class="pill-icon" style="width: 16px; height: 16px;" />
                                    @elseif($catName === 'استقبال وفد')
                                        <x-heroicon-o-user-group class="pill-icon" style="width: 16px; height: 16px;" />
                                    @elseif($catName === 'مؤتمر أو ندوة')
                                        <x-heroicon-o-academic-cap class="pill-icon" style="width: 16px; height: 16px;" />
                                    @elseif($catName === 'قرار وتوجيه')
                                        <x-heroicon-o-document-check class="pill-icon" style="width: 16px; height: 16px;" />
                                    @elseif($catName === 'متابعة مشاريع')
                                        <x-heroicon-o-presentation-chart-line class="pill-icon" style="width: 16px; height: 16px;" />
                                    @else
                                        <x-heroicon-o-tag class="pill-icon" style="width: 16px; height: 16px;" />
                                    @endif
                                    <span>{{ $catName }}</span>
                                    <span class="pill-count">{{ $count }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </nav>
        </div>
    </header>

    <!-- شريط الأخبار العاجلة والأحدث -->
    @if(isset($breakingNews) && $breakingNews->isNotEmpty())
        <div class="ticker-bar">
            <div class="container">
                <div class="ticker-inner">
                    <div class="ticker-badge">
                        <span class="ticker-badge-pulse"></span>
                        <x-heroicon-o-bolt style="width: 16px; height: 16px;" />
                        <span>عاجل وهام</span>
                    </div>
                    <div class="ticker-marquee">
                        <div class="ticker-track" id="breakingTickerTrack">
                            @for($group = 0; $group < 2; $group++)
                                <div class="ticker-track-group" @if($group === 1) aria-hidden="true" @endif>
                                    @foreach($breakingNews as $item)
                                        <a href="{{ route('news.show', $item->id) }}" class="ticker-item" data-ticker-id="{{ $item->id }}">
                                            <span style="color: #facc15;">&bull;</span>
                                            <span>{{ $item->title }} ({{ $item->formatted_date }})</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- محتوى الصفحة الرئيسي -->
    <main class="site-content">
        @yield('content')
    </main>

    <!-- تذييل الصفحة الرسمي -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>الشركة العامة لموانئ العراق</h4>
                    <p>
                        إحدى تشكيلات وزارة النقل العراقية، الصرح البحري الرائد ومحور النقل والتجارة الدولية في العراق والخليج العربي. ترعى إدارة الموانئ العراقية وتطوير البنى التحتية للموانئ التجارية والنفطية وميناء الفاو الكبير ومشروع طريق التنمية.
                    </p>
                    <p style="font-size: 0.85rem; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                        <x-heroicon-o-map-pin style="width: 17px; height: 17px; color: var(--accent-gold); flex-shrink: 0;" />
                        <span>المقر العام: جمهورية العراق - محافظة البصرة - المعقل &bull; مكتب السيد المدير العام</span>
                    </p>
                </div>
                <div class="footer-col">
                    <h4>أقسام البوابة</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('news.index') }}"><x-heroicon-o-home style="width: 15px; height: 15px; color: var(--accent-gold);" /> الرئيسية</a></li>
                        <li><a href="{{ route('news.index', ['category' => 'اجتماع رسمي']) }}"><x-heroicon-o-building-office-2 style="width: 15px; height: 15px; color: var(--accent-gold);" /> الاجتماعات الرسمية</a></li>
                        <li>
                            <a href="{{ route('news.index', ['category' => 'زيارة ميدانية']) }}">
                                <svg style="width: 15px; height: 15px; color: var(--accent-gold); display: inline-block; vertical-align: -2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                    <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/>
                                    <path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/>
                                    <path d="M12 10V2"/>
                                    <path d="M12 2l5 3"/>
                                </svg>
                                الزيارات الميدانية والموانئ
                            </a>
                        </li>
                        <li><a href="{{ route('news.index', ['category' => 'استقبال وفد']) }}"><x-heroicon-o-user-group style="width: 15px; height: 15px; color: var(--accent-gold);" /> استقبال الوفود</a></li>
                        <li><a href="{{ route('news.index', ['category' => 'قرار وتوجيه']) }}"><x-heroicon-o-document-text style="width: 15px; height: 15px; color: var(--accent-gold);" /> القرارات والتوجيهات</a></li>
                        <li><a href="{{ route('news.index', ['category' => 'متابعة مشاريع']) }}"><x-heroicon-o-presentation-chart-line style="width: 15px; height: 15px; color: var(--accent-gold);" /> متابعة المشاريع</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>الوصول الإداري والتقارير</h4>
                    <ul class="footer-links">
                        <li><a href="{{ url('/admin') }}"><x-heroicon-o-lock-closed style="width: 15px; height: 15px; color: var(--accent-gold);" /> لوحة تحكم النظام (Filament)</a></li>
                        <li><a href="{{ route('reports.periodic.pdf') }}"><x-heroicon-o-arrow-down-tray style="width: 15px; height: 15px; color: var(--accent-gold);" /> تصدير التقرير الدوري الرسمي PDF</a></li>
                        <li><a href="{{ route('news.index') }}"><x-heroicon-o-photo style="width: 15px; height: 15px; color: var(--accent-gold);" /> أحدث التغطيات المصورة</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    جميع الحقوق محفوظة &copy; {{ date('Y') }} - جمهورية العراق &bull; وزارة النقل &bull; الشركة العامة لموانئ العراق
                </div>
                <div>
                    نظام إدارة وتوثيق نشاطات المدير العام
                </div>
            </div>
        </div>
    </footer>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox(event)">
        <div class="lightbox-content" onclick="event.stopPropagation()">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <img id="lightboxImg" src="" alt="" class="lightbox-img">
            <div id="lightboxCaption" class="lightbox-caption"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // عرض التاريخ العربي المباشر
        function updateLiveDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            };
            const dateStr = now.toLocaleDateString('ar-IQ', options);
            const timeStr = now.toLocaleTimeString('ar-IQ', { hour: '2-digit', minute: '2-digit' });
            const elem = document.getElementById('live-datetime');
            if (elem) {
                elem.innerHTML = '<span style="display:inline-flex;align-items:center;gap:5px;"><svg style="width:14px;height:14px;display:inline-block;vertical-align:-2px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>' + dateStr + '</span> <span style="margin:0 6px;opacity:0.4;">|</span> <span style="display:inline-flex;align-items:center;gap:5px;"><svg style="width:14px;height:14px;display:inline-block;vertical-align:-2px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>' + timeStr + '</span>';
            }
        }
        updateLiveDateTime();
        setInterval(updateLiveDateTime, 60000);

        // Lightbox Functions
        function openLightbox(src, caption) {
            const modal = document.getElementById('lightboxModal');
            const img = document.getElementById('lightboxImg');
            const captionElem = document.getElementById('lightboxCaption');
            img.src = src;
            captionElem.innerText = caption || '';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox(e) {
            if (e && e.target !== e.currentTarget && !e.target.classList.contains('lightbox-close')) return;
            const modal = document.getElementById('lightboxModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
