<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\PartnertshipController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\CategoryPartnerController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/users', UserController::class);
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/check-otp', [UserController::class, 'checkOtp']);
Route::post('/change-password', [UserController::class, 'changePassword']);
Route::post('/update-user', [UserController::class, 'updateUser']);

Route::get('/banners', [BannerController::class, 'getBanner']);
Route::post('/add-banner', [BannerController::class, 'addBanner']);
Route::post('/delete-banner', [BannerController::class, 'deleteBanner']);

Route::get('/articles', [ArticleController::class, 'getArticle']);
Route::get('/article-by-id', [ArticleController::class, 'getArticleById']);
Route::post('/add-article', [ArticleController::class, 'addArticle']);

Route::post('/add-partnertship', [PartnertshipController::class, 'addPartnertship']);
Route::get('/get-partners', [PartnertshipController::class, 'getPartners']);
Route::get('/get-partners-by-category', [PartnertshipController::class, 'getPartnersByCategory']);
Route::get('/get-category-partners', [PartnertshipController::class, 'getCategoryPartners']);
Route::post('/update-partnertship', [PartnertshipController::class, 'updatePartnertship']);
Route::post('/update-photo-partnertship', [PartnertshipController::class, 'updatePhotoPartnertship']);
Route::post('/delete-partner', [PartnertshipController::class, 'deletePartner']);

Route::get('/get-voucher-by-category', [VoucherController::class, 'getVoucherByCategory']);
Route::post('/add-voucher', [VoucherController::class, 'addVoucher']);
Route::get('/get-voucher-code', [VoucherController::class, 'getVoucherCode']);
Route::post('/scan-voucher', [VoucherController::class, 'scanVoucherCode']);
Route::post('/approval-voucher', [VoucherController::class, 'approvalVoucher']);
Route::post('/delete-voucher', [VoucherController::class, 'deleteVoucher']);
Route::post('/update-voucher', [VoucherController::class, 'updateVoucher']);
Route::get('/get-voucher-by-partner', [VoucherController::class, 'getVoucherByPartner']);

Route::get('/get-category', [CategoryPartnerController::class, 'getCategory']);

Route::get('/get-history', [HistoryController::class, 'getHistory']);
Route::get('/get-history-partnership', [HistoryController::class, 'getHistoryPartnership']);
Route::get('/get-notification', [HistoryController::class, 'getNotification']);