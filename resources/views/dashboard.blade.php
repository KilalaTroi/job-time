<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::user()->id }}">
    <meta name="user-created-at" content="{{ Auth::user()->created_at }}">
    <meta name="user-language" content="{{ Auth::user()->language ? Auth::user()->language : 'en' }}">
    <meta name="teams" content="{{ json_encode($teamDigitalOptions) }}">
    <meta name="media-teams" content="{{ json_encode($teamMediaOptions) }}">
    <meta name="full-teams" content="{{ json_encode($teamFullOptions) }}">
    <meta name="team-default" content="{{ Auth::user()->team }}">

    <title>{{ config('app.name', 'Job Time') }}</title>

    @include('./components/favicon')

    <!-- Styles -->
    <link href="{{ asset(mix('css/app-admin.css')) }}" rel="stylesheet">

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
<script src="{{ asset(mix('js/app-admin.js')) }}"></script>
</body>
</html>
