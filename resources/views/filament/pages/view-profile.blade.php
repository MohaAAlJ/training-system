{{-- Profile info card — animated infolist tiles --}}
<x-filament-panels::page>
<style>
    /* ── Page-level full width ── */
    .fi-page-content { width: 100% !important; }

    /* ── Outer Card ── */
    .vp-card {
        width: 100%;
        background: #ffffff;
        border-radius: 1.5rem;
        border: 1px solid rgba(239,68,68,0.12);
        box-shadow: 0 8px 40px -8px rgba(0,0,0,0.08), 0 0 0 1px rgba(239,68,68,0.05);
        overflow: hidden;
        position: relative;
    }
    .dark .vp-card {
        background: rgb(17,24,39);
        border-color: rgba(239,68,68,0.18);
        box-shadow: 0 8px 40px -8px rgba(0,0,0,0.5), 0 0 0 1px rgba(239,68,68,0.08);
    }
    /* Animated top bar */
    .vp-card::before {
        content: '';
        position: absolute;
        top: 0; inset-inline-start: 0; inset-inline-end: 0;
        height: 3px;
        background: linear-gradient(90deg, #b91c1c, #ef4444, #f87171, #ef4444, #b91c1c);
        background-size: 300% 100%;
        animation: vp-bar-slide 4s linear infinite;
        z-index: 5;
    }
    @keyframes vp-bar-slide {
        0%   { background-position: 0% 0%; }
        100% { background-position: 300% 0%; }
    }

    /* ── Banner ── */
    .vp-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 1.75rem;
        background: linear-gradient(135deg, rgba(239,68,68,0.06) 0%, rgba(239,68,68,0.01) 60%, transparent 100%);
        border-bottom: 1px solid rgba(239,68,68,0.08);
        position: relative;
        overflow: hidden;
    }
    .dark .vp-banner {
        background: linear-gradient(135deg, rgba(239,68,68,0.11) 0%, rgba(0,0,0,0) 60%);
        border-bottom-color: rgba(239,68,68,0.11);
    }
    /* Floating orb decoration */
    .vp-banner::before {
        content: '';
        position: absolute;
        inset-inline-end: -2rem;
        top: -2rem;
        width: 10rem;
        height: 10rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(239,68,68,0.12) 0%, transparent 70%);
        pointer-events: none;
        animation: vp-orb-float 6s ease-in-out infinite;
    }
    @keyframes vp-orb-float {
        0%, 100% { transform: translateY(0) scale(1); }
        50%       { transform: translateY(6px) scale(1.08); }
    }
    /* Dot-grid */
    .vp-banner::after {
        content: '';
        position: absolute;
        inset-inline-end: 0; top: 0;
        width: 45%; height: 100%;
        background-image: radial-gradient(circle, rgba(239,68,68,0.1) 1px, transparent 1px);
        background-size: 15px 15px;
        pointer-events: none;
    }
    /* Banner icon */
    .vp-banner-icon {
        width: 3rem; height: 3rem;
        flex-shrink: 0;
        border-radius: 1rem;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 0 20px rgba(239,68,68,0.5), 0 4px 12px rgba(0,0,0,0.15);
        position: relative; z-index: 1;
    }
    .vp-banner-icon::after {
        content: '';
        position: absolute; inset: -5px;
        border-radius: 1.25rem;
        border: 2px solid rgba(239,68,68,0.35);
        animation: vp-ring 2.5s ease-in-out infinite;
    }
    @keyframes vp-ring {
        0%, 100% { opacity: 0.7; transform: scale(1); }
        50%       { opacity: 0;   transform: scale(1.28); }
    }
    .vp-banner-title {
        position: relative; z-index: 1;
        font-size: 1rem; font-weight: 700; color: #111827;
    }
    .dark .vp-banner-title { color: #f9fafb; }
    .vp-banner-sub {
        position: relative; z-index: 1;
        font-size: 0.78rem; color: #6b7280; margin-top: 0.1rem;
    }
    .dark .vp-banner-sub { color: #9ca3af; }
    /* Status badge */
    .vp-badge {
        margin-inline-start: auto;
        position: relative; z-index: 1;
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.3rem 0.8rem;
        border-radius: 999px;
        font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em;
        background: rgba(22,163,74,0.08);
        border: 1px solid rgba(22,163,74,0.28);
        color: #15803d;
    }
    .dark .vp-badge { background: rgba(22,163,74,0.13); border-color: rgba(22,163,74,0.38); color: #4ade80; }
    .vp-badge-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: #16a34a;
        box-shadow: 0 0 6px rgba(22,163,74,0.9);
        animation: vp-dot 2s ease-in-out infinite;
    }
    @keyframes vp-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.5); opacity: 0.5; }
    }

    /* ── Grid body ── */
    .vp-body { padding: 1.5rem; }
    .vp-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 540px) { .vp-grid { grid-template-columns: 1fr; } }

    /* ── Tile entrance animation ── */
    @keyframes vp-tile-in {
        from { opacity: 0; transform: translateY(14px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }
    .vp-tile {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.125rem 1.25rem;
        border-radius: 1rem;
        border: 1px solid rgba(0,0,0,0.06);
        background: rgba(249,250,251,0.9);
        position: relative; overflow: hidden;
        cursor: default;
        animation: vp-tile-in 0.45s cubic-bezier(.3,1.3,.5,1) both;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .dark .vp-tile {
        background: rgba(255,255,255,0.035);
        border-color: rgba(255,255,255,0.07);
    }
    /* Stagger delays */
    .vp-tile:nth-child(1) { animation-delay: 0.05s; }
    .vp-tile:nth-child(2) { animation-delay: 0.12s; }
    .vp-tile:nth-child(3) { animation-delay: 0.19s; }
    .vp-tile:nth-child(4) { animation-delay: 0.26s; }
    .vp-tile:nth-child(5) { animation-delay: 0.33s; }

    /* Shimmer sweep on hover */
    .vp-tile::after {
        content: '';
        position: absolute;
        top: 0; inset-inline-start: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.45) 60%, transparent 80%);
        transition: inset-inline-start 0.45s ease;
        pointer-events: none;
    }
    .dark .vp-tile::after {
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.07) 60%, transparent 80%);
    }
    .vp-tile:hover::after { inset-inline-start: 130%; }

    /* Colour accent bar on left/right edge */
    .vp-tile::before {
        content: '';
        position: absolute;
        inset-inline-start: 0; top: 0; bottom: 0;
        width: 3px;
        border-radius: 0 3px 3px 0;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .vp-tile.c-red::before    { background: linear-gradient(to bottom,#ef4444,#f87171); }
    .vp-tile.c-blue::before   { background: linear-gradient(to bottom,#3b82f6,#93c5fd); }
    .vp-tile.c-green::before  { background: linear-gradient(to bottom,#22c55e,#86efac); }
    .vp-tile.c-purple::before { background: linear-gradient(to bottom,#a855f7,#c084fc); }
    .vp-tile.c-amber::before  { background: linear-gradient(to bottom,#f59e0b,#fcd34d); }
    .vp-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px -6px rgba(0,0,0,0.1);
        border-color: rgba(239,68,68,0.15);
    }
    .vp-tile:hover::before { opacity: 1; }

    /* ── Tile icon with spinning gradient ring ── */
    .vp-icon-wrap {
        position: relative;
        width: 3rem; height: 3rem;
        flex-shrink: 0;
    }
    .vp-icon-ring {
        position: absolute; inset: -2px;
        border-radius: 1.1rem;
        background: conic-gradient(from 0deg, transparent 60%, currentColor 100%);
        opacity: 0;
        transition: opacity 0.25s ease;
        animation: vp-spin 3s linear infinite paused;
    }
    .vp-tile:hover .vp-icon-ring {
        opacity: 0.5;
        animation-play-state: running;
    }
    @keyframes vp-spin { to { transform: rotate(360deg); } }
    .vp-tile.c-red    .vp-icon-ring { color: #ef4444; }
    .vp-tile.c-blue   .vp-icon-ring { color: #3b82f6; }
    .vp-tile.c-green  .vp-icon-ring { color: #22c55e; }
    .vp-tile.c-purple .vp-icon-ring { color: #a855f7; }
    .vp-tile.c-amber  .vp-icon-ring { color: #f59e0b; }
    .vp-icon {
        position: relative; z-index: 1;
        width: 3rem; height: 3rem;
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.22s cubic-bezier(.3,1.5,.5,1);
    }
    .vp-tile:hover .vp-icon { transform: rotate(-8deg) scale(1.1); }
    .vp-icon svg { width: 1.35rem; height: 1.35rem; }
    /* icon colours */
    .vp-tile.c-red    .vp-icon { background:linear-gradient(135deg,#fef2f2,#fecaca); color:#ef4444; box-shadow:0 3px 12px rgba(239,68,68,0.2); }
    .vp-tile.c-blue   .vp-icon { background:linear-gradient(135deg,#eff6ff,#bfdbfe); color:#2563eb; box-shadow:0 3px 12px rgba(37,99,235,0.2); }
    .vp-tile.c-green  .vp-icon { background:linear-gradient(135deg,#f0fdf4,#bbf7d0); color:#16a34a; box-shadow:0 3px 12px rgba(22,163,74,0.2); }
    .vp-tile.c-purple .vp-icon { background:linear-gradient(135deg,#faf5ff,#e9d5ff); color:#9333ea; box-shadow:0 3px 12px rgba(147,51,234,0.2); }
    .vp-tile.c-amber  .vp-icon { background:linear-gradient(135deg,#fffbeb,#fde68a); color:#d97706; box-shadow:0 3px 12px rgba(217,119,6,0.2); }
    .dark .vp-tile.c-red    .vp-icon { background:rgba(239,68,68,0.14);  color:#f87171; box-shadow:none; }
    .dark .vp-tile.c-blue   .vp-icon { background:rgba(59,130,246,0.14); color:#93c5fd; box-shadow:none; }
    .dark .vp-tile.c-green  .vp-icon { background:rgba(34,197,94,0.14);  color:#86efac; box-shadow:none; }
    .dark .vp-tile.c-purple .vp-icon { background:rgba(168,85,247,0.14); color:#c084fc; box-shadow:none; }
    .dark .vp-tile.c-amber  .vp-icon { background:rgba(245,158,11,0.14); color:#fcd34d; box-shadow:none; }

    /* ── Tile text ── */
    .vp-tile-body { min-width: 0; flex: 1; }
    .vp-tile-label {
        font-size: 0.67rem; font-weight: 700;
        color: #9ca3af; text-transform: uppercase;
        letter-spacing: 0.07em; margin-bottom: 0.3rem;
        white-space: nowrap;
    }
    .dark .vp-tile-label { color: #6b7280; }
    .vp-tile-value {
        font-size: 0.95rem; font-weight: 600; color: #0f172a;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .dark .vp-tile-value { color: #f1f5f9; }
    .vp-tile-value.ltr { direction: ltr; text-align: end; }
    .vp-tile-sub {
        font-size: 0.72rem; font-weight: 500;
        color: #94a3b8; margin-top: 0.2rem;
        letter-spacing: 0.03em;
    }
    .dark .vp-tile-sub { color: #64748b; }
    .vp-tile-sub.ltr { direction: ltr; text-align: end; display: block; }
</style>

<div class="vp-card">
    {{-- Banner --}}
    <div class="vp-banner">
        <div class="vp-banner-icon">
            <x-heroicon-o-user class="w-5 h-5 text-white" />
        </div>
        <div>
            <p class="vp-banner-title">بيانات الحساب</p>
            <p class="vp-banner-sub">التفاصيل الخاصة بحسابك في النظام</p>
        </div>
        <span class="vp-badge">
            <span class="vp-badge-dot"></span>
            نشط
        </span>
    </div>

    {{-- Info Tiles --}}
    <div class="vp-body">
        <div class="vp-grid">

            {{-- Full Name --}}
            <div class="vp-tile c-red">
                <div class="vp-icon-wrap">
                    <div class="vp-icon-ring"></div>
                    <div class="vp-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                </div>
                <div class="vp-tile-body">
                    <p class="vp-tile-label">الاسم الكامل</p>
                    <p class="vp-tile-value">{{ $this->data['name'] ?? '' }}</p>
                </div>
            </div>

            {{-- Email --}}
            <div class="vp-tile c-blue">
                <div class="vp-icon-wrap">
                    <div class="vp-icon-ring"></div>
                    <div class="vp-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                </div>
                <div class="vp-tile-body">
                    <p class="vp-tile-label">البريد الإلكتروني</p>
                    <p class="vp-tile-value ltr">{{ $this->data['email'] ?? '' }}</p>
                </div>
            </div>

            {{-- Phone --}}
            <div class="vp-tile c-green">
                <div class="vp-icon-wrap">
                    <div class="vp-icon-ring"></div>
                    <div class="vp-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                        </svg>
                    </div>
                </div>
                <div class="vp-tile-body">
                    <p class="vp-tile-label">رقم الهاتف</p>
                    <p class="vp-tile-value ltr">{{ $this->data['phone_number'] ?? 'غير محدد' }}</p>
                </div>
            </div>

            {{-- Join Date --}}
            <div class="vp-tile c-amber">
                <div class="vp-icon-wrap">
                    <div class="vp-icon-ring"></div>
                    <div class="vp-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                </div>
                <div class="vp-tile-body">
                    <p class="vp-tile-label">تاريخ الانضمام</p>
                    <p class="vp-tile-value ltr">{{ $this->data['created_at'] ?? '' }}</p>
                    @if(!empty($this->data['created_at_time']))
                        <span class="vp-tile-sub ltr">{{ $this->data['created_at_time'] }}</span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
</x-filament-panels::page>
