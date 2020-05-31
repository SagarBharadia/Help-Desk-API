<?php

namespace App\Http\Controllers;

use App\TenantLogAction;
use App\TenantPermissionAction;
use App\TenantReport;
use App\TenantRole;
use App\TenantUser;
use App\TenantUserActionLog;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use stdClass;

class TenantReportController extends Controller
{

  protected $options = [
    'call_turnaround_times',
    'staff_turnaround_times',
    'calls_open_past_24_hours'
  ];

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Return reports paginated by 25.
   *
   * @return \Illuminate\Contracts\Pagination\Paginator
   */
  public function getAll()
  {
    $reports = TenantReport::with('createdBy')->orderBy('created_at', 'desc')->simplePaginate(25);

    return $reports;
  }

  private function callTurnaroundTimesAnalyse($options)
  {
    $results = DB::connection('tenant')->select(DB::raw("
              SELECT COUNT(*) as total_records, 
              AVG(timeTakenQuery.time_taken) as average_time_taken FROM (
                SELECT id, TIMESTAMPDIFF(HOUR, created_at, resolved_at) as time_taken 
                  FROM calls 
                  WHERE created_at > CONVERT(:start_date, DATETIME) 
                  AND created_at < CONVERT(:end_date, DATETIME) 
                  AND resolved=1
              ) AS timeTakenQuery;
            "), [
      ':start_date' => $options['start_date'],
      ':end_date' => $options['end_date']
    ]);
    $payload = array();
    $payload['start_date'] = $options['start_date'];
    $payload['end_date'] = $options['end_date'];
    $payload['total_records_applicable'] = $results[0]->total_records;
    $payload['average_time_taken'] = $results[0]->average_time_taken;
    return $payload;
  }

  private function callsOpenPast24HoursAnalyse($options)
  {
    $results = DB::connection('tenant')->select(DB::raw("
              SELECT COUNT(*) as callsOpenPast24Hours FROM (
                SELECT id, TIMESTAMPDIFF(HOUR, created_at, resolved_at) as time_taken
                FROM calls 
                WHERE created_at > CONVERT(:start_date, DATETIME) 
                AND created_at < CONVERT(:end_date, DATETIME) 
                AND resolved=1) as openPast24Hours 
              WHERE openPast24Hours.time_taken > 24;
            "), [
      ':start_date' => $options['start_date'],
      ':end_date' => $options['end_date']
    ]);
    $payload = array();
    $payload['start_date'] = $options['start_date'];
    $payload['end_date'] = $options['end_date'];
    $payload['calls_open_past_24_hours'] = $results[0]->callsOpenPast24Hours;
    return $payload;
  }

  private function staffTurnaroundTimesAnalyse($options)
  {
    $results = array();
    $results['start_date'] = $options['start_date'];
    $results['end_date'] = $options['end_date'];
    $results['data'] = [];
    $masterRole = TenantRole::getByName('master');
    $users = TenantUser::without('permissions')->where("role_id", '!=', $masterRole->id)->orderBy("first_name", 'ASC')->get();
    foreach ($users as $user) {
      $averageForUser = DB::connection('tenant')->select(DB::raw("
                  SELECT COUNT(*) as total_records, 
                  AVG(timeTakenQuery.time_taken) as average_time_taken FROM (
                    SELECT id, TIMESTAMPDIFF(HOUR, created_at, resolved_at) as time_taken 
                    FROM calls 
                    WHERE created_at > CONVERT(:start_date, DATETIME) 
                    AND created_at < CONVERT(:end_date, DATETIME) 
                    AND resolved=1
                    AND current_analyst_id = :analyst_id
                  ) AS timeTakenQuery;
                "), [
        ':start_date' => $options['start_date'],
        ':end_date' => $options['end_date'],
        ':analyst_id' => $user->id
      ]);

      // Converting user model to array to store the average time taken
      $user = $user->toArray();
      $payload = array();
      $payload['total_records_applicable'] = $averageForUser[0]->total_records;
      $payload['average_time_taken'] = $averageForUser[0]->average_time_taken;
      $user['averageTurnaroundTime'] = $payload;

      array_push($results['data'], $user);
    }
    return $results;
  }

  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|string',
      'description' => 'required|string',
      'options' => 'required|array'
    ]);

    // Will be $key => $value with $value being another array
    // Example:
    //    [
    //      'call-turnaround-times' => ['start-date' => yyyy-mm-dd, 'end-date' => yyyy-mm-dd],
    //      'staff-turnaround-times' => ['start-date' => yyyy-mm-dd, 'end-date' => yyyy-mm-dd, 'users' => [1,2,3,4,5]],
    //      'calls-open-past-24-hours' => ['start-date' => yyyy-mm-dd, 'end-date' => yyyy-mm-dd]
    //    ]
    $reportOptions = $request->get('options');

    $errors = [];

    foreach ($reportOptions as $key => $value) {
      if (!in_array($key, $this->options)) {
        unset($reportOptions[$key]);
        $reportOptions = array_values($reportOptions);
      } else {
        $validator = Validator::make($value, [
          'start_date' => 'required|date',
          'end_date' => 'required|date'
        ]);
        if ($validator->fails()) {
          $errors[$key] = $validator->errors();
        } else {
          $startDate = DateTime::createFromFormat("Y-m-d", $value['start_date'])->getTimestamp();
          $endDate = DateTime::createFromFormat("Y-m-d", $value['end_date'])->getTimestamp();
          if ($startDate > $endDate) {
            $bag = ["Start date is before end date"];
            $errors[$key] = $bag;
          }
        }
      }
    }

    if (empty($errors)) {
      $reportStatistics = array();
      foreach ($reportOptions as $key => $value) {
        switch ($key) {
          case "call_turnaround_times":
            $reportStatistics['call-turnaround-times'] = $this->callTurnaroundTimesAnalyse($value);
            break;
          case "calls_open_past_24_hours":
            $reportStatistics['calls-open-past-24-hours'] = $this->callsOpenPast24HoursAnalyse($value);
            break;
          case "staff_turnaround_times":
            $reportStatistics['staff-turnaround-times'] = $this->staffTurnaroundTimesAnalyse($value);
            break;
          default:
            break;
        }
      }


      $pdf = PDF::loadView('report', compact('reportStatistics'));
      $pdfContent = $pdf->output();
      $helpDeskName = app('config')->get('database.connections.tenant.database');
      $pdfFileName = $request->get('name') . " - " . Carbon::now()->format('yy-m-d') . "-" . Str::random(16) . ".pdf";
      $pdfFilePath = $helpDeskName . "/reports/" . $pdfFileName;

      $tenantReport = new TenantReport();
      $tenantReport->name = $request->get('name');
      $tenantReport->description = $request->get('description');
      $tenantReport->created_by = Auth::guard('tenant_api')->user()->id;
      $tenantReport->filename = $pdfFileName;
      $tenantReport->save();

      Storage::put($pdfFilePath, $pdfContent);

      $response = $pdf->download($pdfFileName);
    } else {
      $response = response()->json($errors, 422);
    }

    return $response;
  }
}
