<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'value' => 'dashboard_view',
                'name_en' => 'dashboard_view',
                'name_ar' => 'عرض لوحة التحكم',
            ],
            [
                'value' => 'permissions_view',
                'name_en' => 'permissions_view',
                'name_ar' => 'عرض الصلاحيات',
            ],
            [
                'value' => 'permissions_create',
                'name_en' => 'permissions_create',
                'name_ar' => 'إنشاء الصلاحيات',
            ],
            [
                'value' => 'permissions_update',
                'name_en' => 'permissions_update',
                'name_ar' => 'تحديث الصلاحيات',
            ],
            [
                'value' => 'permissions_delete',
                'name_en' => 'permissions_delete',
                'name_ar' => 'حذف الصلاحيات',
            ],
            [
                'value' => 'users_view',
                'name_en' => 'users_view',
                'name_ar' => 'عرض المستخدمين',
            ],
            [
                'value' => 'users_create',
                'name_en' => 'users_create',
                'name_ar' => 'إنشاء المستخدمين',
            ],
            [
                'value' => 'users_update',
                'name_en' => 'users_update',
                'name_ar' => 'تحديث المستخدمين',
            ],
            [
                'value' => 'users_delete',
                'name_en' => 'users_delete',
                'name_ar' => 'حذف المستخدمين',
            ],
            [
                'value' => 'roles_view',
                'name_en' => 'roles_view',
                'name_ar' => 'عرض الأدوار',
            ],
            [
                'value' => 'roles_create',
                'name_en' => 'roles_create',
                'name_ar' => 'إنشاء الأدوار',
            ],
            [
                'value' => 'roles_update',
                'name_en' => 'roles_update',
                'name_ar' => 'تحديث الأدوار',
            ],
            [
                'value' => 'roles_delete',
                'name_en' => 'roles_delete',
                'name_ar' => 'حذف الأدوار',
            ],
            [
                'value' => 'clients_view',
                'name_en' => 'clients_view',
                'name_ar' => 'عرض العملاء',
            ],
            [
                'value' => 'clients_create',
                'name_en' => 'clients_create',
                'name_ar' => 'إنشاء العملاء',
            ],
            [
                'value' => 'clients_update',
                'name_en' => 'clients_update',
                'name_ar' => 'تحديث العملاء',
            ],
            [
                'value' => 'clients_delete',
                'name_en' => 'clients_delete',
                'name_ar' => 'حذف العملاء',
            ],
            [
                'value' => 'vendors_view',
                'name_en' => 'vendors_view',
                'name_ar' => 'عرض الموردين',
            ],
            [
                'value' => 'vendors_create',
                'name_en' => 'vendors_create',
                'name_ar' => 'إنشاء الموردين',
            ],
            [
                'value' => 'vendors_update',
                'name_en' => 'vendors_update',
                'name_ar' => 'تحديث الموردين',
            ],
            [
                'value' => 'vendors_delete',
                'name_en' => 'vendors_delete',
                'name_ar' => 'حذف الموردين',
            ],
            [
                'value' => 'warehouses_view',
                'name_en' => 'warehouses_view',
                'name_ar' => 'عرض المخازن',
            ],
            [
                'value' => 'warehouses_create',
                'name_en' => 'warehouses_create',
                'name_ar' => 'إنشاء المخازن',
            ],
            [
                'value' => 'warehouses_update',
                'name_en' => 'warehouses_update',
                'name_ar' => 'تحديث المخازن',
            ],
            [
                'value' => 'warehouses_delete',
                'name_en' => 'warehouses_delete',
                'name_ar' => 'حذف المخازن',
            ],
            [
                'value' => 'departments_view',
                'name_en' => 'departments_view',
                'name_ar' => 'عرض الأقسام',
            ],
            [
                'value' => 'departments_create',
                'name_en' => 'departments_create',
                'name_ar' => 'إنشاء الأقسام',
            ],
            [
                'value' => 'departments_update',
                'name_en' => 'departments_update',
                'name_ar' => 'تحديث الأقسام',
            ],
            [
                'value' => 'departments_delete',
                'name_en' => 'departments_delete',
                'name_ar' => 'حذف الأقسام',
            ],
            [
                'value' => 'categories_view',
                'name_en' => 'categories_view',
                'name_ar' => 'عرض الفئات',
            ],
            [
                'value' => 'categories_create',
                'name_en' => 'categories_create',
                'name_ar' => 'إنشاء الفئات',
            ],
            [
                'value' => 'categories_update',
                'name_en' => 'categories_update',
                'name_ar' => 'تحديث الفئات',
            ],
            [
                'value' => 'categories_delete',
                'name_en' => 'categories_delete',
                'name_ar' => 'حذف الفئات',
            ],
            [
                'value' => 'products_view',
                'name_en' => 'products_view',
                'name_ar' => 'عرض المنتجات',
            ],
            [
                'value' => 'products_create',
                'name_en' => 'products_create',
                'name_ar' => 'إنشاء المنتجات',
            ],
            [
                'value' => 'products_update',
                'name_en' => 'products_update',
                'name_ar' => 'تحديث المنتجات',
            ],
            [
                'value' => 'products_delete',
                'name_en' => 'products_delete',
                'name_ar' => 'حذف المنتجات',
            ],
            [
                'value' => 'sales_orders_view',
                'name_en' => 'sales_orders_view',
                'name_ar' => 'عرض الطلبات المبيعات',
            ],
            [
                'value' => 'sales_orders_create',
                'name_en' => 'sales_orders_create',
                'name_ar' => 'إنشاء الطلبات المبيعات',
            ],
            [
                'value' => 'sales_orders_update',
                'name_en' => 'sales_orders_update',
                'name_ar' => 'تحديث الطلبات المبيعات',
            ],
            [
                'value' => 'sales_orders_delete',
                'name_en' => 'sales_orders_delete',
                'name_ar' => 'حذف الطلبات المبيعات',
            ],
            [
                'value' => 'purchase_orders_view',
                'name_en' => 'purchase_orders_view',
                'name_ar' => 'عرض الطلبات الشراء',
            ],
            [
                'value' => 'purchase_orders_create',
                'name_en' => 'purchase_orders_create',
                'name_ar' => 'إنشاء الطلبات الشراء',
            ],
            [
                'value' => 'purchase_orders_update',
                'name_en' => 'purchase_orders_update',
                'name_ar' => 'تحديث الطلبات الشراء',
            ],
            [
                'value' => 'purchase_orders_delete',
                'name_en' => 'purchase_orders_delete',
                'name_ar' => 'حذف الطلبات الشراء',
            ],
            [
                'value' => 'stock_movements_view',
                'name_en' => 'stock_movements_view',
                'name_ar' => 'عرض حركات المخزون',
            ],
            [
                'value' => 'stock_levels_view',
                'name_en' => 'stock_levels_view',
                'name_ar' => 'عرض مستويات المخزون',
            ],
            [
                'value' => 'stock_transfers_view',
                'name_en' => 'stock_transfers_view',
                'name_ar' => 'عرض التحويلات المخزون',
            ],
            [
                'value' => 'stock_transfers_create',
                'name_en' => 'stock_transfers_create',
                'name_ar' => 'إنشاء التحويلات المخزون',
            ],
            [
                'value' => 'stock_transfers_update',
                'name_en' => 'stock_transfers_update',
                'name_ar' => 'تحديث التحويلات المخزون',
            ],
            [
                'value' => 'stock_transfers_delete',
                'name_en' => 'stock_transfers_delete',
                'name_ar' => 'حذف التحويلات المخزون',
            ],
            [
                'value' => 'stock_adjustments_view',
                'name_en' => 'stock_adjustments_view',
                'name_ar' => 'عرض التعديلات المخزون',
            ],
            [
                'value' => 'stock_adjustments_create',
                'name_en' => 'stock_adjustments_create',
                'name_ar' => 'إنشاء التعديلات المخزون',
            ],
            [
                'value' => 'stock_adjustments_update',
                'name_en' => 'stock_adjustments_update',
                'name_ar' => 'تحديث التعديلات المخزون',
            ],
            [
                'value' => 'stock_adjustments_delete',
                'name_en' => 'stock_adjustments_delete',
                'name_ar' => 'حذف التعديلات المخزون',
            ],
            [
                'value' => 'journals_view',
                'name_en' => 'journals_view',
                'name_ar' => 'عرض اليوميات',
            ],
            [
                'value' => 'journals_create',
                'name_en' => 'journals_create',
                'name_ar' => 'إنشاء اليوميات',
            ],
            [
                'value' => 'journals_update',
                'name_en' => 'journals_update',
                'name_ar' => 'تحديث اليوميات',
            ],
            [
                'value' => 'journals_delete',
                'name_en' => 'journals_delete',
                'name_ar' => 'حذف اليوميات',
            ],
            [
                'value' => 'dispatching_view',
                'name_en' => 'dispatching_view',
                'name_ar' => 'عرض التوزيعات',
            ],
            [
                'value' => 'invoices_view',
                'name_en' => 'invoices_view',
                'name_ar' => 'عرض الفواتير',
            ],
            [
                'value' => 'invoices_create',
                'name_en' => 'invoices_create',
                'name_ar' => 'إنشاء الفواتير',
            ],
            [
                'value' => 'invoices_update',
                'name_en' => 'invoices_update',
                'name_ar' => 'تحديث الفواتير',
            ],
            [
                'value' => 'invoices_delete',
                'name_en' => 'invoices_delete',
                'name_ar' => 'حذف الفواتير',
            ],
            [
                'value' => 'bills_view',
                'name_en' => 'bills_view',
                'name_ar' => 'عرض الفواتير',
            ],
            [
                'value' => 'bills_create',
                'name_en' => 'bills_create',
                'name_ar' => 'إنشاء الفواتير',
            ],
            [
                'value' => 'bills_update',
                'name_en' => 'bills_update',
                'name_ar' => 'تحديث الفواتير',
            ],
            [
                'value' => 'bills_delete',
                'name_en' => 'bills_delete',
                'name_ar' => 'حذف الفواتير',
            ],
            [
                'value' => 'chart_of_accounts_view',
                'name_en' => 'chart_of_accounts_view',
                'name_ar' => 'عرض الحسابات المالية',
            ],
            [
                'value' => 'chart_of_accounts_create',
                'name_en' => 'chart_of_accounts_create',
                'name_ar' => 'إنشاء الحسابات المالية',
            ],
            [
                'value' => 'chart_of_accounts_update',
                'name_en' => 'chart_of_accounts_update',
                'name_ar' => 'تحديث الحسابات المالية',
            ],
            [
                'value' => 'chart_of_accounts_delete',
                'name_en' => 'chart_of_accounts_delete',
                'name_ar' => 'حذف الحسابات المالية',
            ],
            [
                'value' => 'bank_accounts_view',
                'name_en' => 'bank_accounts_view',
                'name_ar' => 'عرض الحسابات المالية',
            ],
            [
                'value' => 'bank_accounts_create',
                'name_en' => 'bank_accounts_create',
                'name_ar' => 'إنشاء الحسابات المالية',
            ],
            [
                'value' => 'bank_accounts_update',
                'name_en' => 'bank_accounts_update',
                'name_ar' => 'تحديث الحسابات المالية',
            ],
            [
                'value' => 'bank_accounts_delete',
                'name_en' => 'bank_accounts_delete',
                'name_ar' => 'حذف الحسابات المالية',
            ],

            [
                'value' => 'contracts_view',
                'name_en' => 'contracts_view',
                'name_ar' => 'عرض العقود',
            ],
            [
                'value' => 'contracts_create',
                'name_en' => 'contracts_create',
                'name_ar' => 'إنشاء العقود',
            ],
            [
                'value' => 'contracts_update',
                'name_en' => 'contracts_update',
                'name_ar' => 'تحديث العقود',
            ],
            [
                'value' => 'contracts_delete',
                'name_en' => 'contracts_delete',
                'name_ar' => 'حذف العقود',
            ],
        ];


        Permission::insert($permissions);
        
    }
}
