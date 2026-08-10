    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AirlineDiscountController;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\Api\OrderController;
    use App\Http\Controllers\Api\BankController;
    use App\Http\Controllers\AccountController;
    use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\InfoController;

    Route::post('/login', [
        AuthController::class,
        'login'
    ]);
        Route::get('/info', [InfoController::class, 'show']);

    Route::prefix('admin')->group(function () {
        Route::post('/login', [
            AuthController::class,
            'login'
        ]);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::patch('/orders/{id}', [OrderController::class, 'update']);
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::get(
            '/airline-discounts',
            [AirlineDiscountController::class, 'index']
        );
    });


    Route::prefix('banks')->group(function () {
        Route::get('/', [BankController::class, 'index']);
    });

    Route::middleware([
        'auth:sanctum',
        'admin',
    ])->prefix('admin')->group(function () {

        /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    */

        Route::get('/accounts', [
            AccountController::class,
            'index'
        ]);

        Route::post('/accounts', [
            AccountController::class,
            'store'
        ]);

        Route::get('/accounts/{user}', [
            AccountController::class,
            'show'
        ]);

        Route::put('/accounts/{user}', [
            AccountController::class,
            'update'
        ]);

        Route::delete('/accounts/{user}', [
            AccountController::class,
            'destroy'
        ]);
    });


    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [
            AuthController::class,
            'profile'
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout'
        ]);
        Route::put('/info', [InfoController::class, 'update']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::prefix('refunds')->group(function () {

            Route::get('/', [RefundController::class, 'index']);

            Route::post('/', [RefundController::class, 'store']);

            Route::get('/{id}', [RefundController::class, 'show']);

            Route::put('/{id}', [RefundController::class, 'update']);

            Route::post('/{id}/approve', [RefundController::class, 'approve']);

            Route::post('/{id}/reject', [RefundController::class, 'reject']);

            Route::delete('/{id}', [RefundController::class, 'destroy']);
        });
        Route::prefix('banks')->group(function () {
            Route::get('/{id}', [BankController::class, 'show']);
            Route::post('/', [BankController::class, 'store']);
            Route::put('/{id}', [BankController::class, 'update']);
            Route::delete('/{id}', [BankController::class, 'destroy']);
        });

        Route::prefix('airline-discounts')->group(function () {
            Route::post('/save-all', [AirlineDiscountController::class, 'saveAll']);
            Route::put('/default', [AirlineDiscountController::class, 'updateDefault']);
            Route::post('/restore', [AirlineDiscountController::class, 'restoreDefault']);
        });
    });
