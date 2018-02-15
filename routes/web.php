<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('emails.index');
});
Route::get('/inboxes/{email}', function ($email) {
    return view('inboxes.index')->withEmail($email);
});
Route::get('/senders/{email}', function ($email) {
    return view('senders.index')->withEmail($email);
});

Route::get('/emails/{id}', function ($id) {
    return view('emails.show')->withId($id);
});

Auth::routes();

Route::get('/statistics', function () {
    return view('statistics.index');
});
