<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERPNext API Configuration
    |--------------------------------------------------------------------------
    */

    'base_url' => env('ERPNEXT_BASE_URL', 'http://localhost:8000'),

    'api' => [
        'key' => env('ERPNEXT_API_KEY', ''),
        'secret' => env('ERPNEXT_API_SECRET', ''),
    ],

    'site' => env('ERPNEXT_SITE', 'erpnext.localhost'),

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */

    'sync' => [
        'enabled' => env('ERPNEXT_SYNC_ENABLED', true),
        'auto_sync_products' => env('ERPNEXT_AUTO_SYNC_PRODUCTS', true),
        'auto_sync_orders' => env('ERPNEXT_AUTO_SYNC_ORDERS', true),
        'auto_sync_inventory' => env('ERPNEXT_AUTO_SYNC_INVENTORY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | ERPNext Modules
    |--------------------------------------------------------------------------
    */

    'warehouse' => env('ERPNEXT_WAREHOUSE', 'Stores - OCC'),

    'company' => env('ERPNEXT_COMPANY', 'OnClick2Buy'),

    'customer_group' => env('ERPNEXT_CUSTOMER_GROUP', 'All Customer Groups'),

    'territory' => env('ERPNEXT_TERRITORY', 'All Territories'),

    'item_group' => env('ERPNEXT_ITEM_GROUP', 'All Item Groups'),

    'cost_center' => env('ERPNEXT_COST_CENTER', 'Cost Center - OCC'),

    'income_account' => env('ERPNEXT_INCOME_ACCOUNT', 'Income - OCC'),

    'expense_account' => env('ERPNEXT_EXPENSE_ACCOUNT', 'Cost of Goods Sold - OCC'),

    'accounts_receivable' => env('ERPNEXT_ACCOUNTS_RECEIVABLE', 'Accounts Receivable - OCC'),

];
