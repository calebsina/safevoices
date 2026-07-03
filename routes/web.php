<?php

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Serve the UI
Scramble::registerUiRoute(path: 'docs/v1', api: 'default');

// Serve the JSON spec
Scramble::registerJsonSpecificationRoute(path: 'docs/v1/openapi.json', api: 'default');
