<?php

use App\Http\Controllers\DailyDigestController;
use App\Http\Controllers\ProjectionController;
use App\Http\Controllers\PushController;
use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\AuthController;

// Api Controllers
use App\Http\Controllers\Api\{SavingTransactionController,
    UserController as ApiUserController,
    AccountController as ApiAccountController,
    CardController as ApiCardController,
    TransactionCategoryController as ApiTransactionCategoryController,
    InvoiceController as ApiInvoiceController,
    InvoiceItemController as ApiInvoiceItemController,
    RecurrentController as ApiRecurrentController,
    SavingController as ApiSavingController,
    TransactionController as ApiTransactionController,
    InvestmentController as ApiInvestmentController
};

// Web Controllers
use App\Http\Controllers\Web\{ChartController,
    UserController as WebUserController,
    AccountController as WebAccountController,
    CardController as WebCardController,
    DashboardController as WebDashboardController,
    NotificationController as WebNotificationController,
    TransactionCategoryController as WebTransactionCategoryController,
    TransactionController as WebTransactionController,
    SavingController as WebSavingController,
    InvoiceController as WebInvoiceController,
    InvoiceItemController as WebInvoiceItemController,
    InvestmentController as WebInvestmentController,
    PaymentController as WebPaymentController,
    SupportController as WebSupportController
};

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'welcome']);
    Route::get('/login', [AuthController::class, 'welcome']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
    Route::get('/register', [AuthController::class, 'registerView'])->name('register-view');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::any('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::middleware(['auth', config('jetstream.auth_session')])->group(function () {
    Route::middleware('partial')->group(function () {
        // Dashboard
        Route::get('/dashboard', [WebDashboardController::class, 'dashboard'])->name('dashboard');

        // Dashboard Data
        Route::get('/dashboard/kpis', [WebDashboardController::class, 'kpis'])->name('dashboard.kpis');
        Route::post('/transactions/{transaction}/payment', [WebDashboardController::class, 'paymentTransaction'])->name('transaction-payment');

        // Users
        Route::get('/user', [WebUserController::class, 'index'])->name('user-view.index');
        Route::resource('users', ApiUserController::class)->scoped(['user' => 'uuid']);

        // Accounts
        Route::get('/account', [WebAccountController::class, 'index'])->name('account-view.index');
        Route::post('/accounts/transfer', [ApiAccountController::class, 'transfer'])->name('accounts.transfer');
        Route::resource('accounts', ApiAccountController::class)->scoped(['account' => 'uuid']);

        // Cards
        Route::get('/card', [WebCardController::class, 'index'])->name('card-view.index');
        Route::resource('cards', ApiCardController::class)->scoped(['card' => 'uuid']);

        // Transaction Categories
        Route::get('/transaction-category', [WebTransactionCategoryController::class, 'index'])->name('transactionCategory-view.index');
        Route::resource('transaction-categories', ApiTransactionCategoryController::class)->scoped(['transactionCategory' => 'uuid']);

        // Transactions
        Route::get('/transaction', [WebTransactionController::class, 'index'])->name('transaction-view.index');
        Route::get('/transaction/custom-item-projections', [WebTransactionController::class, 'custom-item-projections']);
        Route::resource('transactions', ApiTransactionController::class)->scoped(['transaction' => 'uuid']);

        //Savings
        Route::get('/saving', [WebSavingController::class, 'index'])->name('saving-view.index');
        Route::resource('savings', ApiSavingController::class)->scoped(['saving' => 'uuid']);
        Route::post('/savings/{saving}/deposit', [SavingTransactionController::class, 'deposit']);
        Route::post('/savings/{saving}/withdraw', [SavingTransactionController::class, 'withdraw']);

        Route::resource('recurrents', ApiRecurrentController::class)->scoped(['recurrent' => 'uuid']);

        //Invoices
        Route::get('/invoice/{cardId}', [WebInvoiceController::class, 'index'])->name('invoice-view.index');
        Route::get('/invoice/{cardId}/{ym}', [WebInvoiceController::class, 'show'])->name('invoice-view.show'); // ym = 'Y-m'
        Route::any('/invoice/payment/{cardId}/{ym}', [WebInvoiceController::class, 'update'])->name('invoice-payment.update'); // ym = 'Y-m'
        Route::resource('invoices', ApiInvoiceController::class)->scoped(['invoice' => 'uuid']);

        //InvoiceItem
        Route::get('/invoice-item', [WebInvoiceItemController::class, 'index'])->name('invoiceItem-view.index');
        Route::resource('invoice-items', ApiInvoiceItemController::class)->scoped(['invoiceItem' => 'uuid']);

        //Investments
        Route::get('/investment', [WebInvestmentController::class, 'index'])->name('investment-view.index');
        Route::resource('investments', ApiInvestmentController::class)->scoped(['investment' => 'uuid']);

        // Projection
        Route::get('/projection', [ProjectionController::class, 'index'])->name('projection-view.index');
        Route::get('/projection/data', [ProjectionController::class, 'data'])->name('projection.data');

        // Notifications
        Route::get('notifications', [WebNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}/read', [WebNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('notifications/{notification}', [WebNotificationController::class, 'destroy'])->name('notifications.destroy');

        // Notifications Push
        Route::post('/push/subscribe', [PushController::class, 'subscribe']);
        Route::get('/push/teste', [PushController::class, 'showForm'])->name('test.push');
        Route::post('/push/teste', [PushController::class, 'send']);
        Route::get('/push/debug', [PushController::class, 'page'])->name('push.debug');
        Route::post('/push/debug/send', [PushController::class, 'sender'])->name('push.debug.send');

        // Calendar
        Route::get('/calendar/events', [WebDashboardController::class, 'calendarEvents'])->name('calendar.events');

        // Digest
        Route::get('/lancamentos-do-dia', [DailyDigestController::class, 'index'])->name('digest.index');

        // Charts
        Route::get('/api/analytics/pie', [ChartController::class, 'pie'])->name('analytics.pie');

        Route::prefix('support')
            ->name('support.')
            ->group(function () {
                Route::get('/', [WebSupportController::class, 'index'])->name('index');
                Route::get('/{slug}', [WebSupportController::class, 'article'])->name('article');

                // somente para "outros" (envio do formulário)
                Route::post('/outros', [WebSupportController::class, 'storeOther'])->name('outros.store');
            });
    });
});


// Push Notifications
Route::get('/vapid-public-key', fn() => response(trim(env('VAPID_PUBLIC_KEY')), 200, ['Content-Type' => 'text/plain']));
