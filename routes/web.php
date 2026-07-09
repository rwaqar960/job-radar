<?php

use App\Http\Controllers\JobBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobBoardController::class, 'index'])->name('jobs.index');
