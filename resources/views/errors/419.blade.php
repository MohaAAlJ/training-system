@extends('errors.minimal')

@section('icon_content')
    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
@endsection

@section('title', 'انتهت صلاحية الجلسة')
@section('message', 'عذراً، انتهت صلاحية جلستك. يرجى تحديث الصفحة والمحاولة مرة أخرى.')
@section('actions')
    <button onclick="window.location.reload()" class="btn btn-secondary" type="button">
        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        تحديث الصفحة
    </button>
@endsection
