<?php

use App\Http\Controllers\Api\NewsIngestionController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::post('/news', [NewsIngestionController::class, 'store'])
    ->middleware(['news-bot.token', 'throttle:news-ingest'])
    ->name('news.store');

Route::post('/news/{id}/view', [NewsController::class, 'incrementView'])
    ->middleware(['throttle:news-views'])
    ->name('news.increment-view');
