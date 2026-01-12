<link rel="stylesheet" href="{{ asset('css/form-terms.css') }}">

<!-- Terms & Conditions Section (shown only when personal details are visible) -->
@if ($showPersonalDetails)
<div class="terms-section">
    <label class="checkbox-field" style="display: flex; gap: 12px; align-items: flex-start; cursor: pointer;">
        <input 
            type="checkbox" 
            wire:model.live="termsApproval"
            class="form__checkbox"
            style="width: 20px; height: 20px; accent-color: var(--primary); margin-top: 4px;"
        >
        <div style="flex: 1;">
            <p style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                إقرار صحة البيانات والالتزام
            </p>
            <p style="margin: 6px 0 0; font-size: 0.85rem; color: var(--text-muted);">
                أقر بأن جميع البيانات المدخلة أعلاه صحيحة، وأتحمل كامل المسؤولية عن أي خطأ فيها.
            </p>
            <p style="margin: 8px 0 0; font-size: 0.8rem; color: var(--primary); font-weight: 600; line-height: 1.4;">
                ⚠️ ملاحظة هامة: أقر بعلمي أنه في حال قبول طلبي وتخلفي عن الحضور لمباشرة التدريب لمدة تزيد عن 7 أيام من تاريخ البدء المحدد، يحق للإدارة إلغاء التدريب وطي قيدي تلقائياً.
            </p>
        </div>
    </label>
</div>
@endif
