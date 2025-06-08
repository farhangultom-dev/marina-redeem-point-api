<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ArticleController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/users', UserController::class);
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/check-otp', [UserController::class, 'checkOtp']);
Route::post('/change-password', [UserController::class, 'changePassword']);

Route::get('/banners', [BannerController::class, 'getBanner']);
Route::post('/add-banner', [BannerController::class, 'addBanner']);

Route::get('/articles', [ArticleController::class, 'getArticle']);
Route::post('/add-article', [ArticleController::class, 'addArticle']);
