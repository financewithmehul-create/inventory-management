<?php

use Spatie\Permission\Models\Permission;
use Webkul\Security\Models\Role;
use Webkul\Security\Services\DefaultRoleSynchronizer;

require_once __DIR__.'/../../Helpers/TestBootstrapHelper.php';

/**
 * Permission names follow {action}_{module}_{resource}; the built-in roles are derived from them.
 */
beforeEach(function () {
    foreach (['sale_order', 'purchase_order', 'inventory_operation', 'inventory_warehouse', 'employee_employee', 'accounting_invoice', 'manufacturing_order', 'product_product', 'partner_partner'] as $resource) {
        foreach (['view_any', 'view', 'create', 'update', 'delete'] as $action) {
            Permission::findOrCreate("{$action}_{$resource}", 'web');
        }
    }

    Permission::findOrCreate('page_inventory_manage_warehouses', 'web');

    app(DefaultRoleSynchronizer::class)->sync();
});

function roleCan(string $role, string $permission): bool
{
    return Role::findByName($role, 'web')->hasPermissionTo($permission);
}

it('gives warehouse staff the warehouse but not people, money or settings', function () {
    expect(roleCan('Warehouse Staff', 'create_inventory_operation'))->toBeTrue()
        ->and(roleCan('Warehouse Staff', 'update_inventory_operation'))->toBeTrue()
        ->and(roleCan('Warehouse Staff', 'delete_inventory_operation'))->toBeFalse()
        ->and(roleCan('Warehouse Staff', 'page_inventory_manage_warehouses'))->toBeFalse()
        ->and(roleCan('Warehouse Staff', 'view_any_employee_employee'))->toBeFalse()
        ->and(roleCan('Warehouse Staff', 'view_any_accounting_invoice'))->toBeFalse()
        ->and(roleCan('Warehouse Staff', 'create_sale_order'))->toBeFalse();
});

it('gives the sales manager sales but not warehouse changes, people or accounting', function () {
    expect(roleCan('Sales Manager', 'create_sale_order'))->toBeTrue()
        ->and(roleCan('Sales Manager', 'delete_sale_order'))->toBeTrue()
        ->and(roleCan('Sales Manager', 'view_any_inventory_operation'))->toBeTrue()
        ->and(roleCan('Sales Manager', 'create_inventory_operation'))->toBeFalse()
        ->and(roleCan('Sales Manager', 'view_any_employee_employee'))->toBeFalse()
        ->and(roleCan('Sales Manager', 'create_manufacturing_order'))->toBeFalse();
});

it('keeps the inventory manager from deleting products', function () {
    expect(roleCan('Inventory Manager', 'delete_inventory_warehouse'))->toBeTrue()
        ->and(roleCan('Inventory Manager', 'update_product_product'))->toBeTrue()
        ->and(roleCan('Inventory Manager', 'delete_product_product'))->toBeFalse()
        ->and(roleCan('Inventory Manager', 'view_any_employee_employee'))->toBeFalse();
});

it('lets the viewer look but never change anything', function () {
    foreach (['sale_order', 'inventory_operation', 'employee_employee', 'product_product'] as $resource) {
        expect(roleCan('Viewer', "view_any_{$resource}"))->toBeTrue()
            ->and(roleCan('Viewer', "create_{$resource}"))->toBeFalse()
            ->and(roleCan('Viewer', "update_{$resource}"))->toBeFalse()
            ->and(roleCan('Viewer', "delete_{$resource}"))->toBeFalse();
    }
});

it('keeps the HR manager away from sales and the warehouse', function () {
    expect(roleCan('HR Manager', 'create_employee_employee'))->toBeTrue()
        ->and(roleCan('HR Manager', 'view_any_sale_order'))->toBeFalse()
        ->and(roleCan('HR Manager', 'view_any_inventory_operation'))->toBeFalse();
});
