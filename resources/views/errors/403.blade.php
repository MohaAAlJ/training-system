@extends('errors.minimal')

@section('icon_content')
    <circle cx="12" cy="12" r="10"></circle>
    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
@endsection

@section('title', 'تحذير! ممنوع الوصول')

@section('message')
    @php
        $default = 'أنت تحاول الوصول إلى صفحة ليست ضمن صلاحياتك.';
        $msg = $default;
        if(isset($exception) && $exception->getMessage()) {
            $msg = $exception->getMessage();
        }
    @endphp
    {{ $msg }}
@endsection
