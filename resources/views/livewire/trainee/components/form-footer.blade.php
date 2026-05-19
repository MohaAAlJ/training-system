<link rel="stylesheet" href="{{ asset('css/form-footer.css') }}">

<div class="form-footer">
    @if ($this->showTerms)
        <button type="submit" id="submitBtn" class="glow-button" @disabled(!$this->isFormReady)>
            @if ($isValidating)
                <span class="spinner"></span> جاري الفحص...
            @elseif ($existingApplicationId)
                تعديل الطلب
            @else
                إرسال الطلب
            @endif
        </button>
    @endif
</div>
