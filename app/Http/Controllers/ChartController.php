<?php

namespace App\Http\Controllers;

use App\Models\Chart;
use App\Models\Event;
use App\Models\EventResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChartController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $latest_chart = Chart::orderBy('created_at', 'desc')->first();

        if (!$latest_chart) {
            $latest_chart = $this->create();
        }

        $chart_data = $latest_chart->data;

        return view('charts.index', [
            'chart' => $latest_chart,
            'chart_data' => $chart_data,
        ]);
    }

    public function updatedChart() {
        $this->create();

        return redirect()->route('rankings.index');
    }



    /**
     * Show the form for creating a new resource.
     */
    private function create() {
        //

        $date = Carbon::now()->format('Y-m-d H:i:s');

        $start_date = "";
        $end_date = "";

        if (Carbon::now()->month < 8) {
            $start_date = Carbon::now()->subYear()->startOfYear()->addMonths(8)->startOfMonth();
            $end_date = Carbon::now()->startOfYear()->addMonths(6)->endOfMonth();
        } else {
            $start_date = Carbon::now()->startOfYear()->addMonths(8)->startOfMonth();
            $end_date = Carbon::now()->addYear()->startOfYear()->addMonths(6)->endOfMonth();
        }

        $events = Event::whereBetween('start_date', [
            $start_date,
            $end_date,
        ])->where('is_disabled', false)->get();


        $results = [];

        foreach ($events as $event) {
            $event_result = $event->results()->with('user')->orderBy('war_points', 'desc')->get();

            foreach ($event_result as $key => $value) {

                if (!isset($results[$value->user_id])) {

                    $primaryAcademyAthlete = $value->user->primaryAcademyAthlete();

                    $results[$value->user_id] = [
                        'user_id' => $value->user_id,
                        'user_name' => $value->user->name . ' ' . $value->user->surname,
                        'user_battle_name' => $value->user->battle_name,
                        'user_battle_name' => $value->user->battle_name,
                        'user_academy' => $primaryAcademyAthlete ? $primaryAcademyAthlete->name : '',
                        'user_school' => $value->user->primarySchoolAthlete()->name ?? '',
                        'school_slug' => $value->user->primarySchoolAthlete()->slug ?? '',
                        'nation' => $value->user->nation->name,
                        'total_war_points' => 0,
                        'total_style_points' => 0,
                    ];
                }

                $results[$value->user_id]['total_war_points'] += $value->total_war_points;
                $results[$value->user_id]['total_style_points'] += $value->total_style_points;
            }
        }

        usort($results, function ($a, $b) {
            return $b['total_war_points'] - $a['total_war_points'];
        });

        $chart = Chart::create([
            'note' => Carbon::now()->year . " chart",
            'data' => $results,
            'created_at' => $date,
        ]);

        return $chart;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Chart $chart) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chart $chart) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chart $chart) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chart $chart) {
        //
    }

    public function generateChart() {

        for ($i = 2; $i <= 6; $i++) {

            $current_year = date('Y') . "-0{$i}";

            $data = [];
            $events = Event::where('start_date', '<', "{$current_year}-01")->get();

            foreach ($events as $event) {
                $event_results = EventResult::where('event_id', $event->id)->get();

                foreach ($event_results as $result) {
                    $data[$result->user_id][] = [
                        'war_points' => $result->total_war_points,
                        'style_points' => $result->total_style_points,
                    ];
                }
            }

            $total_chart_data = [];

            foreach ($data as $user_id => $results) {

                $total_war_points = 0;
                $total_style_points = 0;

                foreach ($results as $result) {
                    $total_war_points += $result['war_points'];
                    $total_style_points += $result['style_points'];
                }

                $total_chart_data[] = [
                    'user' => User::find($user_id)->with('nation')->first(),
                    'total_war_points' => $total_war_points,
                    'total_style_points' => $total_style_points,
                ];
            }

            usort($total_chart_data, function ($a, $b) {
                if ($a['total_war_points'] == $b['total_war_points']) {
                    return $b['total_style_points'] - $a['total_style_points'];
                }
                return $b['total_war_points'] - $a['total_war_points'];
            });

            $fake_date = date('Y-m-d H:i:s', strtotime("{$current_year}-01 00:00:00"));

            Chart::create([
                'note' => "$current_year chart",
                'data' => $total_chart_data,
                'created_at' =>  $fake_date,
            ]);
        }
    }
}
