<x-filament-panels::page>
<style>
    /* ── Page-level full width ── */
    .fi-page-content { width: 100% !important; }

    /* ── Outer Card ── */
    .cp-card {
        width: 100%;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-radius: 1.375rem;
        border: 1px solid rgba(239,68,68,0.13);
        box-shadow: 0 4px 28px -4px rgba(0,0,0,0.07), 0 0 24px rgba(239,68,68,0.07);
        overflow: hidden;
        position: relative;
    }
    .dark .cp-card {
        background: rgba(15,20,35,0.92);
        border-color: rgba(239,68,68,0.2);
        box-shadow: 0 4px 28px -4px rgba(0,0,0,0.4), 0 0 28px rgba(239,68,68,0.12);
    }
    /* animated sliding top bar */
    .cp-card::before {
        content: '';
        position: absolute;
        top: 0; inset-inline-start: 0; inset-inline-end: 0;
        height: 3px;
        background: linear-gradient(90deg, #b91c1c, #ef4444, #f87171, #ef4444, #b91c1c);
        background-size: 300% 100%;
        animation: cp-bar-slide 4s linear infinite;
        z-index: 5;
    }
    @keyframes cp-bar-slide {
        0%   { background-position: 0% 0%; }
        100% { background-position: 300% 0%; }
    }

    /* ── Banner ── */
    .cp-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.375rem 1.5rem 1.25rem;
        background: linear-gradient(110deg, rgba(239,68,68,0.07) 0%, rgba(239,68,68,0.01) 70%);
        border-bottom: 1px solid rgba(239,68,68,0.08);
        position: relative;
        overflow: hidden;
    }
    .dark .cp-banner {
        background: linear-gradient(110deg, rgba(239,68,68,0.13) 0%, rgba(239,68,68,0.02) 70%);
        border-bottom-color: rgba(239,68,68,0.12);
    }
    /* floating orb */
    .cp-banner::before {
        content: '';
        position: absolute;
        inset-inline-end: -2rem; top: -2rem;
        width: 10rem; height: 10rem;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(239,68,68,0.12) 0%, transparent 70%);
        pointer-events: none;
        animation: cp-orb 6s ease-in-out infinite;
    }
    @keyframes cp-orb {
        0%, 100% { transform: translateY(0) scale(1); }
        50%       { transform: translateY(6px) scale(1.08); }
    }
    /* dot-grid decoration */
    .cp-banner::after {
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
    .cp-banner-icon {
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
    /* pulsing ring */
    .cp-banner-icon::after {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 1.25rem;
        border: 2px solid rgba(239,68,68,0.35);
        animation: cp-ring 2.5s ease-in-out infinite;
    }
    @keyframes cp-ring {
        0%, 100% { opacity: 0.7; transform: scale(1); }
        50%       { opacity: 0;   transform: scale(1.28); }
    }
    .cp-banner-text { position: relative; z-index: 1; }
    .cp-banner-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }
    .dark .cp-banner-title { color: #f9fafb; }
    .cp-banner-sub {
        font-size: 0.775rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }
    .dark .cp-banner-sub { color: #9ca3af; }

    /* ── Card Body ── */
    .cp-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem; }

    /* ── Requirements box ── */
    .cp-requirements {
        border-radius: 1rem;
        border: 1px solid rgba(239,68,68,0.12);
        background: rgba(239,68,68,0.025);
        padding: 1.125rem 1.375rem 1.25rem;
    }
    .dark .cp-requirements {
        background: rgba(239,68,68,0.055);
        border-color: rgba(239,68,68,0.16);
    }
    .cp-req-title {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #ef4444;
        text-shadow: 0 0 10px rgba(239,68,68,0.3);
        margin-bottom: 1rem;
    }
    .cp-req-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(11rem, 1fr));
        gap: 0.625rem;
    }
    /* staggered entrance */
    @keyframes cp-pill-in {
        from { opacity: 0; transform: translateY(8px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0)   scale(1); }
    }
    .cp-req-pill {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 0.875rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 500;
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        animation: cp-pill-in 0.4s cubic-bezier(.3,1.3,.5,1) both;
    }
    .cp-req-pill:nth-child(1) { animation-delay: 0.05s; }
    .cp-req-pill:nth-child(2) { animation-delay: 0.11s; }
    .cp-req-pill:nth-child(3) { animation-delay: 0.17s; }
    .cp-req-pill:nth-child(4) { animation-delay: 0.23s; }
    .cp-req-pill:nth-child(5) { animation-delay: 0.29s; }
    /* shimmer on hover */
    .cp-req-pill::after {
        content: '';
        position: absolute;
        top: 0; inset-inline-start: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.5) 60%, transparent 80%);
        transition: inset-inline-start 0.4s ease;
    }
    .dark .cp-req-pill::after {
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.08) 60%, transparent 80%);
    }
    .cp-req-pill:hover::after { inset-inline-start: 130%; }
    .cp-req-pill:hover { transform: translateY(-1px); }
    .cp-req-pill.pass {
        background: rgba(22,163,74,0.08);
        border: 1px solid rgba(22,163,74,0.28);
        color: #15803d;
    }
    .cp-req-pill.fail {
        background: rgba(220,38,38,0.06);
        border: 1px solid rgba(220,38,38,0.2);
        color: #b91c1c;
    }
    .dark .cp-req-pill.pass { background: rgba(22,163,74,0.13); border-color: rgba(22,163,74,0.35); color: #4ade80; }
    .dark .cp-req-pill.fail { background: rgba(220,38,38,0.1);  border-color: rgba(220,38,38,0.28); color: #fca5a5; }
    /* indicator with pulsing ring */
    .cp-ind {
        position: relative;
        width: 1.1rem; height: 1.1rem;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .cp-ind::before {
        content: '';
        position: absolute; inset: -3px;
        border-radius: 50%;
        border: 2px solid transparent;
    }
    .cp-req-pill.pass .cp-ind::before { border-color: rgba(22,163,74,0.5); animation: cp-pulse-green 2s ease-out infinite; }
    .cp-req-pill.fail .cp-ind::before { border-color: rgba(220,38,38,0.5); animation: cp-pulse-red   2s ease-out infinite; }
    .dark .cp-req-pill.pass .cp-ind::before { border-color: rgba(74,222,128,0.55); }
    .dark .cp-req-pill.fail .cp-ind::before { border-color: rgba(239,68,68,0.55); }
    @keyframes cp-pulse-green {
        0%   { transform: scale(0.8); opacity: 0.9; }
        65%  { transform: scale(1.55); opacity: 0; }
        100% { transform: scale(0.8); opacity: 0; }
    }
    @keyframes cp-pulse-red {
        0%   { transform: scale(0.8); opacity: 0.8; }
        65%  { transform: scale(1.55); opacity: 0; }
        100% { transform: scale(0.8); opacity: 0; }
    }
    .cp-ind svg { width: 0.85rem; height: 0.85rem; position: relative; z-index: 1; }
    .cp-req-pill.pass .cp-ind svg { color: #16a34a; filter: drop-shadow(0 0 4px rgba(22,163,74,0.7)); }
    .cp-req-pill.fail .cp-ind svg { color: #dc2626; }
    .dark .cp-req-pill.pass .cp-ind svg { color: #4ade80; filter: drop-shadow(0 0 5px rgba(74,222,128,0.8)); }
    .dark .cp-req-pill.fail .cp-ind svg { color: #f87171; }
    /* save row */
    .cp-save-row { display: flex; justify-content: flex-end; }
</style>

<div class="cp-card">
    <div class="cp-banner">
        <div class="cp-banner-icon">
            <x-heroicon-o-lock-closed class="w-5 h-5 text-white" />
        </div>
        <div class="cp-banner-text">
            <p class="cp-banner-title">تغيير كلمة المرور</p>
            <p class="cp-banner-sub">قم بتحديث كلمة المرور الخاصة بك بشكل دوري للحفاظ على أمان حسابك</p>
        </div>
    </div>
    <div class="cp-body">
        <form wire:submit="save">
            <div class="cp-body">
                <div>{{ $this->form }}</div>

                {{-- Password Requirements --}}
                <div class="cp-requirements">
                    <p class="cp-req-title">متطلبات كلمة المرور</p>
                    <div class="cp-req-grid">

                        <div class="cp-req-pill {{ $hasMinLength ? 'pass' : 'fail' }}">
                            <span class="cp-ind">
                                @if($hasMinLength)
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            8+ أحرف على الأقل
                        </div>

                        <div class="cp-req-pill {{ $hasLetters ? 'pass' : 'fail' }}">
                            <span class="cp-ind">
                                @if($hasLetters)
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            يحتوي على أحرف
                        </div>

                        <div class="cp-req-pill {{ $hasNumbers ? 'pass' : 'fail' }}">
                            <span class="cp-ind">
                                @if($hasNumbers)
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            يحتوي على أرقام
                        </div>

                        <div class="cp-req-pill {{ $hasSymbols ? 'pass' : 'fail' }}">
                            <span class="cp-ind">
                                @if($hasSymbols)
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            يحتوي على رموز
                        </div>

                        <div class="cp-req-pill {{ $hasMixedCase ? 'pass' : 'fail' }}">
                            <span class="cp-ind">
                                @if($hasMixedCase)
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            أحرف كبيرة وصغيرة
                        </div>

                    </div>
                </div>

                <div class="cp-save-row">
                    <x-filament::button type="submit" :disabled="!$this->allRulesPass()" icon="heroicon-o-shield-check" size="lg">
                        حفظ التغييرات
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</div>
</x-filament-panels::page>
