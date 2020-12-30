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
      font-size: 18px
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
    <p style="font-size: 22px; margin-bottom: 60px;" class="d-flex algin-items-end">Date:&nbsp&nbsp<span style="width: 160px; padding-top: 6px;" class="d-inline-block overflow-x-hidden" data-label="{{ $data['now_date'] }}">................................................................................................................</span>
    </p>
    <h1 class="text-center" style="font-size: 26px; margin-bottom: 60px;">APPLICATION FOR ABSENCE</h1>
    <p style="margin-block: 60px" class="d-flex">My name is:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<span style="width: 630px; padding-top: 8px;" class="d-inline-block overflow-x-hidden font-family-dif" data-label="{{ $data['name'] }}">................................................................................................................................................................................................................................</span>
    </p>
    <p>Today, I would like to write this application to ask for your approval for my absence from the company on:</p>
    <ul>
      <li class="d-flex" style="margin-bottom: 10px;">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ 'morning' === $data['type'] ? asset('images/check.gif') : asset('images/uncheck.gif') }}">
          <p class='mb-0'>Half-day (08:00 - 12:00)</p>
        </div>
        <span style="width: 300px; padding-top: 6px;" class="d-inline-block overflow-x-hidden" data-label="{{ 'morning' === $data['type'] ? $data['date'] : '' }}">................................................................................................................</span>
      </li>
      <li class="d-flex" style="margin-bottom: 10px;">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ 'afternoon' === $data['type'] ? asset('images/check.gif') : asset('images/uncheck.gif') }}">
          <p class='mb-0'>Half-day (13:00 - 17:00)</p>
        </div>
        <span style="width: 300px; padding-top: 6px;" class="d-inline-block overflow-x-hidden" data-label="{{ 'afternoon' === $data['type'] ? $data['date'] : '' }}">................................................................................................................</span>
      </li>
      <li class="d-flex">
        <div class="d-flex align-items-center" style="margin-right: 60px; width: 260px">
          <img style="position: relative; top: -5px" class="mr-2" src="{{ 'all_day' === $data['type'] ? asset('images/check.gif') : asset('images/uncheck.gif') }}">
          <p class='mb-0'>Full-day (08:00 - 17:00)</p>
        </div>
        <span style="width: 300px; padding-top: 6px;" class="d-inline-block overflow-x-hidden" data-label="{{ 'all_day' === $data['type'] ? $data['date'] : '' }}">................................................................................................................</span>
      </li>
    </ul>
    <p style="margin-bottom: 60px" class="d-flex algin-items-end">Total of off-day:&nbsp&nbsp&nbsp&nbsp<span style="width: 80px; padding-top: 4px" class="d-inline-block overflow-x-hidden text-center" data-label="{{ $data['totalOff'] }}">...................</span>&nbspday(s).</p>
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
      <div style="min-width: 180px; position: relative">
        <p>Yours sincerely,</p>
        <span style="width: 180px; line-height: 31px; position: absolute; bottom: -8px; left: 0;" class="d-inline-block overflow-x-hidden">................................................................</span>
      </div>
      <div style="width: 700px; padding-left: 90px;">
        <p style="font-weight: 700" class="text-center">Company’s Approval</p>
        <table class="table table-border">
          <thead>
            <th>Leader</th>
            <th>Administrative Manager</th>
            <th>D. General Director</th>
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