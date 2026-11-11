@extends('layouts.news')

@section('title', $activity->title . ' - الشركة العامة لموانئ العراق')
@section('meta_description', Str::limit(strip_tags($activity->short_description), 160))

@push('styles')
<style>
    .article-container {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 32px;
    }

    /* Breadcrumbs */
    .breadcrumbs-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .breadcrumbs-bar a:hover {
        color: var(--primary);
        text-decoration: underline;
    }

    /* Main Article */
    .article-main {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 36px 40px;
    }

    .article-header {
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 20px;
    }

    .article-badges-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .article-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.85rem;
        font-weight: 900;
        line-height: 1.35;
        color: var(--primary-dark);
        margin-bottom: 16px;
    }

    /* Meta Information Box */
    .article-meta-box {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-right: 4px solid var(--primary);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-top: 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px 24px;
    }

    .meta-item {
        display: inline-flex;
        align-items: baseline;
        gap: 6px;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .meta-icon {
        font-size: 1rem;
        flex-shrink: 0;
    }

    .meta-label {
        color: var(--text-muted);
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .meta-value {
        color: var(--text-main);
        font-weight: 700;
    }

    .meta-row-attendees {
        padding-top: 12px;
        border-top: 1px dashed var(--border-color);
    }

    /* Main Featured Media */
    .article-featured-media {
        background: #f1f5f9;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-bottom: 28px;
        text-align: center;
    }

    .article-featured-img-box {
        height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
    }

    .article-featured-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .article-featured-img:hover {
        transform: scale(1.01);
    }

    .featured-media-caption {
        background: #ffffff;
        padding: 10px 16px;
        font-size: 0.85rem;
        color: var(--text-muted);
        border-top: 1px solid var(--border-color);
        font-weight: 600;
    }

    /* Article Content */
    .article-body {
        font-size: 1.05rem;
        line-height: 1.85;
        color: #1e293b;
        margin-bottom: 30px;
    }

    .article-body p {
        margin-bottom: 16px;
    }

    /* Directives Box */
    .directives-box {
        background: #f0fdf4;
        border-right: 4px solid var(--success-green);
        border-top: 1px solid #bbf7d0;
        border-bottom: 1px solid #bbf7d0;
        border-left: 1px solid #bbf7d0;
        border-radius: 0 var(--radius-md) var(--radius-md) 0;
        padding: 20px 24px;
        margin-bottom: 32px;
    }

    .directives-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: #166534;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .directives-content {
        color: #14532d;
        font-size: 0.98rem;
        line-height: 1.7;
    }

    /* Gallery Section */
    .article-gallery-section {
        margin-top: 36px;
        padding-top: 24px;
        border-top: 1px solid var(--border-color);
    }

    .gallery-section-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    .gallery-thumb-card {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
    }

    .gallery-thumb-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
    }

    .gallery-thumb-box {
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        overflow: hidden;
    }

    .gallery-thumb-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .gallery-thumb-caption {
        font-size: 0.78rem;
        color: var(--text-muted);
        padding: 6px 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        background: #ffffff;
    }

    /* Article Actions Bar */
    .article-actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        margin-top: 32px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 12px;
    }

    .btn-pdf-export {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: var(--primary-dark);
        padding: 8px 18px;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
    }

    .btn-pdf-export:hover {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    .share-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .share-btn {
        background: #f1f5f9;
        border: 1px solid var(--border-color);
        padding: 6px 14px;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-muted);
        cursor: pointer;
        transition: var(--transition);
    }

    .share-btn:hover {
        background: var(--primary);
        color: #ffffff;
    }

    /* Sidebar */
    .article-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .sidebar-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        padding: 22px 24px;
    }

    .sidebar-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 16px;
        position: relative;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar-title::after {
        content: '';
        position: absolute;
        bottom: -1px;
        right: 0;
        width: 35px;
        height: 2px;
        background: var(--primary);
    }

    .related-news-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .related-news-item {
        display: flex;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px dashed var(--border-color);
    }

    .related-news-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .related-news-thumb {
        width: 80px;
        height: 70px;
        border-radius: var(--radius-sm);
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .related-news-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .related-news-info h5 {
        font-family: 'Cairo', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--primary-dark);
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .related-news-info h5 a:hover {
        color: var(--primary);
    }

    .related-news-date {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    @media (max-width: 900px) {
        .article-container {
            grid-template-columns: 1fr;
        }
        .article-main {
            padding: 24px 20px;
        }
        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="container">

    <!-- مسار التنقل (Breadcrumbs) -->
    <nav class="breadcrumbs-bar" aria-label="مسار التنقل">
        <a href="{{ route('news.index') }}">الرئيسية</a>
        <span>&rsaquo;</span>
        <a href="{{ route('news.index', ['category' => $activity->category]) }}">
            {{ $activity->category }}
        </a>
        <span>&rsaquo;</span>
        <span>تغطية النشاط</span>
    </nav>

    <div class="article-container">

        <!-- العمود الرئيسي للخبر -->
        <article class="article-main">

            <header class="article-header">
                <div class="article-badges-row">
                    <span class="badge badge-category">{{ $activity->category }}</span>
                    @if($activity->importance === 'urgent')
                        <span class="badge badge-urgent">عاجل جداً</span>
                    @elseif($activity->importance === 'important')
                        <span class="badge badge-important">مهم</span>
                    @else
                        <span class="badge badge-routine">عادي</span>
                    @endif
                    <span style="font-size: 0.85rem; color: var(--text-muted); margin-right: auto;">
                        رقم التوثيق: #{{ $activity->id }}
                    </span>
                </div>

                <h1 class="article-title">{{ $activity->title }}</h1>

                <div class="article-meta-box">
                    <div class="meta-row">
                        <div class="meta-item">
                            <x-heroicon-o-calendar class="meta-icon" style="width: 18px; height: 18px; color: var(--primary);" />
                            <span class="meta-label">التاريخ:</span>
                            <span class="meta-value">{{ $activity->formatted_date }}</span>
                        </div>
                        @if($activity->activity_time)
                            <div class="meta-item">
                                <x-heroicon-o-clock class="meta-icon" style="width: 18px; height: 18px; color: var(--primary);" />
                                <span class="meta-label">الوقت:</span>
                                <span class="meta-value">{{ date('h:i A', strtotime($activity->activity_time)) }}</span>
                            </div>
                        @endif
                        @if($activity->location)
                            <div class="meta-item">
                                <x-heroicon-o-map-pin class="meta-icon" style="width: 18px; height: 18px; color: var(--primary);" />
                                <span class="meta-label">الموقع / المقر:</span>
                                <span class="meta-value">{{ $activity->location }}</span>
                            </div>
                        @endif
                    </div>

                    @if($activity->attendees)
                        <div class="meta-row meta-row-attendees">
                            <div class="meta-item">
                                <x-heroicon-o-user-group class="meta-icon" style="width: 18px; height: 18px; color: var(--primary);" />
                                <span class="meta-label">المشاركون / الوفد:</span>
                                <span class="meta-value">{{ $activity->attendees }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            <!-- الصورة البارزة الرئيسية -->
            @php
                $mainImg = $activity->primaryAttachment ?: $activity->imageAttachments->first();
            @endphp

            @if($mainImg && $mainImg->base64_data_uri)
                <div class="article-featured-media">
                    <div class="article-featured-img-box">
                        <img
                            src="{{ $mainImg->base64_data_uri }}"
                            alt="{{ $activity->title }}"
                            class="article-featured-img"
                            onclick="openLightbox('{{ $mainImg->base64_data_uri }}', '{{ $mainImg->caption ?: $activity->title }}')"
                        >
                    </div>
                    @if(filled($mainImg->caption))
                        <div class="featured-media-caption">
                            <x-heroicon-o-camera style="width: 16px; height: 16px; display: inline-block; vertical-align: -2px; color: var(--primary);" />
                            <span>{{ $mainImg->caption }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <!-- نص وتفاصيل الخبر والموجز -->
            <div class="article-body">
                {!! nl2br(e($activity->short_description)) !!}
            </div>

            <!-- صندوق القرارات والتوجيهات إن وجدت -->
            @if($activity->detailed_notes)
                <div class="directives-box">
                    <div class="directives-title">
                        <x-heroicon-o-document-check style="width: 22px; height: 22px; color: var(--success-green); flex-shrink: 0;" />
                        <span>القرارات والمخرجات والتوجيهات الصادرة:</span>
                    </div>
                    <div class="directives-content">
                        {!! nl2br(e($activity->detailed_notes)) !!}
                    </div>
                </div>
            @endif

            <!-- معرض الصور الموثقة للنشاط -->
            @if($activity->imageAttachments->count() > 1)
                <section class="article-gallery-section">
                    <h3 class="gallery-section-title">
                        <x-heroicon-o-photo style="width: 22px; height: 22px; color: var(--primary); flex-shrink: 0;" />
                        <span>التوثيق المصور للنشاط ({{ $activity->imageAttachments->count() }} صورة)</span>
                    </h3>
                    <div class="gallery-grid">
                        @foreach($activity->imageAttachments as $galleryImg)
                            @if($galleryImg->base64_data_uri)
                                <div
                                    class="gallery-thumb-card"
                                    onclick="openLightbox('{{ $galleryImg->base64_data_uri }}', '{{ $galleryImg->caption ?: $activity->title }}')"
                                    title="انقر لتكبير الصورة"
                                >
                                    <div class="gallery-thumb-box">
                                        <img
                                            src="{{ $galleryImg->base64_data_uri }}"
                                            alt="{{ $galleryImg->caption ?: 'صورة توثيقية' }}"
                                            class="gallery-thumb-img"
                                            loading="lazy"
                                        >
                                    </div>
                                    @if(filled($galleryImg->caption))
                                        <div class="gallery-thumb-caption">{{ $galleryImg->caption }}</div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- شريط الأزرار والمشاركة والـ PDF -->
            <div class="article-actions-bar">
                <div style="display: flex; gap: 10px; align-items: center;">
                    @auth
                        <a href="{{ route('activities.single.pdf', $activity->id) }}" target="_blank" class="btn-pdf-export">
                            <x-heroicon-o-arrow-down-tray style="width: 16px; height: 16px;" />
                            <span>تحميل بطاقة النشاط الرسمية PDF</span>
                        </a>
                    @else
                        <span style="font-size: 0.82rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 5px;">
                            <x-heroicon-o-building-office-2 style="width: 16px; height: 16px;" />
                            <span>الجهة الناشرة: الشركة العامة لموانئ العراق</span>
                        </span>
                    @endauth
                </div>

                <div class="share-buttons">
                    <button type="button" class="share-btn" onclick="copyCurrentUrl()" style="display: inline-flex; align-items: center; gap: 5px;">
                        <x-heroicon-o-clipboard-document style="width: 15px; height: 15px;" />
                        <span>نسخ الرابط</span>
                    </button>
                    <a
                        href="https://api.whatsapp.com/send?text={{ urlencode($activity->title . ' - ' . url()->current()) }}"
                        target="_blank"
                        class="share-btn"
                        style="color: #15803d; display: inline-flex; align-items: center; gap: 5px;"
                    >
                        <svg style="width: 15px; height: 15px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm0 18.15c-1.49 0-2.95-.4-4.22-1.16l-.3-.18-3.13.82.84-3.05-.2-.31a8.19 8.19 0 0 1-1.26-4.36c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.83 2.42a8.18 8.18 0 0 1 2.42 5.83c0 4.55-3.7 8.24-8.23 8.24zm4.52-6.17c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.39-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.12.17 1.77 2.7 4.29 3.78.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.31z"/>
                        </svg>
                        <span>واتساب</span>
                    </a>
                </div>
            </div>

        </article>

        <!-- الشريط الجانبي (أخبار ذات صلة ومعلومات) -->
        <aside class="article-sidebar">

            @if(isset($relatedActivities) && $relatedActivities->isNotEmpty())
                <div class="sidebar-card">
                    <h4 class="sidebar-title">نشاطات ذات صلة</h4>
                    <div class="related-news-list">
                        @foreach($relatedActivities as $rel)
                            @php
                                $relImg = $rel->primaryAttachment ?: $rel->imageAttachments->first();
                            @endphp
                            <div class="related-news-item">
                                <div class="related-news-thumb">
                                    @if($relImg && $relImg->base64_data_uri)
                                        <img src="{{ $relImg->base64_data_uri }}" alt="{{ $rel->title }}" loading="lazy">
                                    @else
                                        <svg style="width: 28px; height: 28px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="5" r="3"/>
                                            <line x1="12" y1="22" x2="12" y2="8"/>
                                            <path d="M5 12H2a10 10 0 0 0 20 0h-3"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="related-news-info">
                                    <h5>
                                        <a href="{{ route('news.show', $rel->id) }}">
                                            {{ $rel->title }}
                                        </a>
                                    </h5>
                                    <div class="related-news-date" style="display: flex; align-items: center; gap: 4px;">
                                        <x-heroicon-o-calendar style="width: 13px; height: 13px;" />
                                        <span>{{ $rel->formatted_date }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="sidebar-card" style="background: linear-gradient(135deg, #0d1b2a 0%, #1e3a8a 100%); color: #ffffff;">
                <h4 class="sidebar-title" style="color: #ffffff; border-bottom-color: rgba(255,255,255,0.15);">
                    الشركة العامة لموانئ العراق
                </h4>
                <p style="font-size: 0.88rem; line-height: 1.7; color: #cbd5e1; margin-bottom: 16px;">
                    منظومة الموانئ البحرية العراقية: ميناء أم قصر الشمالي والجنوبي، ميناء خور الزبير، ميناء أبو فلوس، وميناء الفاو الكبير.
                </p>
                <a href="{{ route('news.index') }}" style="display: inline-flex; align-items: center; gap: 6px; background: var(--accent-gold); color: #ffffff; padding: 7px 18px; border-radius: var(--radius-full); font-size: 0.85rem; font-weight: 700;">
                    <span>تصفح كافة الأخبار</span>
                    <x-heroicon-o-arrow-left style="width: 15px; height: 15px;" />
                </a>
            </div>

        </aside>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyCurrentUrl() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('تم نسخ رابط التغطية بنجاح إلى الحافظة!');
        }).catch(function() {
            prompt('انسخ الرابط التالي:', window.location.href);
        });
    }
</script>
@endpush
