<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Job Time') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body style="background: none;">

<div class="container-fluid">
    <h1 class="text-center"><strong>Report</strong></h1>
    <h5 class="mt-5"><strong>Title:</strong> {{ $data['title'] }}</h5>
    <h5><strong>Date:</strong> {{ $data['date_time'] }}</h5>
    <h5><strong>Reporter:</strong> {{ $data['reporter'] }}</h5>

    @if ( $data['is_metting'] == 1 )
        <h5><strong>Attend Person (KILALA):</strong> {{ $data['attend_person'] }}</h5>
        <h5><strong>Attend Person (OTHER):</strong> {{ $data['attend_other_person'] }}</h5>
    @else
        <h5><strong>Departments:</strong> {{ $data['dept_name'] }}</h5>
        <h5><strong>Projects:</strong> {{ $data['project_name'] }}</h5>
        <h5><strong>Issue:</strong> {{ $data['issue_name'] }}</h5>
    @endif
    
    <div class="content mt-5">
        {!! $data['content'] !!}
    </div>
</div>

</body>
</html>
