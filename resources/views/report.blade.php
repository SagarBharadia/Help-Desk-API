<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body>
<h1>Help Desk Performance Statistics</h1>
<hr/>
@if(isset($reportStatistics['call-turnaround-times']))
    @php
        $callTurnaroundTimes = $reportStatistics['call-turnaround-times'];
    @endphp
    <h2>Call turnaround times</h2>
    <p>Between {{ $callTurnaroundTimes['start_date'] }} and {{ $callTurnaroundTimes['end_date'] }} the average call
        turnaround time was {{ number_format($callTurnaroundTimes['average_time_taken'], 1) }} hours.</p>
    <p>There was a total of {{ $callTurnaroundTimes['total_records_applicable'] }} applicable to this criteria.</p>
    <hr/>
@endif
@if(isset($reportStatistics['calls-open-past-24-hours']))
    @php
        $callsOpenPast24Hours = $reportStatistics['calls-open-past-24-hours'];
    @endphp
    <h2>Calls open for over 24 hours</h2>
    <p>Between {{ $callsOpenPast24Hours['start_date'] }} and {{ $callsOpenPast24Hours['end_date'] }} the number of calls
        open for over 24 hours was {{ $callsOpenPast24Hours['calls_open_past_24_hours'] }}.</p>
    <hr/>
@endif
@if(isset($reportStatistics['staff-turnaround-times']))
    @php
        $staffTurnaroundTime = $reportStatistics['staff-turnaround-times'];
    @endphp
    <h2>Staff turnaround times</h2>
    <p>The information shown below is applicable for the time period between {{ $staffTurnaroundTime['start_date'] }}
        and {{ $staffTurnaroundTime['end_date'] }}.</p>
    <br/>
    @foreach($staffTurnaroundTime['data'] as $user)
        @if($user['averageTurnaroundTime']['total_records_applicable'] != 0)
            <h3>{{ $user['first_name'] . " " . $user['second_name'] }}</h3>
            <p>Id: {{ $user['id'] }}</p>
            <p>Email: {{ $user['email_address'] }}</p>
            <p>Average Turnaround Time: {{ number_format($user['averageTurnaroundTime']['average_time_taken'], 1) }} hours</p>
            <p>Total Records Applicable: {{ $user['averageTurnaroundTime']['total_records_applicable'] }}</p>
            <br/>
        @endif
    @endforeach
    <hr/>
@endif
</body>
</html>