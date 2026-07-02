<?php

use App\Http\Controllers\Mcp\McpToolsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('test',function () {
    return 'welcome';
});


Route::get('mcp/{integrationId}/tools', [McpToolsController::class, 'index']);
