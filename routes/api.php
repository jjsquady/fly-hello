<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', function () {
    return \App\Models\User::all();
});

Route::post('/users', function (\Illuminate\Http\Request $request) {

    $validator = Validator::make($request->all(),[
        'name' => 'required',
        'email' => 'required',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $request['password'] = \Illuminate\Support\Facades\Hash::make($request->get('password'));

    try {
        App\Models\User::create($request->all());
    }catch (\Exception $exception) {
        return response()->json(['errors' => $exception], 500);
    }

    return response()->json([], 201);
});

