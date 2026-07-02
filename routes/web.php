<?php

use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', StatusController::class);
