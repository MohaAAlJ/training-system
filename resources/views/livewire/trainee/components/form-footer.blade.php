<link rel="stylesheet" href="{{ asset('css/form-footer.css') }}">

<!-- Submit Button - Posts to ApplicationFormController::store -->
<div class="form-footer">
    <button
        type="submit"
        id="submitBtn"
        class="glow-button"
        @disabled(!$termsApproval || $isValidating)
    >
        @if ($isValidating)
            <span class="spinner"></span> جاري الفحص...
        @else
            إرسال الطلب
        @endif
    </button>
</div>
