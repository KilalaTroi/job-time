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
        *{ font-family: DejaVu Sans !important;}
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body style="background: none;">

<div class="container-fluid">
    <h2 class="text-center">Report</h2>
    <h6 class="mt-5">Title: {{ $data['title'] }}</h6>
    <h6>Date: {{ $data['date_time'] }}</h6>
    <h6>Reporter: {{ $data['reporter'] }}</h6>

    @if ( $data['is_metting'] == 1 )
        <h6>Attend Person (KILALA): {{ $data['attend_person'] }}</h6>
        <h6>Attend Person (OTHER): {{ $data['attend_other_person'] }}</h6>
    @else
        <h6>Departments: {{ $data['dept_name'] }}</h6>
        <h6>Projects: {{ $data['project_name'] }}</h6>
        <h6>Issue: {{ $data['issue_name'] }}</h6>
    @endif
    
    <div class="content mt-5">
        {!! $data['content'] !!}
    </div>
</div>

</body>
</html>
