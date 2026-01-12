<!-- Submit Button - Posts to ApplicationFormController::store -->
@if ($showPersonalDetails)
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
@endif
