<x-filament-panels::page>
<style>
    /* ── Page-level full width ── */
    .fi-page-content { width: 100% !important; }

    /* ── Outer Card ── */
    .pn-card {
        width: 100%;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-radius: 1.375rem;
        border: 1px solid rgba(239,68,68,0.13);
        box-shadow: 0 4px 28px -4px rgba(0,0,0,0.07), 0 0 24px rgba(239,68,68,0.07);
        overflow: hidden;
        position: relative;
    }
    .dark .pn-card {
        background: rgba(15,20,35,0.92);
        border-color: rgba(239,68,68,0.2);
        box-shadow: 0 4px 28px -4px rgba(0,0,0,0.4), 0 0 28px rgba(239,68,68,0.12);
    }
    .pn-card::before {
        content: '';
        position: absolute;
        top: 0; inset-inline-start: 0; inset-inline-end: 0;
        height: 3px;
        background: linear-gradient(90deg, #b91c1c, #ef4444, #f87171, #ef4444, #b91c1c);
        background-size: 300% 100%;
        animation: pn-bar-slide 4s linear infinite;
        z-index: 5;
    }
    @keyframes pn-bar-slide {
        0%   { background-position: 0% 0%; }
        100% { background-position: 300% 0%; }
    }

    /* ── Banner ── */
    .pn-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.375rem 1.5rem 1.25rem;
        background: linear-gradient(110deg, rgba(239,68,68,0.07) 0%, rgba(239,68,68,0.01) 70%);
        border-bottom: 1px solid rgba(239,68,68,0.08);
        position: relative;
        overflow: hidden;
    }
    .dark .pn-banner {
        background: linear-gradient(110deg, rgba(239,68,68,0.13) 0%, rgba(239,68,68,0.02) 70%);
        border-bottom-color: rgba(239,68,68,0.12);
    }
    /* floating orb */
    .pn-banner::before {
        content: '';
        position: absolute;
        inset-inline-end: -2rem; top: -2rem;
        width: 10rem; height: 10rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(239,68,68,0.12) 0%, transparent 70%);
        pointer-events: none;
        animation: pn-orb 6s ease-in-out infinite;
    }
    @keyframes pn-orb {
        0%, 100% { transform: translateY(0) scale(1); }
        50%       { transform: translateY(6px) scale(1.08); }
    }
    .pn-banner::after {
        content: '';
        position: absolute;
        inset-inline-end: 0;
        top: 0;
        width: 40%;
        height: 100%;
        background-image: radial-gradient(circle, rgba(239,68,68,0.12) 1px, transparent 1px);
        background-size: 14px 14px;
        pointer-events: none;
    }
    .pn-banner-icon {
        width: 3rem;
        height: 3rem;
        flex-shrink: 0;
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 20px rgba(239,68,68,0.5), 0 4px 12px rgba(0,0,0,0.15);
        position: relative;
        z-index: 1;
    }
    .pn-banner-icon::after {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 1.25rem;
        border: 2px solid rgba(239,68,68,0.35);
        animation: pn-ring 2.5s ease-in-out infinite;
    }
    @keyframes pn-ring {
        0%, 100% { opacity: 0.7; transform: scale(1); }
        50%       { opacity: 0;   transform: scale(1.28); }
    }
    .pn-banner-text { position: relative; z-index: 1; }
    .pn-banner-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }
    .dark .pn-banner-title { color: #f9fafb; }
    .pn-banner-sub {
        font-size: 0.775rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }
    .dark .pn-banner-sub { color: #9ca3af; }

    /* ── Card Body ── */
    .pn-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }

    /* ── Info notice ── */
    .pn-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        padding: 0.8rem 1rem;
        background: rgba(239,68,68,0.04);
        border: 1px solid rgba(239,68,68,0.12);
        border-radius: 0.875rem;
        margin-bottom: 1.5rem;
    }
    .dark .pn-notice {
        background: rgba(239,68,68,0.08);
        border-color: rgba(239,68,68,0.18);
    }
    .pn-notice-icon {
        width: 1rem; height: 1rem;
        color: #ef4444;
        flex-shrink: 0;
        margin-top: 0.1rem;
        filter: drop-shadow(0 0 4px rgba(239,68,68,0.4));
    }
    .pn-notice-text {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.6;
    }
    .dark .pn-notice-text { color: #9ca3af; }

    /* ── Strip inner Filament section card ── */
    .pn-body .fi-section {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
    }
    .pn-body .fi-section-header { display: none !important; }
    .pn-body .fi-section-content-ctn { padding: 0 !important; border: none !important; }
    .pn-body .fi-section-content { padding: 0 !important; }
    /* save row */
    .pn-save-row { display: flex; justify-content: flex-end; }
</style>

<div class="pn-card">
    <div class="pn-banner">
        <div class="pn-banner-icon">
            <x-heroicon-o-device-phone-mobile class="w-5 h-5 text-white" />
        </div>
        <div class="pn-banner-text">
            <p class="pn-banner-title">رقم الجوال</p>
            <p class="pn-banner-sub">تأكد من إدخال رقم جوال صحيح وفعّال للتحقق من هويتك</p>
        </div>
    </div>
    <div class="pn-body">
        <form wire:submit="save">
            <div class="pn-body">
                <div>{{ $this->form }}</div>
                <div class="pn-save-row">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle">
                        حفظ التغييرات
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-filament-panels::page>