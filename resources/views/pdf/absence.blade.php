<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;700&display=fallback" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;1,700&display=fallback" rel="stylesheet">

  <title>{{ config('app.name', 'Job Time') }}</title>

  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <style>
    body {
      font-family: 'Merriweather', sans-serif;
      padding: 10px 30px;
      font-size: 18px;
      color: #000;
    }

    h1 {
      font-weight: 700;
    }

    span.overflow-x-hidden {
      position: relative;
      overflow-x: hidden !important;
    }

    span.overflow-x-hidden::before {
      content: attr(data-label);
      position: absolute;
      top: -5px;
      left: 10px;
      width: 100%;
    }

    span.overflow-x-hidden.text-center::before {
      left: 0;
    }

    span.overflow-x-hidden.font-family-dif::before{
      font-family: 'Merriweather Sans', sans-serif;
      font-size: 20px;
      text-transform: uppercase;
    }

    .table thead th,
    .table tbody td,
    .table .table-border {
      border: thin solid #000;
    }

    .table {
      font-size: 14px;
    }

    .table thead th {
      font-weight: 400;
      text-align: center
    }

    .table tbody td {
      height: 90px;
    }
  </style>
</head>

<body style="background: none;">

  <div class="container">
    <p style="margin-bottom: 5px"><span style="width: 60px; display:inline-block">To:</span>KILALA COMMUNICATIONS CO., LTD.</p>
    <p style="margin-bottom: 30px"><span style="width: 60px; display:inline-block">Attn:</span>Mr. KASADO HIROFUMI, General Director</p>

    @php
      $styleNowDate= "width: 160px; padding-top: 6px;";
      $contentNowDate = "......................................";
      if(isset($data['now_date']) && !empty($data['now_date'])) {
        $styleNowDate= "font-size:22px";
        $contentNowDate = $data['now_date'];
      }
    @endphp

    <p style="font-size: 22px; margin-bottom: 60px;" class="d-flex algin-items-end">Date:&nbsp&nbsp<span style="{{ $styleNowDate }}" class="d-inline-block overflow-x-hidden">{{ $contentNowDate }}</span>
    </p>
    <h1 class="text-center" style="font-size: 26px; margin-bottom: 60px;">APPLICATION FOR ABSENCE</h1>

    @php
      $styleName= "width: 630px; padding-top: 10px;";
      $contentName = "................................................................................................................................................................................................................................";
      if(isset($data['name']) && !empty($data['name'])) {
        $styleName= "font-size:22px";
        $contentName = $data['name'];
      }
    @endphp

    <p style="margin-block: 60px" class="d-flex">My name is:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<span style="{{ $styleName }}" class="d-inline-block overflow-x-hidden font-family-dif">{{ $contentName  }}</span>
    </p>
    <p>Today, I would like to write this application to ask for your approval for my absence from the company on:</p>
    <ul>
      @php
        $styleType = "width: 300px; padding-top: 6px;";
        $contentType[0]['text'] = $contentType[1]['text'] = $contentType[2]['text'] = "................................................................................................................";
        $contentType[0]['img'] = $contentType[1]['img'] = $contentType[2]['img'] = asset('images/uncheck.gif');
        if(isset($data['off']) && !empty($data['off'])) {
          $styleType= "width: 300px; font-size:22px";
          if(isset($data['off']['morning']) && !empty($data['off']['morning'])){
            $contentType[0] = array(
              'text' => $data['off']['morning'],
              'img' => asset('images/check.gif')
            );
          }
          if(isset($data['off']['afternoon']) && !empty($data['off']['afternoon'])){
            $contentType[1] = array(
              'text' => $data['off']['afternoon'],
              'img' => asset('images/check.gif')
            );
          }
          if(isset($data['off']['all_day']) && !empty($data['off']['all_day'])){
            $contentType[2] = array(
              'text' => $data['off']['all_day'],
              'img' => asset('images/check.gif')
            );
          }
        }
      @endphp
      <li class="d-flex" style="margin-bottom: 10px;">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ $contentType[0]['img'] }}">
          <p class='mb-0'>Half-day (08:00 - 12:00)</p>
        </div>
        <span style="{{ $styleType }}" class="d-inline-block overflow-x-hidden">{{ $contentType[0]['text'] }}</span>
      </li>
      <li class="d-flex" style="margin-bottom: 10px;">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ $contentType[1]['img'] }}">
          <p class='mb-0'>Half-day (13:00 - 17:00)</p>
        </div>
        <span style="{{ $styleType }}" class="d-inline-block overflow-x-hidden">{{ $contentType[1]['text'] }}</span>
      </li>
      <li class="d-flex">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ $contentType[2]['img'] }}">
          <p class='mb-0'>Full-day (08:00 - 17:00)</p>
        </div>
        <span style="{{ $styleType }}" class="d-inline-block overflow-x-hidden">{{ $contentType[2]['text'] }}</span>
      </li>
    </ul>
    @php
      $styleTotalOff= "width: 80px; padding-top: 4px;";
      $contentTotalOff = "...................";
      if(isset($data['totalOff']) && !empty($data['totalOff'])) {
        $styleTotalOff= "width: 60px; font-size:22px";
        $contentTotalOff = $data['totalOff'];
      }
    @endphp
    <p style="margin-bottom: 60px" class="d-flex algin-items-end">Total of off-day:&nbsp&nbsp&nbsp&nbsp<span style="{{ $styleTotalOff }}" class="d-inline-block overflow-x-hidden text-center">{{ $contentTotalOff }}</span>&nbspday(s).</p>
    <p>For the following reason:</p>
    <ul style="margin-bottom: 60px">
      <li class="d-flex align-items-center" style="margin-bottom: 10px">
        <img style="position: relative; top: -5px" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Personal reasons</p>
      </li>
      <li class="d-flex align-items-center" style="margin-bottom: 10px">
        <img style="position: relative; top: -5px" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Sickness</p>
      </li>
      <li class="d-flex align-items-center" style="margin-bottom: 10px">
        <img style="position: relative; top: -5px" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Baby’s sickness</p>
      </li>
      <li class="d-flex align-items-center" style="margin-bottom: 10px">
        <img style="position: relative; top: -5px" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Go to hometown</p>
      </li>
      <li class="d-flex align-items-center" style="margin-bottom: 10px">
        <img style="position: relative; top: -5px" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Travel</p>
      </li>
      <li class="d-flex align-items-center">
        <img style="position: relative; top: -5px;opacity: 0;" class="mr-2" src="{{ asset('images/uncheck.gif') }}">
        <p class='mb-0'>Others:&nbsp&nbsp&nbsp</p>
        <span style="width: 650px; line-height: 31px" class="d-inline-block overflow-x-hidden">................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................................</span>
      </li>
    </ul>
    <p style="margin-bottom: 60px">I commits that I will arrange and finish my tasks as soon as returning to work.</p>
    <div class="d-flex">
      <div style="min-width: 270px; position: relative">
        <p>Yours sincerely,</p>
        <span style="width: 100%;  white-space: nowrap; line-height: 31px; position: absolute; bottom: -8px; left: 0; {{ isset($data['name']) && !empty($data['name']) ? 'font-size: 20px' : '' }}" class="d-inline-block overflow-x-hidden">{{ $contentName }}</span>
      </div>
      <div style="width: 680px; padding-left: 40px;">
        <p style="font-weight: 680" class="text-center">Company’s Approval</p>
        <table class="table table-border">
          <thead>
            <th style="width: 33%">Leader</th>
            <th style="width: 33%">Administrative Manager</th>
            <th style="width: 33%">D. General Director</th>
          </thead>
          <tbody>
            <tr>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>

</html>