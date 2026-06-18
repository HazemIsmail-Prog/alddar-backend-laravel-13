<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run()
    {
        // ============ ASSETS (1000-1999) ============
        $assets = ChartOfAccount::create([
            'account_code' => '1000',
            'account_name' => 'Assets',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'All company assets',
            'created_by' => 1,
        ]);

        // Current Assets
        $currentAssets = ChartOfAccount::create([
            'account_code' => '1100',
            'account_name' => 'Current Assets',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $assets->id,
            'level' => 1,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Assets convertible to cash within one year',
            'created_by' => 1,
        ]);

        // Cash and Bank Accounts
        ChartOfAccount::create([
            'account_code' => '1110',
            'account_name' => 'Cash on Hand',
            'account_type' => 'cash',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Physical cash in office',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1120',
            'account_name' => 'Cash in Bank - Operating',
            'account_type' => 'bank',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Main operating bank account',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1121',
            'account_name' => 'Cash in Bank - Payroll',
            'account_type' => 'bank',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Payroll bank account',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1122',
            'account_name' => 'Cash in Bank - Tax',
            'account_type' => 'bank',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Tax holding bank account',
            'created_by' => 1,
        ]);

        // Receivables
        ChartOfAccount::create([
            'account_code' => '1200',
            'account_name' => 'Accounts Receivable',
            'account_type' => 'accounts_receivable',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Customer outstanding invoices',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1210',
            'account_name' => 'Allowance for Doubtful Accounts',
            'account_type' => 'asset',
            'normal_balance' => 'credit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Contra-asset for bad debt estimation',
            'created_by' => 1,
        ]);

        // Prepayments
        ChartOfAccount::create([
            'account_code' => '1300',
            'account_name' => 'Prepaid Expenses',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Insurance, rent, subscriptions paid in advance',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1310',
            'account_name' => 'Prepaid Insurance',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Insurance premiums paid in advance',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1320',
            'account_name' => 'Deposits',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $currentAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Security and utility deposits',
            'created_by' => 1,
        ]);

        // Fixed Assets
        $fixedAssets = ChartOfAccount::create([
            'account_code' => '1400',
            'account_name' => 'Fixed Assets',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $assets->id,
            'level' => 1,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Long-term assets',
            'created_by' => 1,
                ]);

        ChartOfAccount::create([
            'account_code' => '1410',
            'account_name' => 'Land',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Land owned by company',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1420',
            'account_name' => 'Buildings',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Factory, warehouse, office buildings',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1430',
            'account_name' => 'Machinery & Equipment',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Woodworking machinery, CNC machines',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1440',
            'account_name' => 'Furniture & Fixtures',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Office furniture and fixtures',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1450',
            'account_name' => 'Vehicles',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Delivery trucks, company vehicles',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1460',
            'account_name' => 'Computer Equipment',
            'account_type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Computers, servers, IT equipment',
            'created_by' => 1,
        ]);

        // Accumulated Depreciation
        ChartOfAccount::create([
            'account_code' => '1480',
            'account_name' => 'Accumulated Depreciation - Buildings',
            'account_type' => 'asset',
            'normal_balance' => 'credit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Contra-asset for building depreciation',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '1481',
            'account_name' => 'Accumulated Depreciation - Equipment',
            'account_type' => 'asset',
            'normal_balance' => 'credit',
            'parent_id' => $fixedAssets->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Contra-asset for equipment depreciation',
            'created_by' => 1,
        ]);

        // ============ LIABILITIES (2000-2999) ============
        $liabilities = ChartOfAccount::create([
            'account_code' => '2000',
            'account_name' => 'Liabilities',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'All company liabilities',
            'created_by' => 1,
        ]);

        // Current Liabilities
        $currentLiabs = ChartOfAccount::create([
            'account_code' => '2100',
            'account_name' => 'Current Liabilities',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $liabilities->id,
            'level' => 1,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Liabilities due within one year',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2110',
            'account_name' => 'Accounts Payable',
            'account_type' => 'accounts_payable',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Vendor outstanding bills',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2120',
            'account_name' => 'Accrued Expenses',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Expenses incurred but not paid',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2130',
            'account_name' => 'Accrued Wages',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Employee wages earned but not paid',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2140',
            'account_name' => 'Sales Tax Payable',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Sales tax collected from customers',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2150',
            'account_name' => 'VAT Payable',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Value Added Tax payable',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2160',
            'account_name' => 'Income Tax Payable',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Corporate income tax payable',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2170',
            'account_name' => 'Customer Deposits',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Prepayments from customers',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2180',
            'account_name' => 'Short-term Loans',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $currentLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Loans due within one year',
            'created_by' => 1,
        ]);

        // Long-term Liabilities
        $longTermLiabs = ChartOfAccount::create([
            'account_code' => '2200',
            'account_name' => 'Long-term Liabilities',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $liabilities->id,
            'level' => 1,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Liabilities due after one year',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2210',
            'account_name' => 'Long-term Bank Loans',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $longTermLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Loans with maturity >1 year',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '2220',
            'account_name' => 'Mortgage Payable',
            'account_type' => 'liability',
            'normal_balance' => 'credit',
            'parent_id' => $longTermLiabs->id,
            'level' => 2,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Property mortgages',
            'created_by' => 1,
        ]);

        // ============ EQUITY (3000-3999) ============
        $equity = ChartOfAccount::create([
            'account_code' => '3000',
            'account_name' => 'Equity',
            'account_type' => 'equity',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Owner equity',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '3100',
            'account_name' => 'Owner Capital',
            'account_type' => 'equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Owner investments',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '3200',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Accumulated profits/losses',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '3300',
            'account_name' => 'Current Year Earnings',
            'account_type' => 'equity',
            'normal_balance' => 'credit',
            'parent_id' => $equity->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Current fiscal year profit/loss',
            'created_by' => 1,
        ]);

        // ============ REVENUE (4000-4999) ============
        $revenue = ChartOfAccount::create([
            'account_code' => '4000',
            'account_name' => 'Revenue',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'All income accounts',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4100',
            'account_name' => 'Sales Revenue - Furniture',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Revenue from furniture sales',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4110',
            'account_name' => 'Sales Revenue - Accessories',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Revenue from accessories and add-ons',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4120',
            'account_name' => 'Sales Revenue - Custom Orders',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Revenue from custom furniture orders',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4200',
            'account_name' => 'Sales Returns & Allowances',
            'account_type' => 'income',
            'normal_balance' => 'debit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Contra-revenue for returns',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4300',
            'account_name' => 'Sales Discounts',
            'account_type' => 'income',
            'normal_balance' => 'debit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Early payment discounts',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4400',
            'account_name' => 'Shipping & Handling Revenue',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Revenue from shipping charges',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '4500',
            'account_name' => 'Service Revenue',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'parent_id' => $revenue->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Assembly, delivery, installation services',
            'created_by' => 1,
        ]);

        // ============ COST OF GOODS SOLD (5000-5999) ============
        $cogs = ChartOfAccount::create([
            'account_code' => '5000',
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Direct costs of products sold',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '5100',
            'account_name' => 'COGS - Furniture',
            'account_type' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Direct cost of furniture sold',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '5110',
            'account_name' => 'COGS - Accessories',
            'account_type' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Direct cost of accessories sold',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '5200',
            'account_name' => 'Inventory Adjustments',
            'account_type' => 'cost_of_goods_sold',
            'normal_balance' => 'debit',
            'parent_id' => $cogs->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'Write-offs, damages, losses',
            'created_by' => 1,
        ]);

        // ============ EXPENSES (6000-7999) ============
        $expenses = ChartOfAccount::create([
            'account_code' => '6000',
            'account_name' => 'Expenses',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Operating expenses',
            'created_by' => 1,
        ]);

        // Operating Expenses
        ChartOfAccount::create([
            'account_code' => '6100',
            'account_name' => 'Raw Materials Purchases',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Wood, fabric, hardware purchases',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6110',
            'account_name' => 'Packaging Supplies',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Boxes, bubble wrap, pallets',
            'created_by' => 1,
        ]);

        // Labor Expenses
        ChartOfAccount::create([
            'account_code' => '6200',
            'account_name' => 'Direct Labor',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Production workers wages',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6210',
            'account_name' => 'Indirect Labor',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Supervisors, maintenance wages',
            'created_by' => 1,
        ]);

        // Rent & Utilities
        ChartOfAccount::create([
            'account_code' => '6300',
            'account_name' => 'Rent Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Factory and office rent',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6310',
            'account_name' => 'Utilities Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Electricity, water, gas, internet',
            'created_by' => 1,
        ]);

        // Marketing & Sales
        ChartOfAccount::create([
            'account_code' => '6400',
            'account_name' => 'Advertising Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Marketing campaigns, ads',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6410',
            'account_name' => 'Commission Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Sales commissions',
            'created_by' => 1,
        ]);

        // Shipping & Logistics
        ChartOfAccount::create([
            'account_code' => '6500',
            'account_name' => 'Freight & Shipping Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Outbound shipping costs',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6510',
            'account_name' => 'Delivery Vehicle Expenses',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Fuel, maintenance, insurance for delivery trucks',
            'created_by' => 1,
        ]);

        // Administrative
        ChartOfAccount::create([
            'account_code' => '6600',
            'account_name' => 'Office Supplies',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Stationery, printer supplies',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6610',
            'account_name' => 'Software Subscriptions',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'ERP, accounting, design software',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6620',
            'account_name' => 'Professional Fees',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Legal, accounting consulting',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6630',
            'account_name' => 'Bank Charges',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Bank fees, transaction fees',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6640',
            'account_name' => 'Insurance Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'General liability, property insurance',
            'created_by' => 1,
        ]);

        // Maintenance
        ChartOfAccount::create([
            'account_code' => '6700',
            'account_name' => 'Maintenance & Repairs',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Equipment and building maintenance',
            'created_by' => 1,
        ]);

        // Depreciation
        ChartOfAccount::create([
            'account_code' => '6800',
            'account_name' => 'Depreciation Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Annual depreciation of fixed assets',
            'created_by' => 1,
        ]);

        // Taxes
        ChartOfAccount::create([
            'account_code' => '6900',
            'account_name' => 'Property Tax Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Annual property taxes',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '6910',
            'account_name' => 'Business License Fees',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'parent_id' => $expenses->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Annual business licenses and permits',
            'created_by' => 1,
        ]);

        // ============ OTHER INCOME/EXPENSES (8000-8999) ============
        ChartOfAccount::create([
            'account_code' => '8100',
            'account_name' => 'Interest Income',
            'account_type' => 'income',
            'normal_balance' => 'credit',
            'level' => 0,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Interest earned on bank accounts',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '8200',
            'account_name' => 'Interest Expense',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Interest paid on loans',
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '8300',
            'account_name' => 'Gain/Loss on Asset Disposal',
            'account_type' => 'expense',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => true,
            'is_system_account' => false,
            'is_active' => true,
            'description' => 'Profit or loss from selling fixed assets',
            'created_by' => 1,
        ]);

        // ============ INVENTORY (9000-9999) ============
        $inventory = ChartOfAccount::create([
            'account_code' => '9000',
            'account_name' => 'Inventory',
            'account_type' => 'inventory',
            'normal_balance' => 'debit',
            'level' => 0,
            'is_leaf' => false,
            'is_system_account' => true,
            'is_active' => true,
            'description' => 'All inventory accounts',
            'created_by' => 1,
        ]);

        $rawMaterialsInventory = ChartOfAccount::create([
            'account_code' => '9100',
            'account_name' => 'Raw Materials Inventory',
            'account_type' => 'inventory',
            'normal_balance' => 'debit',
            'parent_id' => $inventory->id,
            'level' => 1,
            'is_leaf' => false,
            'is_system_account' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '9110',
            'account_name' => 'Work in Process Inventory',
            'account_type' => 'inventory',
            'normal_balance' => 'debit',
            'parent_id' => $rawMaterialsInventory->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '9120',
            'account_name' => 'Finished Goods Inventory',
            'account_type' => 'inventory',
            'normal_balance' => 'debit',
            'parent_id' => $rawMaterialsInventory->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);

        ChartOfAccount::create([
            'account_code' => '9130',
            'account_name' => 'Inventory in Transit',
            'account_type' => 'inventory',
            'normal_balance' => 'debit',
            'parent_id' => $rawMaterialsInventory->id,
            'level' => 1,
            'is_leaf' => true,
            'is_system_account' => true,
            'is_active' => true,
            'created_by' => 1,
        ]);
    }
}
