<?php

/*
|--------------------------------------------------------------------------
| Laravel 2-Step Verification Web Routes
|--------------------------------------------------------------------------
*/

use CodeBros\TwoStep\Http\Controllers\TwoStepController;

Route::group([
    'prefix' => 'verification',
    'as' => 'laravel-two-step::',
    'namespace' => 'CodeBros\TwoStep\Http\Controllers',
    'middleware' => ['web'],
], function () {
    Route::get('needed', [TwoStepController::class, 'showVerification'])->name('verificationNeeded');
    Route::post('verify', [TwoStepController::class, 'verify'])->name('verify');
    Route::post('resend', [TwoStepController::class, 'resend'])->name('resend');
}
);
