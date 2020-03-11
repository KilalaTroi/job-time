<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::user()->id }}">
    <meta name="user-language" content="{{ Auth::user()->language ? Auth::user()->language : 'en' }}">

    <title>{{ config('app.name', 'Job Time') }}</title>

    @include('./components/favicon')

    <!-- Fonts and icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app-admin.css') }}" rel="stylesheet">

    <script type="text/javascript">
        window.Laravel = {!! json_encode([
                'baseUrl' => url('/'),
                'csrfToken' => csrf_token(),
            ]) !!};
    </script>
</head>
<body class="language-{{ Auth::user()->language ? Auth::user()->language : 'en' }}">
<div id="app"></div>

<!-- Scripts -->
<script src="{{ asset('js/app-japanese_planner.js') }}"></script>
</body>
</html>
