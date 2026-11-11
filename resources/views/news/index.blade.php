@extends('layouts.news')

@section('title', 'بوابة الأخبار والنشاطات الرسمية - الشركة العامة لموانئ العراق')

@push('styles')
<style>
    /* =========================================================
       MODERN SLIDESHOW (أحدث 10 نشاطات مضافة)
       ========================================================= */
    .slider-section-wrap {
        margin-bottom: 38px;
    }

    .slider-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .slider-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .slider-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .live-pulse-pill {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: var(--radius-full);
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.2px;
    }

    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseRing 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
    }

    @keyframes pulseRing {
        to {
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
        }
    }

    .slider-controls-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .slider-counter-pill {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: var(--radius-full);
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.5px;
    }

    .slider-nav-btn {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: var(--primary-dark);
        width: 36px;
        height: 36px;
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .slider-nav-btn:hover {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .slider-pause-btn {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        width: 36px;
        height: 36px;
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .slider-pause-btn:hover {
        background: #f1f5f9;
        color: var(--primary);
    }

    /* Main Slider Box */
    .slider-main-box {
        position: relative;
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        transition: var(--transition);
    }

    .slider-main-box:hover {
        box-shadow: var(--shadow-xl);
        border-color: var(--border-hover);
    }

    /* Autoplay Progress Line */
    .slider-progress-track {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(0, 0, 0, 0.06);
        z-index: 20;
    }

    .slider-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--accent-gold) 0%, var(--primary) 100%);
        transition: width 0.1s linear;
    }

    /* Slide Stage */
    .slider-stage {
        position: relative;
        min-height: 420px;
    }

    .slide-item {
        display: none;
        grid-template-columns: 1.15fr 1fr;
        min-height: 420px;
    }

    .slide-item.is-active {
        display: grid;
        animation: slideFadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes slideFadeIn {
        from {
            opacity: 0;
            transform: translateX(12px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Slide Media with Dual-layer Aura */
    .slide-media-wrap {
        position: relative;
        background: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        min-height: 420px;
    }

    .slide-ambient-aura {
        position: absolute;
        inset: -20px;
        background-size: cover;
        background-position: center;
        filter: blur(28px) brightness(0.65);
        opacity: 0.55;
        transform: scale(1.15);
        transition: transform 0.6s ease;
    }

    .slide-item:hover .slide-ambient-aura {
        transform: scale(1.22);
    }

    .slide-img {
        position: relative;
        z-index: 2;
        max-width: 100%;
        max-height: 420px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        transition: transform 0.4s ease;
    }

    .slide-item:hover .slide-img {
        transform: scale(1.02);
    }

    .slide-media-placeholder {
        position: relative;
        z-index: 2;
        text-align: center;
        color: #94a3b8;
        padding: 40px;
    }

    .slide-badges-floating {
        position: absolute;
        top: 18px;
        right: 18px;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .slide-badge-photos {
        position: absolute;
        bottom: 18px;
        right: 18px;
        z-index: 10;
        background: rgba(15, 23, 42, 0.85);
        color: #ffffff;
        backdrop-filter: blur(8px);
        padding: 5px 12px;
        border-radius: var(--radius-full);
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* Slide Content Info */
    .slide-info-wrap {
        padding: 34px 38px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #ffffff;
        position: relative;
        z-index: 2;
    }

    .slide-top-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .slide-index-tag {
        background: #eff6ff;
        color: var(--primary);
        border: 1px solid #bfdbfe;
        padding: 3px 10px;
        border-radius: var(--radius-full);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .slide-headline {
        font-family: 'Cairo', sans-serif;
        font-size: 1.65rem;
        font-weight: 900;
        line-height: 1.35;
        color: var(--primary-dark);
        margin-bottom: 14px;
        transition: color 0.2s ease;
    }

    .slide-headline a {
        color: inherit;
    }

    .slide-headline a:hover {
        color: var(--primary);
    }

    .slide-excerpt {
        color: #334155;
        font-size: 0.98rem;
        line-height: 1.75;
        margin-bottom: 22px;
    }

    .slide-bottom-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px dashed var(--border-color);
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    /* Thumbnails Strip */
    .slider-thumbs-wrap {
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
        padding: 14px 18px;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .slider-thumbs-track {
        display: flex;
        align-items: center;
        gap: 10px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .slider-thumb-item {
        flex: 0 0 auto;
        width: 145px;
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 8px;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        gap: 6px;
        position: relative;
        text-align: right;
    }

    .slider-thumb-item:hover {
        border-color: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .slider-thumb-item.is-active {
        border-color: var(--primary);
        background: #f0f7ff;
        box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.18);
        transform: translateY(-2px);
    }

    .thumb-mini-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
    }

    .slider-thumb-item.is-active .thumb-mini-header {
        color: var(--primary);
    }

    .thumb-img-box {
        width: 100%;
        height: 64px;
        border-radius: var(--radius-sm);
        background: #e2e8f0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumb-caption {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* =========================================================
       FILTER SEARCH NOTICE
       ========================================================= */
    .filter-notice-bar {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        border-radius: var(--radius-md);
        padding: 14px 20px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.95rem;
    }

    .filter-reset-link {
        color: #dc2626;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #ffffff;
        padding: 5px 12px;
        border-radius: var(--radius-full);
        border: 1px solid #fca5a5;
    }

    .filter-reset-link:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    /* =========================================================
       NEWS GRID & CARDS
       ========================================================= */
    .section-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border-color);
        position: relative;
    }

    .section-header-row::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: 0;
        width: 70px;
        height: 2px;
        background: var(--primary);
    }

    .section-header-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 26px;
        margin-bottom: 45px;
    }

    .news-card {
        background: #ffffff;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
    }

    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--border-hover);
    }

    .news-card.just-added {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        animation: newCardGlow 2.5s ease-out;
    }

    @keyframes newCardGlow {
        0% { transform: scale(0.97); opacity: 0.5; }
        50% { box-shadow: 0 0 25px rgba(16, 185, 129, 0.5); }
        100% { transform: scale(1); opacity: 1; }
    }

    .news-card-media {
        position: relative;
        background: #f1f5f9;
        height: 210px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .news-card-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.35s ease;
    }

    .news-card:hover .news-card-img {
        transform: scale(1.04);
    }

    .news-card-placeholder {
        text-align: center;
        color: #94a3b8;
        padding: 20px;
    }

    .news-card-badges-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .news-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .news-meta-row {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .news-card-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.12rem;
        font-weight: 800;
        line-height: 1.4;
        color: var(--primary-dark);
        margin-bottom: 10px;
    }

    .news-card-title a {
        color: inherit;
    }

    .news-card-title a:hover {
        color: var(--primary);
    }

    .news-card-excerpt {
        color: #475569;
        font-size: 0.88rem;
        line-height: 1.6;
        margin-bottom: 18px;
        flex-grow: 1;
    }

    .news-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 14px;
        border-top: 1px solid var(--border-color);
        margin-top: auto;
    }

    .read-article-link {
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .read-article-link:hover {
        color: var(--primary-dark);
        transform: translateX(-3px);
    }

    .btn-read-more {
        background: var(--primary);
        color: #ffffff;
        padding: 9px 20px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
    }

    .btn-read-more:hover {
        background: var(--primary-dark);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.35);
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-category {
        background: rgba(30, 58, 138, 0.92);
        color: #ffffff;
        backdrop-filter: blur(4px);
    }

    .badge-urgent {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #ffffff;
    }

    .badge-important {
        background: #d97706;
        color: #ffffff;
    }

    .badge-live-now {
        background: #10b981;
        color: #ffffff;
        animation: pulseRing 1.8s infinite;
    }

    /* Custom Pagination */
    .pagination-wrapper {
        margin: 45px 0 20px 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .custom-pagination {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        padding: 6px 10px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        flex-wrap: wrap;
    }

    .custom-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 10px;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid var(--border-color);
        color: var(--primary-dark);
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        transition: var(--transition);
        user-select: none;
    }

    .custom-page-btn:hover:not(.disabled):not(.active) {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .custom-page-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
    }

    .custom-page-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: #f1f5f9;
        color: #94a3b8;
    }

    .custom-page-btn.dots {
        border: none;
        background: transparent;
        cursor: default;
    }

    .custom-page-btn svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px dashed var(--border-color);
        margin: 30px 0;
    }

    .empty-state h3 {
        font-family: 'Cairo', sans-serif;
        font-size: 1.3rem;
        color: var(--primary-dark);
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    /* =========================================================
       REAL-TIME LIVE TOAST NOTIFICATION
       ========================================================= */
    .live-toast-container {
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 420px;
        pointer-events: none;
    }

    .live-toast-box {
        pointer-events: auto;
        background: #ffffff;
        border: 1.5px solid #10b981;
        box-shadow: 0 14px 35px -5px rgba(15, 23, 42, 0.18), 0 0 15px rgba(16, 185, 129, 0.25);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        animation: toastEntrance 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        position: relative;
    }

    @keyframes toastEntrance {
        from {
            opacity: 0;
            transform: translateY(40px) scale(0.92);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .toast-icon-wrap {
        background: #ecfdf5;
        color: #059669;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .toast-content-wrap {
        flex-grow: 1;
    }

    .toast-top-tag {
        font-size: 0.72rem;
        font-weight: 800;
        color: #059669;
        text-transform: uppercase;
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .toast-title {
        font-family: 'Cairo', sans-serif;
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.35;
        margin-bottom: 6px;
    }

    .toast-view-link {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .toast-close-btn {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 2px;
        font-size: 1.1rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .toast-close-btn:hover {
        color: #dc2626;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .news-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .slide-item {
            grid-template-columns: 1fr;
        }
        .slide-media-wrap {
            min-height: 280px;
        }
        .slide-img {
            max-height: 280px;
        }
        .slide-info-wrap {
            padding: 24px 20px;
        }
    }

    @media (max-width: 640px) {
        .news-grid {
            grid-template-columns: 1fr;
        }
        .slider-header-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .slider-controls-group {
            width: 100%;
            justify-content: space-between;
        }
        .slide-headline {
            font-size: 1.25rem;
        }
        .slider-thumb-item {
            width: 125px;
        }
        .live-toast-container {
            left: 12px;
            right: 12px;
            bottom: 12px;
            max-width: none;
        }
    }
    /* =========================================================
       PORTS LIVE DASHBOARD STYLES (موقف السفن، الطاقة، الإيراد)
       ========================================================= */
    .ports-live-section {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        margin-bottom: 35px;
        overflow: hidden;
    }

    /* Vessels Ticker Bar */
    .vessels-ticker-bar {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        padding: 10px 14px;
        border-bottom: 1px solid #334155;
    }

    .vessels-ticker-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow: hidden;
    }

    .vessels-ticker-badge {
        background: #2563eb;
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 800;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
        z-index: 2;
        line-height: 1.2;
    }

    .vessels-ticker-badge-main {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .vessels-ticker-date {
        font-size: 0.68rem;
        color: #dbeafe;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .vessels-ticker-pulse {
        width: 8px;
        height: 8px;
        background: #60a5fa;
        border-radius: 50%;
        display: inline-block;
        animation: pulseRing 1.8s infinite;
    }

    .vessels-ticker-scroll {
        flex-grow: 1;
        overflow: hidden;
        white-space: nowrap;
        position: relative;
        direction: rtl;
        padding: 2px 0;
    }

    .vessels-ticker-track {
        display: flex;
        width: max-content;
        align-items: center;
        animation: rtlTickerMove 28s linear infinite;
        will-change: transform;
    }

    .vessels-ticker-scroll:hover .vessels-ticker-track,
    .vessels-ticker-scroll:active .vessels-ticker-track {
        animation-play-state: paused;
    }

    .vessels-ticker-group {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-left: 10px;
        flex-shrink: 0;
    }

    .vessel-kpi-chip {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        padding: 4px 11px;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #e2e8f0;
        flex-shrink: 0;
    }

    @media (max-width: 640px) {
        .vessels-ticker-bar {
            padding: 7px 8px;
        }
        .vessels-ticker-inner {
            gap: 8px;
        }
        .vessels-ticker-badge {
            padding: 3px 8px;
            font-size: 0.7rem;
            border-radius: 8px;
            gap: 1px;
        }
        .vessels-ticker-date {
            font-size: 0.62rem;
        }
        .vessel-kpi-chip {
            font-size: 0.75rem;
            padding: 3px 9px;
        }
    }

    .vessel-kpi-chip.chip-highlight {
        background: rgba(37, 99, 235, 0.25);
        border-color: rgba(96, 165, 250, 0.4);
        color: #ffffff;
    }

    .vessel-kpi-chip strong {
        color: #38bdf8;
        font-weight: 800;
    }

    .chip-highlight strong {
        color: #facc15;
    }

    /* Tabs Header */
    .ports-tabs-nav {
        padding: 18px 22px 14px 22px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .ports-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .heading-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(30, 58, 138, 0.2);
    }

    .ports-section-heading h3 {
        font-family: 'Cairo', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin: 0 0 3px 0;
    }

    .ports-section-heading p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
    }

    .ports-tab-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ports-tab-btn {
        background: #ffffff;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 8px 14px;
        border-radius: var(--radius-md);
        font-family: 'Cairo', sans-serif;
        font-size: 0.86rem;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .ports-tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: #f0f7ff;
    }

    .ports-tab-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.25);
    }

    .ports-tab-btn .tab-chip {
        background: rgba(0, 0, 0, 0.06);
        color: inherit;
        padding: 2px 7px;
        border-radius: var(--radius-full);
        font-size: 0.74rem;
        font-weight: 800;
    }

    .ports-tab-btn.active .tab-chip {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    /* Tabs Content Panes */
    .ports-tabs-content {
        padding: 22px;
    }

    .ports-tab-pane {
        display: none;
    }

    .ports-tab-pane.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* KPI Cards Grid */
    .kpi-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .kpi-metric-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 16px 18px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .kpi-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .kpi-metric-card.border-blue { border-top: 4px solid #2563eb; }
    .kpi-metric-card.border-emerald { border-top: 4px solid #10b981; }
    .kpi-metric-card.border-amber { border-top: 4px solid #f59e0b; }
    .kpi-metric-card.border-indigo { border-top: 4px solid #6366f1; }
    .kpi-metric-card.border-purple { border-top: 4px solid #8b5cf6; }

    .metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .metric-label {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    .metric-badge-blue, .metric-badge-emerald, .metric-badge-amber, .metric-badge-indigo, .metric-badge-purple {
        font-size: 0.72rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: var(--radius-full);
    }

    .metric-badge-blue { background: #eff6ff; color: #1d4ed8; }
    .metric-badge-emerald { background: #ecfdf5; color: #047857; }
    .metric-badge-amber { background: #fffbeb; color: #b45309; }
    .metric-badge-indigo { background: #eef2ff; color: #4338ca; }
    .metric-badge-purple { background: #f5f3ff; color: #6d28d9; }

    .metric-main {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 6px;
    }

    .metric-num {
        font-family: 'Cairo', sans-serif;
        font-size: 1.85rem;
        font-weight: 900;
        color: var(--primary-dark);
        line-height: 1;
    }

    .metric-num.text-emerald { color: #059669; }
    .metric-num.text-amber { color: #d97706; }
    .metric-num.text-indigo { color: #4f46e5; }
    .metric-num.text-purple { color: #7c3aed; }

    .metric-unit {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    .metric-sub {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.4;
    }

    /* Breakdown Mini Table */
    .ports-vessels-breakdown {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .breakdown-table-wrap {
        overflow-x: auto;
    }

    .ports-mini-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .ports-mini-table th {
        background: #edf2f7;
        color: var(--primary-dark);
        font-family: 'Cairo', sans-serif;
        font-weight: 800;
        text-align: right;
        padding: 11px 16px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .ports-mini-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .ports-mini-table tr:last-child td {
        border-bottom: none;
    }

    .ports-mini-table tr:hover {
        background: #ffffff;
    }

    .ports-mini-table strong {
        color: var(--primary-dark);
        display: block;
        font-size: 0.92rem;
    }

    .ports-mini-table .sub-text {
        font-size: 0.76rem;
        color: var(--text-muted);
    }

    .port-count-badge {
        font-size: 0.82rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: var(--radius-full);
        display: inline-block;
        white-space: nowrap;
    }

    .port-count-badge.badge-blue { background: #dbeafe; color: #1e40af; }
    .port-count-badge.badge-emerald { background: #d1fae5; color: #065f46; }
    .port-count-badge.badge-amber { background: #fef3c7; color: #92400e; }
    .port-count-badge.badge-purple { background: #ede9fe; color: #5b21b6; }

    .status-pill {
        font-size: 0.76rem;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status-pill.status-active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .progress-bar-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 140px;
    }

    .progress-bar-fill {
        height: 7px;
        background: #2563eb;
        border-radius: 4px;
        flex-grow: 1;
    }

    .progress-bar-fill.fill-emerald {
        background: #10b981;
    }

    .progress-label {
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--text-muted);
        min-width: 34px;
    }

    .currency-text {
        font-family: monospace;
        font-size: 0.92rem;
        color: #0f172a;
    }

    @media (max-width: 768px) {
        .ports-tabs-nav {
            flex-direction: column;
            align-items: flex-start;
        }
        .ports-tab-buttons {
            width: 100%;
        }
        .ports-tab-btn {
            flex: 1 1 auto;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 0; padding-bottom: 40px;">

    <!-- تنبيه التصفية والبحث في حال تفعيله -->
    @if(request('q') || request('category') || request('importance'))
        <div class="filter-notice-bar">
            <div style="display: flex; align-items: center; gap: 8px;">
                <x-heroicon-o-magnifying-glass style="width: 18px; height: 18px;" />
                <span>نتائج التصفية:</span>
                @if(request('category'))
                    <span>قسم: <strong>{{ request('category') }}</strong></span>
                @endif
                @if(request('q'))
                    <span> &bull; كلمة البحث: <strong>"{{ request('q') }}"</strong></span>
                @endif
                @if(request('importance'))
                    <span> &bull; الأهمية: <strong>{{ request('importance') }}</strong></span>
                @endif
                (<span id="filteredActivitiesCount">{{ $activities->total() }}</span> نشاط)
            </div>
            <a href="{{ route('news.index') }}" class="filter-reset-link">
                ✕ إلغاء التصفية وعرض الكل
            </a>
        </div>
    @endif

    <!-- =========================================================
         MODERN SLIDESHOW (آخر 10 نشاطات مضافة)
         يظهر فقط في الصفحة الرئيسية دون بحث مخصص
         ========================================================= -->
    @if(!request('q') && !request('category') && !request('importance') && !request('from') && !request('to') && $activities->currentPage() === 1 && $sliderActivities->isNotEmpty())
        <section class="slider-section-wrap" aria-label="أحدث الأنشطة المضافة">
            <!-- Header bar for slider -->
            <div class="slider-header-bar">
                <div class="slider-title-group">
                    <h2 class="slider-title">
                        <x-heroicon-o-sparkles style="width: 22px; height: 22px; color: var(--accent-gold);" />
                        <span>أبرز التغطيات الرسمية المصورة</span>
                    </h2>
                    <span class="live-pulse-pill" title="يتم تحديث الأخبار تلقائياً دون إعادة تحميل الصفحة">
                        <span class="live-pulse-dot"></span>
                        <span>مباشر &bull; آخر 10 نشاطات</span>
                    </span>
                </div>

                <div class="slider-controls-group">
                    <span class="slider-counter-pill" id="sliderCounterText">
                        01 / {{ sprintf('%02d', $sliderActivities->count()) }}
                    </span>

                    <button type="button" class="slider-pause-btn" id="sliderPauseBtn" title="إيقاف / تشغيل التشغيل التلقائي" aria-label="Pause Autoplay">
                        <x-heroicon-o-pause id="iconPause" style="width: 17px; height: 17px;" />
                        <x-heroicon-o-play id="iconPlay" style="width: 17px; height: 17px; display: none;" />
                    </button>

                    <button type="button" class="slider-nav-btn" id="sliderPrevBtn" title="النشاط السابق" aria-label="Previous Slide">
                        <x-heroicon-o-chevron-right style="width: 18px; height: 18px;" />
                    </button>

                    <button type="button" class="slider-nav-btn" id="sliderNextBtn" title="النشاط التالي" aria-label="Next Slide">
                        <x-heroicon-o-chevron-left style="width: 18px; height: 18px;" />
                    </button>
                </div>
            </div>

            <!-- Slider Main Box -->
            <div class="slider-main-box" id="sliderMainBox">
                <!-- Autoplay progress line -->
                <div class="slider-progress-track">
                    <div class="slider-progress-bar" id="sliderProgressBar"></div>
                </div>

                <!-- Slide Stage -->
                <div class="slider-stage" id="sliderStage">
                    @foreach($sliderActivities as $idx => $sAct)
                        @php
                            $sImg = $sAct->primaryAttachment ?: $sAct->imageAttachments->first();
                            $hasImg = $sImg && $sImg->base64_data_uri;
                        @endphp
                        <article
                            class="slide-item {{ $idx === 0 ? 'is-active' : '' }}"
                            data-slide-index="{{ $idx }}"
                            data-activity-id="{{ $sAct->id }}"
                        >
                            <!-- Right: Media Showcase -->
                            <div class="slide-media-wrap">
                                @if($hasImg)
                                    <div
                                        class="slide-ambient-aura"
                                        style="background-image: url('{{ $sImg->base64_data_uri }}');"
                                    ></div>
                                    <img
                                        src="{{ $sImg->base64_data_uri }}"
                                        alt="{{ $sAct->title }}"
                                        class="slide-img"
                                        loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                                    >
                                @else
                                    <div class="slide-media-placeholder">
                                        <svg style="width: 58px; height: 58px; margin: 0 auto 10px auto; display: block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="5" r="3"/>
                                            <line x1="12" y1="22" x2="12" y2="8"/>
                                            <path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                                        </svg>
                                        <span style="font-size: 0.85rem;">الشركة العامة لموانئ العراق</span>
                                    </div>
                                @endif

                                @if($sAct->imageAttachments->count() > 1)
                                    <div class="slide-badge-photos">
                                        <x-heroicon-o-photo style="width: 14px; height: 14px;" />
                                        <span>{{ $sAct->imageAttachments->count() }} صور موثقة</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Left: Info & Details -->
                            <div class="slide-info-wrap">
                                <div class="slide-top-meta">
                                    <span class="slide-index-tag">نشاط #{{ sprintf('%02d', $idx + 1) }} من {{ sprintf('%02d', $sliderActivities->count()) }}</span>
                                    <span class="badge badge-category">{{ $sAct->category }}</span>
                                    @if($sAct->importance === 'urgent')
                                        <span class="badge badge-urgent">عاجل وهام</span>
                                    @elseif($sAct->importance === 'important')
                                        <span class="badge badge-important">مهم</span>
                                    @endif
                                    <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                        <x-heroicon-o-calendar style="width: 15px; height: 15px;" />
                                        {{ $sAct->formatted_date }}
                                    </span>
                                    @if($sAct->location)
                                        <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                            <x-heroicon-o-map-pin style="width: 15px; height: 15px;" />
                                            {{ $sAct->location }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="slide-headline">
                                    <a href="{{ route('news.show', $sAct->id) }}">
                                        {{ $sAct->title }}
                                    </a>
                                </h3>

                                <p class="slide-excerpt">
                                    {{ Str::limit($sAct->short_description, 230) }}
                                </p>

                                <div class="slide-bottom-bar">
                                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                                        <x-heroicon-o-shield-check style="width: 16px; height: 16px; color: #059669;" />
                                        <span>تغطية إعلامية رسمية معتمدة</span>
                                    </span>

                                    <a href="{{ route('news.show', $sAct->id) }}" class="btn-read-more">
                                        <span>اقرأ التفاصيل الكاملة</span>
                                        <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" />
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Thumbnails Strip below slide -->
                <div class="slider-thumbs-wrap">
                    <ul class="slider-thumbs-track" id="sliderThumbsTrack">
                        @foreach($sliderActivities as $idx => $sAct)
                            @php
                                $sImg = $sAct->primaryAttachment ?: $sAct->imageAttachments->first();
                            @endphp
                            <li
                                class="slider-thumb-item {{ $idx === 0 ? 'is-active' : '' }}"
                                data-thumb-index="{{ $idx }}"
                                data-activity-id="{{ $sAct->id }}"
                                onclick="switchSlide({{ $idx }})"
                            >
                                <div class="thumb-mini-header">
                                    <span>#{{ sprintf('%02d', $idx + 1) }}</span>
                                    <span>{{ Str::limit($sAct->category, 12) }}</span>
                                </div>
                                <div class="thumb-img-box">
                                    @if($sImg && $sImg->base64_data_uri)
                                        <img src="{{ $sImg->base64_data_uri }}" alt="" class="thumb-img">
                                    @else
                                        <svg style="width: 22px; height: 22px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 6v6l4 2"/>
                                        </svg>
                                    @endif
                                </div>
                                <span class="thumb-caption" title="{{ $sAct->title }}">
                                    {{ Str::limit($sAct->title, 24) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    <!-- لوحة المؤشرات التشغيلية والمالية (موقف السفن اليومي + الطاقة الإنتاجية + الإيراد) -->
    @include('partials.ports-dashboard')

    <!-- =========================================================
         قسم شبكة الأخبار والنشاطات
         ========================================================= -->
    <div class="section-header-row">
        <h3 class="section-header-title">
            @if(request('category'))
                نشاطات وأخبار قسم: {{ request('category') }}
            @elseif(request('q'))
                نتائج البحث عن: "{{ request('q') }}"
            @else
                <x-heroicon-o-newspaper style="width: 24px; height: 24px; color: var(--primary);" />
                <span>أحدث التغطيات والنشاطات الرسمية (التسلسل الزمني)</span>
            @endif
        </h3>
        <span style="font-size: 0.88rem; color: var(--text-muted); font-weight: 700;">
            إجمالي المعروض: <span id="newsGridTotalCount">{{ $activities->total() }}</span> نشاط
        </span>
    </div>

    @if($activities->isNotEmpty())
        <div class="news-grid" id="newsGridContainer">
            @foreach($activities as $act)
                @php
                    $cardImg = $act->primaryAttachment ?: $act->imageAttachments->first();
                @endphp
                <article class="news-card" data-activity-id="{{ $act->id }}">
                    <div class="news-card-media">
                        @if($cardImg && $cardImg->base64_data_uri)
                            <img
                                src="{{ $cardImg->base64_data_uri }}"
                                alt="{{ $act->title }}"
                                class="news-card-img"
                                loading="lazy"
                            >
                        @else
                            <div class="news-card-placeholder">
                                <svg style="width: 34px; height: 34px; color: #94a3b8; margin: 0 auto 6px auto; display: block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="5" r="3"/>
                                    <line x1="12" y1="22" x2="12" y2="8"/>
                                    <path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                                </svg>
                                <span style="font-size: 0.75rem;">الشركة العامة لموانئ العراق</span>
                            </div>
                        @endif
                    </div>

                    <div class="news-card-body">
                        <div class="news-card-badges-row">
                            <span class="badge badge-category">{{ $act->category }}</span>
                            @if($act->importance === 'urgent')
                                <span class="badge badge-urgent">عاجل</span>
                            @elseif($act->importance === 'important')
                                <span class="badge badge-important">مهم</span>
                            @endif
                        </div>

                        <div class="news-meta-row">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <x-heroicon-o-calendar style="width: 14px; height: 14px;" />
                                {{ $act->formatted_date }}
                            </span>
                            @if($act->location)
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <x-heroicon-o-map-pin style="width: 14px; height: 14px;" />
                                    {{ Str::limit($act->location, 22) }}
                                </span>
                            @endif
                        </div>

                        <h4 class="news-card-title">
                            <a href="{{ route('news.show', $act->id) }}">
                                {{ $act->title }}
                            </a>
                        </h4>

                        <p class="news-card-excerpt">
                            {{ Str::limit($act->short_description, 130) }}
                        </p>

                        <div class="news-card-footer">
                            <span style="color: var(--text-muted); font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                                @if($act->imageAttachments->count() > 0)
                                    <x-heroicon-o-photo style="width: 14px; height: 14px;" />
                                    <span>{{ $act->imageAttachments->count() }} مرفق</span>
                                @else
                                    <x-heroicon-o-document-text style="width: 14px; height: 14px;" />
                                    <span>وثيقة رسمية</span>
                                @endif
                            </span>

                            <a href="{{ route('news.show', $act->id) }}" class="read-article-link">
                                <span>التفاصيل</span>
                                <x-heroicon-o-arrow-left style="width: 14px; height: 14px;" />
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- الترقيم والتنقل بين الصفحات -->
        <div class="pagination-wrapper">
            {{ $activities->onEachSide(1)->links('partials.pagination') }}
        </div>
    @else
        <div class="empty-state">
            <x-heroicon-o-clipboard-document-list style="width: 48px; height: 48px; color: #94a3b8; margin: 0 auto 12px auto; display: block;" />
            <h3>لا توجد أنشطة مسجلة وفق هذه المعايير</h3>
            <p>يرجى اختيار قسم آخر أو تغيير معايير البحث.</p>
            <div style="margin-top: 18px;">
                <a href="{{ route('news.index') }}" class="btn-read-more">
                    <span>العودة لكافة الأنشطة</span>
                    <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" />
                </a>
            </div>
        </div>
    @endif

</div>

<!-- Floating Real-Time Toast Container -->
<div id="liveToastContainer" class="live-toast-container"></div>
@endsection

@push('scripts')
<script>
    /* =========================================================
       MODERN SLIDESHOW ENGINE
       ========================================================= */
    let currentSlide = 0;
    let autoplayInterval = null;
    let isAutoplayPaused = false;
    const SLIDE_DURATION = 10000; // 10 ثوانٍ لكل نشاط
    let progressStartTime = 0;
    let progressAnimFrame = null;

    function getSlides() {
        return document.querySelectorAll('.slide-item');
    }

    function getThumbs() {
        return document.querySelectorAll('.slider-thumb-item');
    }

    function updateSliderCounter(index, total) {
        const counterEl = document.getElementById('sliderCounterText');
        if (counterEl) {
            const pad = (n) => String(n).padStart(2, '0');
            counterEl.innerText = pad(index + 1) + ' / ' + pad(total);
        }
    }

    function resetProgressBar() {
        const pBar = document.getElementById('sliderProgressBar');
        if (pBar) {
            pBar.style.width = '0%';
        }
        if (progressAnimFrame) {
            cancelAnimationFrame(progressAnimFrame);
        }
        progressStartTime = Date.now();
        if (!isAutoplayPaused) {
            animateProgressBar();
        }
    }

    function animateProgressBar() {
        if (isAutoplayPaused) return;
        const elapsed = Date.now() - progressStartTime;
        const percent = Math.min((elapsed / SLIDE_DURATION) * 100, 100);
        const pBar = document.getElementById('sliderProgressBar');
        if (pBar) {
            pBar.style.width = percent + '%';
        }

        if (percent < 100) {
            progressAnimFrame = requestAnimationFrame(animateProgressBar);
        }
    }

    function switchSlide(index) {
        const slides = getSlides();
        const thumbs = getThumbs();
        if (slides.length === 0) return;

        if (index >= slides.length) index = 0;
        if (index < 0) index = slides.length - 1;

        currentSlide = index;

        slides.forEach((s, i) => {
            s.classList.toggle('is-active', i === currentSlide);
        });

        thumbs.forEach((t, i) => {
            t.classList.toggle('is-active', i === currentSlide);
            if (i === currentSlide) {
                // تمرير شريط المصغرات أفقياً فقط دون تحريك مؤشر الصفحة أو النزول
                const thumbsWrap = document.querySelector('.slider-thumbs-wrap');
                if (thumbsWrap) {
                    const targetLeft = t.offsetLeft - (thumbsWrap.clientWidth / 2) + (t.clientWidth / 2);
                    thumbsWrap.scrollTo({ left: targetLeft, behavior: 'smooth' });
                }
            }
        });

        updateSliderCounter(currentSlide, slides.length);
        resetProgressBar();
    }

    function nextSlide() {
        switchSlide(currentSlide + 1);
    }

    function prevSlide() {
        switchSlide(currentSlide - 1);
    }

    function toggleAutoplay() {
        isAutoplayPaused = !isAutoplayPaused;
        const iconPause = document.getElementById('iconPause');
        const iconPlay = document.getElementById('iconPlay');

        if (isAutoplayPaused) {
            if (autoplayInterval) clearInterval(autoplayInterval);
            if (progressAnimFrame) cancelAnimationFrame(progressAnimFrame);
            if (iconPause) iconPause.style.display = 'none';
            if (iconPlay) iconPlay.style.display = 'block';
        } else {
            resetProgressBar();
            autoplayInterval = setInterval(nextSlide, SLIDE_DURATION);
            if (iconPause) iconPause.style.display = 'block';
            if (iconPlay) iconPlay.style.display = 'none';
        }
    }

    // Init slider
    document.addEventListener('DOMContentLoaded', function() {
        const nextBtn = document.getElementById('sliderNextBtn');
        const prevBtn = document.getElementById('sliderPrevBtn');
        const pauseBtn = document.getElementById('sliderPauseBtn');
        const mainBox = document.getElementById('sliderMainBox');

        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (pauseBtn) pauseBtn.addEventListener('click', toggleAutoplay);

        // Hover pause
        if (mainBox) {
            mainBox.addEventListener('mouseenter', () => {
                isAutoplayPaused = true;
                if (progressAnimFrame) cancelAnimationFrame(progressAnimFrame);
            });
            mainBox.addEventListener('mouseleave', () => {
                const iconPause = document.getElementById('iconPause');
                // Only resume if user hadn't manually paused
                if (iconPause && iconPause.style.display !== 'none') {
                    isAutoplayPaused = false;
                    resetProgressBar();
                }
            });
        }

        // Keyboard arrows
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') prevSlide();
            if (e.key === 'ArrowLeft') nextSlide();
        });

        // Start autoplay
        if (getSlides().length > 1) {
            resetProgressBar();
            autoplayInterval = setInterval(nextSlide, SLIDE_DURATION);
        }
    });

    /* =========================================================
       REAL-TIME LIVE UPDATES ENGINE (تحديث لحظي بدون إعادة تحميل)
       ========================================================= */
    window.LATEST_ACTIVITY_ID = {{ $latestPublicId ?? 0 }};
    const LIVE_POLL_INTERVAL = 4000; // فحص خفيف كل 4 ثوانٍ
    let livePollTimer = null;

    function playSoftBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } catch(e) {
            // AudioContext might be restricted until user interaction
        }
    }

    function showLiveToast(item) {
        const container = document.getElementById('liveToastContainer');
        if (!container) return;

        playSoftBeep();

        const toast = document.createElement('div');
        toast.className = 'live-toast-box';
        toast.innerHTML = `
            <div class="toast-icon-wrap">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5"/>
                </svg>
            </div>
            <div class="toast-content-wrap">
                <div class="toast-top-tag">
                    <span class="live-pulse-dot" style="width: 6px; height: 6px;"></span>
                    <span>تم نشر نشاط جديد للتو &bull; ${item.category}</span>
                </div>
                <div class="toast-title">${item.title}</div>
                <a href="${item.url}" class="toast-view-link">
                    <span>عرض التفاصيل الكاملة</span>
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                </a>
            </div>
            <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()" title="إغلاق">&times;</button>
        `;

        container.appendChild(toast);

        // Auto remove toast after 7 seconds
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 400);
            }
        }, 7000);
    }

    function prependNewActivityToSlideshow(item) {
        const stage = document.getElementById('sliderStage');
        const thumbsTrack = document.getElementById('sliderThumbsTrack');
        if (!stage || !thumbsTrack) return;

        // Create new slide element
        const newSlide = document.createElement('article');
        newSlide.className = 'slide-item is-active';
        newSlide.setAttribute('data-activity-id', item.id);

        const mediaHtml = item.image_url ? `
            <div class="slide-ambient-aura" style="background-image: url('${item.image_url}');"></div>
            <img src="${item.image_url}" alt="${item.title}" class="slide-img">
        ` : `
            <div class="slide-media-placeholder">
                <svg style="width: 58px; height: 58px; margin: 0 auto 10px auto; display: block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                </svg>
                <span style="font-size: 0.85rem;">الشركة العامة لموانئ العراق</span>
            </div>
        `;

        const urgentHtml = item.importance === 'urgent' ? '<span class="badge badge-urgent">عاجل وهام</span>' : '';
        const photosBadge = item.images_count > 1 ? `
            <div class="slide-badge-photos">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                <span>${item.images_count} صور موثقة</span>
            </div>
        ` : '';

        newSlide.innerHTML = `
            <div class="slide-media-wrap">
                ${mediaHtml}
                ${photosBadge}
            </div>
            <div class="slide-info-wrap">
                <div class="slide-top-meta">
                    <span class="slide-index-tag">نشاط #01 (أحدث إضافة)</span>
                    <span class="badge badge-category">${item.category}</span>
                    <span class="badge badge-live-now">جديد الآن</span>
                    ${urgentHtml}
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        ${item.formatted_date}
                    </span>
                    ${item.location ? `
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                            <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            ${item.location}
                        </span>
                    ` : ''}
                </div>
                <h3 class="slide-headline">
                    <a href="${item.url}">${item.title}</a>
                </h3>
                <p class="slide-excerpt">${item.short_description}</p>
                <div class="slide-bottom-bar">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg style="width:16px;height:16px;color:#059669;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        <span>تغطية إعلامية رسمية معتمدة</span>
                    </span>
                    <a href="${item.url}" class="btn-read-more">
                        <span>اقرأ التفاصيل الكاملة</span>
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            </div>
        `;

        // Deactivate old active slide
        document.querySelectorAll('.slide-item').forEach(s => s.classList.remove('is-active'));
        stage.prepend(newSlide);

        // Create new thumbnail
        const newThumb = document.createElement('li');
        newThumb.className = 'slider-thumb-item is-active';
        newThumb.setAttribute('data-activity-id', item.id);
        newThumb.onclick = () => switchSlide(0);
        newThumb.innerHTML = `
            <div class="thumb-mini-header">
                <span>#01</span>
                <span>${item.category}</span>
            </div>
            <div class="thumb-img-box">
                ${item.image_url ? `<img src="${item.image_url}" alt="" class="thumb-img">` : `
                    <svg style="width: 22px; height: 22px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                `}
            </div>
            <span class="thumb-caption" title="${item.title}">${item.title}</span>
        `;

        document.querySelectorAll('.slider-thumb-item').forEach(t => t.classList.remove('is-active'));
        thumbsTrack.prepend(newThumb);

        // Keep strictly 10 items in slider
        const allSlides = getSlides();
        const allThumbs = getThumbs();
        if (allSlides.length > 10) {
            allSlides[allSlides.length - 1].remove();
        }
        if (allThumbs.length > 10) {
            allThumbs[allThumbs.length - 1].remove();
        }

        // Re-index remaining thumbnails
        getThumbs().forEach((t, i) => {
            const hSpan = t.querySelector('.thumb-mini-header span:first-child');
            if (hSpan) hSpan.innerText = '#' + String(i + 1).padStart(2, '0');
            t.onclick = () => switchSlide(i);
        });

        currentSlide = 0;
        updateSliderCounter(0, Math.min(allSlides.length, 10));
        resetProgressBar();
    }

    function rebuildSlideshowFromFallback(items) {
        const stage = document.getElementById('sliderStage');
        const thumbsTrack = document.getElementById('sliderThumbsTrack');
        if (!stage || !thumbsTrack || !items || items.length === 0) return;

        let slidesHtml = '';
        let thumbsHtml = '';

        items.forEach((item, idx) => {
            const isActive = idx === 0 ? 'is-active' : '';
            const mediaHtml = item.image_url ? `
                <div class="slide-ambient-aura" style="background-image: url('${item.image_url}');"></div>
                <img src="${item.image_url}" alt="${item.title}" class="slide-img">
            ` : `
                <div class="slide-media-placeholder">
                    <svg style="width: 58px; height: 58px; margin: 0 auto 10px auto; display: block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                    </svg>
                    <span style="font-size: 0.85rem;">الشركة العامة لموانئ العراق</span>
                </div>
            `;

            const urgentHtml = item.importance === 'urgent' ? '<span class="badge badge-urgent">عاجل وهام</span>' : (item.importance === 'important' ? '<span class="badge badge-important">مهم</span>' : '');
            const photosBadge = item.images_count > 1 ? `
                <div class="slide-badge-photos">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    <span>${item.images_count} صور موثقة</span>
                </div>
            ` : '';

            slidesHtml += `
                <article class="slide-item ${isActive}" data-slide-index="${idx}" data-activity-id="${item.id}">
                    <div class="slide-media-wrap">
                        ${mediaHtml}
                        ${photosBadge}
                    </div>
                    <div class="slide-info-wrap">
                        <div class="slide-top-meta">
                            <span class="slide-index-tag">نشاط #${String(idx + 1).padStart(2, '0')} من ${String(items.length).padStart(2, '0')}</span>
                            <span class="badge badge-category">${item.category}</span>
                            ${urgentHtml}
                            <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                ${item.formatted_date}
                            </span>
                            ${item.location ? `
                                <span style="font-size: 0.85rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                    <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    ${item.location}
                                </span>
                            ` : ''}
                        </div>
                        <h3 class="slide-headline">
                            <a href="${item.url}">${item.title}</a>
                        </h3>
                        <p class="slide-excerpt">${item.short_description}</p>
                        <div class="slide-bottom-bar">
                            <span style="display: inline-flex; align-items: center; gap: 6px;">
                                <svg style="width:16px;height:16px;color:#059669;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                <span>تغطية إعلامية رسمية معتمدة</span>
                            </span>
                            <a href="${item.url}" class="btn-read-more">
                                <span>اقرأ التفاصيل الكاملة</span>
                                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            `;

            thumbsHtml += `
                <li class="slider-thumb-item ${isActive}" data-thumb-index="${idx}" data-activity-id="${item.id}" onclick="switchSlide(${idx})">
                    <div class="thumb-mini-header">
                        <span>#${String(idx + 1).padStart(2, '0')}</span>
                        <span>${item.category}</span>
                    </div>
                    <div class="thumb-img-box">
                        ${item.image_url ? `<img src="${item.image_url}" alt="" class="thumb-img">` : `
                            <svg style="width: 22px; height: 22px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                        `}
                    </div>
                    <span class="thumb-caption" title="${item.title}">${item.title}</span>
                </li>
            `;
        });

        stage.innerHTML = slidesHtml;
        thumbsTrack.innerHTML = thumbsHtml;
        currentSlide = 0;
        updateSliderCounter(0, items.length);
        resetProgressBar();
    }

    function prependNewActivityToGrid(item) {
        const grid = document.getElementById('newsGridContainer');
        if (!grid) return;

        // Check if card already exists
        if (grid.querySelector(`[data-activity-id="${item.id}"]`)) return;

        const card = document.createElement('article');
        card.className = 'news-card just-added';
        card.setAttribute('data-activity-id', item.id);

        const cardMedia = item.image_url ? `
            <img src="${item.image_url}" alt="${item.title}" class="news-card-img" loading="lazy">
        ` : `
            <div class="news-card-placeholder">
                <svg style="width: 34px; height: 34px; color: #94a3b8; margin: 0 auto 6px auto; display: block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                </svg>
                <span style="font-size: 0.75rem;">الشركة العامة لموانئ العراق</span>
            </div>
        `;

        const urgentBadge = item.importance === 'urgent' ? '<span class="badge badge-urgent">عاجل</span>' : '';

        card.innerHTML = `
            <div class="news-card-media">
                ${cardMedia}
            </div>
            <div class="news-card-body">
                <div class="news-card-badges-row">
                    <span class="badge badge-category">${item.category}</span>
                    <span class="badge badge-live-now">جديد الآن</span>
                    ${urgentBadge}
                </div>
                <div class="news-meta-row">
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        ${item.formatted_date}
                    </span>
                    ${item.location ? `
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            ${item.location}
                        </span>
                    ` : ''}
                </div>
                <h4 class="news-card-title">
                    <a href="${item.url}">${item.title}</a>
                </h4>
                <p class="news-card-excerpt">${item.short_description}</p>
                <div class="news-card-footer">
                    <span style="color: var(--text-muted); font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                        <span>${item.images_count} مرفق</span>
                    </span>
                    <a href="${item.url}" class="read-article-link">
                        <span>التفاصيل</span>
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            </div>
        `;

        grid.prepend(card);

        // Update counts
        const totalCountEl = document.getElementById('newsGridTotalCount');
        if (totalCountEl) {
            const currentTotal = parseInt(totalCountEl.innerText) || 0;
            totalCountEl.innerText = currentTotal + 1;
        }

        const publicBadge = document.getElementById('totalPublicBadge');
        if (publicBadge) {
            const currentPublic = parseInt(publicBadge.innerText) || 0;
            publicBadge.innerText = currentPublic + 1;
        }
    }

    function prependNewActivityToTicker(item) {
        const groups = document.querySelectorAll('#breakingTickerTrack .ticker-track-group');
        if (!groups.length) return;

        groups.forEach(group => {
            if (group.querySelector(`[data-ticker-id="${item.id}"]`)) return;

            const link = document.createElement('a');
            link.href = item.url;
            link.className = 'ticker-item';
            link.setAttribute('data-ticker-id', item.id);
            link.innerHTML = `<span style="color: #facc15;">&bull;</span><span>${item.title} (${item.formatted_date})</span>`;

            group.prepend(link);

            // الحفاظ على آخر 10 أخبار فقط في الشريط
            const allItems = group.querySelectorAll('.ticker-item');
            if (allItems.length > 10) {
                allItems[allItems.length - 1].remove();
            }
        });
    }

    function rebuildTickerFromFallback(items) {
        const groups = document.querySelectorAll('#breakingTickerTrack .ticker-track-group');
        if (!groups.length || !items || !items.length) return;

        let html = '';
        items.slice(0, 10).forEach(item => {
            html += `<a href="${item.url}" class="ticker-item" data-ticker-id="${item.id}"><span style="color: #facc15;">&bull;</span><span>${item.title} (${item.formatted_date})</span></a>`;
        });

        groups.forEach(group => {
            group.innerHTML = html;
        });
    }

    function checkLiveUpdates() {
        // جمع كافة معرفات الأنشطة المعروضة حالياً على شاشة المستخدم
        const visibleElements = document.querySelectorAll('[data-activity-id]');
        const visibleIds = Array.from(visibleElements).map(el => el.getAttribute('data-activity-id')).filter(Boolean);
        const pulseUrl = '{{ route("news.pulse") }}?last_id=' + encodeURIComponent(window.LATEST_ACTIVITY_ID) + '&visible_ids=' + encodeURIComponent(visibleIds.join(','));

        fetch(pulseUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (!data.has_updates) return;

            // 1. معالجة الأنشطة المحذوفة أو التي تم تحويلها إلى سرية
            if (data.deleted_ids && data.deleted_ids.length > 0) {
                data.deleted_ids.forEach(delId => {
                    // إخفاء وحذف البطاقة من شبكة الأخبار بانسيابية
                    const gridCards = document.querySelectorAll(`.news-card[data-activity-id="${delId}"]`);
                    gridCards.forEach(c => {
                        c.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        c.style.opacity = '0';
                        c.style.transform = 'scale(0.85)';
                        setTimeout(() => c.remove(), 400);
                    });
                });

                // إذا توفرت بيانات السلايد شو البديلة المحدثة، نعيد بناء السلايدر والشريط بدقة ومباشرة
                if (data.slider_fallback && data.slider_fallback.length > 0) {
                    rebuildSlideshowFromFallback(data.slider_fallback);
                    rebuildTickerFromFallback(data.slider_fallback);
                } else {
                    // أو نحذف العناصر التي تحمل المعرف المحذوف
                    data.deleted_ids.forEach(delId => {
                        document.querySelectorAll(`.slide-item[data-activity-id="${delId}"]`).forEach(s => s.remove());
                        document.querySelectorAll(`.slider-thumb-item[data-activity-id="${delId}"]`).forEach(t => t.remove());
                        document.querySelectorAll(`.ticker-item[data-ticker-id="${delId}"]`).forEach(tk => tk.remove());
                    });
                    const allSlides = getSlides();
                    const allThumbs = getThumbs();
                    if (allSlides.length > 0) {
                        if (!document.querySelector('.slide-item.is-active')) {
                            allSlides[0].classList.add('is-active');
                            if (allThumbs[0]) allThumbs[0].classList.add('is-active');
                            currentSlide = 0;
                        }
                        allThumbs.forEach((t, i) => {
                            const hSpan = t.querySelector('.thumb-mini-header span:first-child');
                            if (hSpan) hSpan.innerText = '#' + String(i + 1).padStart(2, '0');
                            t.onclick = () => switchSlide(i);
                        });
                        updateSliderCounter(currentSlide, allSlides.length);
                    }
                }
            }

            // 2. تحديث العدادات الإجمالية للأنشطة
            if (data.total_count !== undefined) {
                const totalCountEl = document.getElementById('newsGridTotalCount');
                if (totalCountEl) totalCountEl.innerText = data.total_count;

                const publicBadge = document.getElementById('totalPublicBadge');
                if (publicBadge) publicBadge.innerText = data.total_count;
            }

            // 3. معالجة الأنشطة الجديدة المضافة
            if (data.items && data.items.length > 0) {
                window.LATEST_ACTIVITY_ID = data.latest_id;

                data.items.forEach(item => {
                    showLiveToast(item);
                    prependNewActivityToSlideshow(item);
                    prependNewActivityToGrid(item);
                    prependNewActivityToTicker(item);
                });
            }
        })
        .catch(err => {
            // تجاهل أي انقطاع مؤقت بالشبكة
        });
    }

    // Ports Dashboard Tab Switching
    document.querySelectorAll('.ports-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');

            document.querySelectorAll('.ports-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.ports-tab-pane').forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.getElementById(targetTab);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // Start live background pulse
    livePollTimer = setInterval(checkLiveUpdates, LIVE_POLL_INTERVAL);
</script>
@endpush
