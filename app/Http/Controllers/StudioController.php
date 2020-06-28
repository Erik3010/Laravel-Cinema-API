<?php

namespace App\Http\Controllers;

use App\Studio;
use App\Branch;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class StudioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $studios = Studio::all();

        foreach($studios as $studio) {
            $data[] = [
                'id' => $studio->id,
                'name' => $studio->name,
                'basic_price' => $studio->basic_price,
                'additional_friday_price' => $studio->additional_friday_price,
                'additional_saturday_price' => $studio->additional_saturday_price,
                'additional_sunday_price' => $studio->additional_sunday_price,
                'branch_name' => Branch::find($studio->branch_id)->name
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
            'name' => 'required',
            'branch_id' => 'required|exists:branches,id',
            'basic_price' => 'required|between:1,1000000',
            'additional_friday_price' => 'required|between: 0,1000000',
            'additional_saturday_price' => 'required|between: 0,1000000',
            'additional_sunday_price' => 'required|between: 0,1000000'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid field'], 422);

        $param = $request->all();
        $param = Arr::except($param, ['token']);

        Studio::create($param);

        return response()->json(['message' => 'create studio success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function show(Studio $studio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function edit(Studio $studio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Studio $studio)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'branch_id' => 'required|exists:branches,id',
            'basic_price' => 'required|between:1,1000000',
            'additional_friday_price' => 'required|between: 0,1000000',
            'additional_saturday_price' => 'required|between: 0,1000000',
            'additional_sunday_price' => 'required|between: 0,1000000'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid field'], 422);

        $param = $request->all();
        $param = Arr::except($param, ['token']);

        $studio->update($param);

        return response()->json(['message' => 'update studio success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Studio  $studio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Studio $studio)
    {
        $studio->delete();
        return response()->json(['message' => 'delete studio success']);
    }
}
