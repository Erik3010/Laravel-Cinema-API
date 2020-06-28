<?php

namespace App\Http\Controllers;

use App\Movie;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $movie = Movie::all();

        return response()->json($movie, 200);
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
            'minute_length' => 'required|between:1,999'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid field'], 422);

        $picture = $request->file('picture');
        $pictureName = $picture->getClientOriginalName();
        $picture->move(public_path('images'), $pictureName);

        $param = $request->all();
        $param = Arr::except($param, ['token','picture']);
        $param['picture_url'] = $pictureName;

        Movie::create($param);

        return response()->json(['message' => 'create movie success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function edit(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Movie $movie)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'minute_length' => 'required|between:1,999'
        ]);

        if($validate->fails())
            return response()->json(['message' => 'invalid field'], 422);

        $picture = $request->file('picture');
        $pictureName = $picture->getClientOriginalName();
        $picture->move(public_path('/images'), $pictureName);

        $param = $request->all();
        $param = Arr::except($param, ['token','picture']);
        $param['picture_url'] = $pictureName;

        $movie->update($param);

        return response()->json(['message' => 'movie update success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        return response()->json(['message' => 'delete movie success'], 200);
    }
}
