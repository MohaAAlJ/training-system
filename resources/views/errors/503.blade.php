@extends('errors.minimal')

@section('icon_content')
    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
@endsection

@section('title')
    @php
       $title = 'الموقع قيد الصيانة';
       try {
           /** @var \App\Settings\TrainingSettings $settings */
           $settings = app(\App\Settings\TrainingSettings::class);
           if($settings->maintenance_title) $title = $settings->maintenance_title;
       } catch(\Throwable $e){}
    @endphp
    {{ $title }}
@endsection

@section('message')
    @php
       $msg = 'عذراً، الموقع قيد الصيانة حالياً. سنعود قريباً.';
       try {
           /** @var \App\Settings\TrainingSettings $settings */
           $settings = app(\App\Settings\TrainingSettings::class);
           if($settings->maintenance_message) $msg = $settings->maintenance_message;
       } catch(\Throwable $e){}
    @endphp
    {{ $msg }}
@endsection

@section('actions')
    <form id="logout-form" action="{{ route('filament.home.auth.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    <button onclick="document.getElementById('logout-form').submit()" type="button" class="btn" style="color: white; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
        </svg>
        تسجيل الخروج
    </button>
@endsection
