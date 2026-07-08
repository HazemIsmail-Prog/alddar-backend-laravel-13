<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DispatchingController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLevelController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DispatchingHistoryController;
use App\Http\Controllers\DropdownListController;
use App\Http\Controllers\ContractController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth:sanctum', 'is_active'],
], function () {

    // Auth
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/login', [AuthController::class, 'login'])
        ->withoutMiddleware(['auth:sanctum', 'is_active']);

    Route::apiResource('users', UserController::class)->except(['show']);
    Route::apiResource('departments', DepartmentController::class)->except(['show']);
    Route::apiResource('permissions', PermissionController::class)->except(['show']);
    Route::apiResource('roles', RoleController::class)->except(['show']);
    Route::apiResource('warehouses', WarehouseController::class)->except(['show']);
    Route::apiResource('categories', CategoryController::class)->except(['show']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('parties', PartyController::class)->except(['show']);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('dispatching-histories/{order}', DispatchingHistoryController::class)->only(['index']);
    Route::apiResource('order-statuses', OrderStatusController::class)->except(['show']);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('stock-movements', StockMovementController::class)->except(['show']);
    Route::apiResource('stock-levels', StockLevelController::class)->only(['index']);
    Route::get('stock-levels/{product}/{warehouse}', [StockLevelController::class, 'getStockLevel']);
    Route::apiResource('stock-transfers', StockTransferController::class)->except(['show']);
    Route::apiResource('stock-adjustments', StockAdjustmentController::class)->except(['show']);
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class)->except(['show']);
    Route::apiResource('journals', JournalController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('comments', CommentController::class)->only(['index','store','show']);
    Route::apiResource('attachments', AttachmentController::class)->only(['index','store','destroy']);
    Route::apiResource('contracts', ContractController::class);


    // Financial Reports
    Route::controller(FinancialReportController::class)->group(function () {
        Route::get('financial-reports/trial-balance', 'trialBalance');
        Route::get('financial-reports/balance-sheet', 'balanceSheet');
        Route::get('financial-reports/income-statement', 'incomeStatement');
        Route::get('financial-reports/general-ledger', 'generalLedger');
        Route::get('financial-reports/aged-receivables', 'agedReceivables');
        Route::get('financial-reports/aged-payables', 'agedPayables');
        Route::get('financial-reports/cash-flow', 'cashFlow');
    });

    // Dispatching
    Route::controller(DispatchingController::class)->group(function () {
        Route::get('dispatching/{department}', 'index');
        Route::get('dispatching/technician/orders', 'getTechnicianOrders');
        Route::put('dispatching/{order}/set-holded', 'setOrderHolded');
        Route::put('dispatching/{order}/set-unassigned', 'setOrderUnassigned');
        Route::put('dispatching/{order}/set-cancelled', 'setOrderCancelled');
        Route::put('dispatching/{order}/assign-technician', 'assignTechnician');
        Route::put('dispatching/{order}/set-received', 'setOrderReceived');
        Route::put('dispatching/{order}/set-reached', 'setOrderReached');
        Route::put('dispatching/{order}/set-completed', 'setOrderCompleted');
        // update order sort number
        Route::put('dispatching/{order}/update-sort-number', 'updateOrderSortNumber');
    });

    // Counters
    Route::controller(CounterController::class)->group(function () {
        Route::get('counters/un-invoiced-completed-orders', 'unInvoicedCompletedOrders');
    });

    // Dropdown Lists
    Route::controller(DropdownListController::class)->group(function () {
        Route::get('dropdown-list/permissions', 'permissions');
        Route::get('dropdown-list/roles', 'roles');
        Route::get('dropdown-list/departments', 'departments');
        Route::get('dropdown-list/products', 'products');
        Route::get('dropdown-list/warehouses', 'warehouses');
        Route::get('dropdown-list/users', 'users');
        Route::get('dropdown-list/chart-of-accounts', 'chartsOfAccounts');
        Route::get('dropdown-list/categories', 'categories');
    });

});
