<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/register', 'register')->name('auth.register');
            Route::post('/login', 'login')->name('auth.login');
            Route::post('/forgot-password', 'forgotPassword')->name('auth.forgot_password');
            Route::post('/reset-password', 'resetPassword')->name('auth.reset_password');
        });
    });

    Route::middleware(['auth:sanctum', 'is_verified_user'])->prefix('users')->group(function () {
        Route::controller(AdsController::class)->prefix('ads')->group(function () {
            Route::get('/', 'myAds')->name('ads.myAds');
            Route::post('/', 'store')->name('ads.store');
            Route::put('/{id}', 'update')->name('ads.update');
            Route::delete('/{id}', 'delete')->name('ads.delete');
            Route::post('/{adsId}/pictures', 'uploadPictures')->name('ads.pictures.upload');
            Route::delete('/{adsId}/pictures/{pictureId}', 'deletePicture')->name('ads.pictures.delete');
            Route::post('/{adsId}/sort-options', 'storeSortOptions')->name('ads.store.sort-options');
            //Route::post('/{id}/reviews', 'storeReviews')->name('ads.reviews.store');
        });

        Route::controller(UserController::class)->group(function () {
            Route::get('/{slug}', 'profile')->name('user.profile');

            Route::prefix('profile')->group(function () {
                Route::post('/update/picture', 'updateProfilePicture')->name('user.update.profile.picture');
                Route::put('/update/personal-information', 'updateProfile')->name('user.update.profile');
                Route::put('/update/business-information', 'updateBusinessProfile')->name('user.update.profile.business');
                Route::put('/update/password', 'updatePassword')->name('user.update.password');
                Route::get('/logout', 'logout')->name('user.logout');
            });
        });
    });

    Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::get('/sort-options', [CategoryController::class, 'allSortOptions'])->name('sort_options.index');
        Route::get('/sort-option-values/{sortOptionId}', [CategoryController::class, 'sortOptionValues'])->name('sort_options.values');

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::post('/', 'store')->name('category.store');
            Route::post('/{slug}', 'update')->name('category.update');
        });

        Route::controller(CategoryController::class)->prefix('sub-categories')->group(function () {
            Route::post('/', 'addSubCategory')->name('sub_category.store');
            Route::put('/{slug}', 'updateSubCategory')->name('sub_category.update');
            Route::post('/sort-options/{subCategoryId}', 'storeSortOptions')->name('sub_category.sort_options.store');
            Route::put('/sort-options/{subCategoryId}', 'updateSortOptions')->name('sub_category.sort_options.update');
        });
    });

    Route::prefix('ads')->group(function () {
        Route::controller(AdsController::class)->group(function () {
            Route::get('/', 'index')->name('ads.index');
            Route::get('/{id}', 'view')->name('ads.view');
            Route::get('/category/{id}', 'categoryAds')->name('category.ads');
            Route::get('/category/{id}/sub-category/{subId}', 'subCategoryAds')->name('sub_category.ads');
        });
    });

    Route::prefix('categories')->group(function () {
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/', 'index')->name('category.index');
            Route::get('/{id}/sub-categories', 'subCategories')->name('sub_categories.index');
        });
    });

    Route::prefix('sub-categories')->group(function () {
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/{id}/sort-options', 'subCategorySortOptions')->name('sub_categories.sort-options');
        });
    });
});
