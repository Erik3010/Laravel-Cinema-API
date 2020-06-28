<?php

namespace App\Http\Controllers;

use App\Schedules;
use App\Movie;
use App\Studio;
use App\Branch;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class SchedulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = Schedules::all();

        foreach($schedules as $schedule) {
            $studio = Studio::find($schedule->studio_id);
            $data[] = [
                'id' => $schedule->id,
                'movie_name' => Movie::find($schedule->movie_id)->name,
                'studio_name' => $studio->name,
                'branch_name' => Branch::find($studio->branch_id)->name,
                'start_time' => $schedule->start,
                'end_time' => $schedule->end,
                'price' => $schedule->price
            ];
        }

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'studio_id' => 'required|exists:studios,id',
            'movie_id' => 'required|exists:movies,id',
            'start' => 'required'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid message'], 422);

        $movie = Movie::find($request->movie_id);
        $studio = Studio::find($request->studio_id);

        $movie_length = $movie->minute_length;
        $price = $studio->basic_price;
        $priceFri = $studio->additional_friday_price;
        $priceSat = $studio->additional_saturday_price;
        $priceSun = $studio->additional_sunday_price;

        $start = strtotime($request->start);
        $end = date('Y-m-d H:i:s', strtotime("+$movie_length minutes", $start));

        $day = date('D', $start);

        if($day === "Fri") $price = $price + $priceFri;
        else if($day === "Sat") $price = $price + $priceSat;
        else if ($day === "Sun") $price = $price + $priceSun;

        $overlap = false;
        $schedules = Schedules::all();

        $startTime = date('Y-m-d H:i:s', $start);

        foreach($schedules as $schedule) {
            if($startTime < $schedule->end && $end > $schedule->start) {
                $overlap = true;
                break;
            }
        }

        if($overlap) return response()->json(['message' => 'schedule overlapped'], 400);

        Schedules::create([
            'studio_id' => $request->studio_id,
            'movie_id'=> $request->movie_id,
            'start' => $startTime,
            'end' => $end,
            'price' => $price
        ]);

        return response()->json(['message' => 'create schedule success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Schedules  $schedules
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $schedules = Schedules::groupBy('movie_id', 'price');

        if(isset($request->date) && $request->date != "") {
            $schedules = $schedules->where('start', $request->date);
        }

        if(isset($request->branch_id) && $request->branch_id != "") {
            $studio = Studio::where('branch_id', $request->branch_id)->pluck('id');
            $schedules = $schedules->whereIn('studio_id', $studio);
        }

        $schedulesAll = $schedules->get();

        foreach($schedulesAll as $schedule) {
            if($request->date != "" || ($request->date == "" && $request->branch_id == "") ) {
                $sch = Schedules::where([
                    'movie_id' => $schedule->movie_id,
                    'price' => $schedule->price
                ])->get();
            }else{
                $sch = Schedules::where([
                    'movie_id' => $schedule->movie_id,
                    'price' => $schedule->price,
                    'studio_id' => $schedule->studio_id
                ])->get();
            }

            foreach($sch as $s) {
                $date = date('H:i', strtotime($s['start']));
                $start[] = $date;
            }

            $data[] = [
                'name' => Movie::find($schedule->movie_id)->name,
                'price' => $schedule->price,
                'start' => $start
            ];
            $start = [];
        }

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Schedules  $schedules
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedules $schedules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Schedules  $schedules
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $schedules = Schedules::find($id);

        $validate = Validator::make($request->all(), [
            'studio_id' => 'required|exists:studios,id',
            'movie_id' => 'required|exists:movies,id',
            'start' => 'required'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid field'], 422);

        $movie = Movie::find($request->movie_id);
        $studio = Studio::find($request->studio_id);

        $price = $studio->basic_price;
        $minute_length = $movie->minute_length;

        $start = strtotime($request->start);
        $day = date('D', $start);

        if($day === 'Fri') $price += $studio->additional_friday_price;
        else if ($day === 'Sat') $price += $studio->additional_saturday_price;
        else if ($day === 'Sun') $price += $studio->additional_sunday_price;

        $end = date('Y-m-d H:i:s', strtotime("+$minute_length minutes", $start));

        $overlap = false;
        $schedulesArr = Schedules::all();
        $startTime = date('Y-m-d H:i:s', $start);

        foreach($schedulesArr as $schedule) {
            if($startTime < $schedule->end && $end > $schedule->start) {
                $overlap = true;
                break;
            }
        }

        if($overlap)
            return response()->json(['message' => 'schedule overlapped'], 400);

        $param = $request->all();
        $param = Arr::except($param, ['token']);
        $param['end'] = $end;
        $param['price'] = $price;

        $schedules->update($param);

        return response()->json(['message' => 'update schedule success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Schedules  $schedules
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = Schedules::find($id);
        $schedule->delete();

        return response()->json(['message' => 'delete schedule success'], 200);
    }
}
