<?php

use Illuminate\Http\Request;
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

Route::group(
    [
        'prefix' => 'v1',
        'namespace' => 'App\Modules\Api\V1\Controllers'
    ],
    function () {
        //user routes

        Route::group(
            ['prefix' => 'account'],
            function () {
                Route::post('/signup', 'UserController@signUp')->name('user.signup');
                Route::post('/signin', 'UserController@signIn')->name('user.signin');
            }
        );

        Route::group(
            [
                'prefix' => 'user',
                'middleware' => ['v1.validate.user']
            ],
            function () {
                Route::get('/{id}', 'UserController@profile')->name('user.profile')
                    ->where('id', '[0-9]+');
                Route::put('/profile/update/personal-information', 'UserController@updatePersonalInformation')
                    ->name('profile.update.personal-information');
                Route::put('/profile/update/business-information', 'UserController@updateBusinessInformation')
                    ->name('profile.update.business-information');
                Route::post('/picture/upload', 'UserController@updateProfilePicture')
                    ->name('user.profile-picture.upload');
                Route::put('/password/change', 'UserController@changePassword')
                    ->name('user.password.change');
            }
        );

        Route::group(
            ['prefix' => 'category'],
            function () {
                Route::get('/', 'CategoryController@index')->name('category.index');
                Route::get('/{id}', 'CategoryController@categoryDetails')->name('category.details')->where('id', '[0-9]+');
                Route::get('/{id}/sub-categories', 'CategoryController@subCategories')
                    ->name('sub-categories.index')->where('id', '[0-9]+');
                Route::get('/{id}/sub-category/{subId}', 'CategoryController@subCategoryDetails')
                    ->name('sub-category.details')->where('id', '[0-9]+')
                    ->where('subId', '[0-9]+');
            }
        );

        //admin routes

        Route::group(
            ['prefix' => 'admin'],
            function () {
                Route::group(
                    ['prefix' => 'category'],
                    function () {
                        Route::post('/', 'CategoryController@addCategory')->name('category.add');
                        Route::post('/{id}', 'CategoryController@updateCategory')->name('category.update')->where('id', '[0-9]+');
                        Route::post('/sub-category', 'CategoryController@addSubCategory')
                            ->name('sub-category.add')->where('id', '[0-9]+');
                        Route::put('/sub-category/{subId}', 'CategoryController@updateSubCategory')
                            ->name('sub-category.update')->where('id', '[0-9]+')
                            ->where('subId', '[0-9]+');
                    }
                );
            }
        );
    }
);
