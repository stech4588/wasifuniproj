<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();   //here we are disabling it so that if there are any parent/child relations
        // with other tables, it should not stop us from doing what we want or prompt any errors.

        if(Schema::hasTable('permissions')){
            DB::table('permissions')->truncate();
        }
        // this function here makes sure that after running the seeder duplication of data is ignored so if there is data
        // already in the table, first it will drop/empty the table then store latest permissions

        Schema::enableForeignKeyConstraints();

        // again enabling to maintain all relations (parent/child) will all other tables to ensure the smooth functioning

        $permissions = $this->getPermissions();   // it is a php function from php.net which returns an array.

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
    /**
     * Get the permissions data from an external source (e.g., array, configuration file).
     */
    private function getPermissions()
    {
        return [
            [
                'name' => 'viewPermissionDetails',
                'description' => 'This will allow us to see details of each permission according to their ids',
                'category' => 'permission'
            ],
            [
                'name' => 'permissionsView',
                'description' => 'This will allow to view the permissions',
                'category' => 'permission'
            ],
            [
                'name' => 'permissionsAdd',
                'description' => 'This will allow to add permissions',
                'category' => 'permission'
            ],
            [
                'name' => 'permissionsUpdate',
                'description' => 'This will allow to edit permissions',
                'category' => 'permission'
            ],
            [
                'name' => 'permissionsDelete',
                'description' => 'This will allow to delete permissions',
                'category' => 'permission'
            ],
            [
                'name' => 'roleView',
                'description' => 'This will allow to view the roles',
                'category' => 'role'
            ],
            [
                'name' => 'roleAdd',
                'description' => 'This will allow to add the roles',
                'category' => 'role'
            ],
            [
                'name' => 'roleUpdate',
                'description' => 'This will allow to update the roles',
                'category' => 'role'
            ],
            [
                'name' => 'roleDelete',
                'description' => 'This will allow to delete the roles',
                'category' => 'role'
            ],
            [
                'name' => 'normalUser',
                'description' => 'This will redirect user to store',
                'category' => 'user'
            ],
            [
                'name' => 'adminUser',
                'description' => 'This will redirect user to Admin Dashboard',
                'category' => 'user'
            ],
            [
                'name' => 'metatagsView',
                'description' => 'This will allow to view the metatag',
                'category' => 'metatag'
            ],
            [
                'name' => 'metatagsAdd',
                'description' => 'This will allow to add the metatag',
                'category' => 'metatag'
            ],
            [
                'name' => 'metatagsUpdate',
                'description' => 'This will allow to update the metatag',
                'category' => 'metatag'
            ],
            [
                'name' => 'metatagsDelete',
                'description' => 'This will allow to delete the metatag',
                'category' => 'metatag'
            ],
            [
                'name' => 'viewMetatagDetails',
                'description' => 'This will allow to delete the metatag',
                'category' => 'metatag'
            ],
            [
                'name' => 'productUnitView',
                'description' => 'This will allow to view the Product unit',
                'category' => 'Product_unit'
            ],
            [
                'name' => 'productUnitAdd',
                'description' => 'This will allow to add the Product unit',
                'category' => 'Product_unit'
            ],
            [
                'name' => 'productUnitUpdate',
                'description' => 'This will allow to update the Product unit',
                'category' => 'Product_unit'
            ],
            [
                'name' => 'productUnitDelete',
                'description' => 'This will allow to delete the Product unit',
                'category' => 'Product_unit'
            ],
            [
                'name' => 'categoryView',
                'description' => 'This will allow to view the category',
                'category' => 'category'
            ],
            [
                'name' => 'categoryAdd',
                'description' => 'This will allow to add the category',
                'category' => 'category'
            ],
            [
                'name' => 'categoryUpdate',
                'description' => 'This will allow to update the category',
                'category' => 'category'
            ],
            [
                'name' => 'categoryDelete',
                'description' => 'This will allow to delete the category',
                'category' => 'category'
            ],
            [
                'name' => 'productView',
                'description' => 'This will allow to view the Product',
                'category' => 'Product'
            ],
            [
                'name' => 'productAdd',
                'description' => 'This will allow to add the Product',
                'category' => 'Product'
            ],
            [
                'name' => 'productUpdate',
                'description' => 'This will allow to update the Product',
                'category' => 'Product'
            ],
            [
                'name' => 'productDelete',
                'description' => 'This will allow to delete the Product',
                'category' => 'Product'
            ],
            [
                'name' => 'couponsView',
                'description' => 'This will allow to view the Coupons',
                'category' => 'coupons'
            ],
            [
                'name' => 'couponsAdd',
                'description' => 'This will allow to add the Coupons',
                'category' => 'coupons'
            ],
            [
                'name' => 'couponsUpdate',
                'description' => 'This will allow to update the Coupons',
                'category' => 'coupons'
            ],
            [
                'name' => 'couponsDelete',
                'description' => 'This will allow to delete the Coupons',
                'category' => 'coupons'
            ],
            [
                'name' => 'canApproved',
                'description' => 'This will allow to approve the Product',
                'category' => 'approve'
            ],
            [
                'name' => 'orderView',
                'description' => 'This will allow to view the Order',
                'category' => 'order'
            ],
            [
                'name' => 'orderAdd',
                'description' => 'This will allow to add the Order',
                'category' => 'order'
            ],
            [
                'name' => 'orderUpdate',
                'description' => 'This will allow to update the Order',
                'category' => 'order'
            ],
            [
                'name' => 'orderDelete',
                'description' => 'This will allow to delete the Order',
                'category' => 'order'
            ],
            [
                'name' => 'invoiceView',
                'description' => 'This will allow to view the Invoice',
                'category' => 'invoice'
            ],
            [
                'name' => 'invoiceAdd',
                'description' => 'This will allow to add the Invoice',
                'category' => 'invoice'
            ],
            [
                'name' => 'invoiceUpdate',
                'description' => 'This will allow to update the Invoice',
                'category' => 'invoice'
            ],
            [
                'name' => 'invoiceDelete',
                'description' => 'This will allow to delete the Invoice',
                'category' => 'invoice'
            ],
            [
                'name' => 'settingView',
                'description' => 'This will allow to view the Setting',
                'category' => 'setting'
            ],
            [
                'name' => 'settingAdd',
                'description' => 'This will allow to add the Setting',
                'category' => 'setting'
            ],
            [
                'name' => 'settingUpdate',
                'description' => 'This will allow to update the Setting',
                'category' => 'setting'
            ],
            [
                'name' => 'settingDelete',
                'description' => 'This will allow to delete the Setting',
                'category' => 'setting'
            ],
            [
                'name' => 'bannersView',
                'description' => 'This will allow to view the banners',
                'category' => 'banners'
            ],
            [
                'name' => 'bannersAdd',
                'description' => 'This will allow to add the banners',
                'category' => 'banners'
            ],
            [
                'name' => 'bannersUpdate',
                'description' => 'This will allow to update the banners',
                'category' => 'banners'
            ],
            [
                'name' => 'bannersDelete',
                'description' => 'This will allow to delete the banners',
                'category' => 'banners'
            ],
            [
                'name' => 'otherChargeView',
                'description' => 'This will allow to view the Other Charges',
                'category' => 'other_charge'
            ],
            [
                'name' => 'otherChargeAdd',
                'description' => 'This will allow to add the Other Charges',
                'category' => 'other_charge'
            ],
            [
                'name' => 'otherChargeUpdate',
                'description' => 'This will allow to update the Other Charges',
                'category' => 'other_charge'
            ],
            [
                'name' => 'otherChargeDelete',
                'description' => 'This will allow to delete the Other Charges',
                'category' => 'other_charge'
            ],
            [
                'name' => 'reviewView',
                'description' => 'This will allow to view the review',
                'category' => 'review'
            ],
            [
                'name' => 'reviewAdd',
                'description' => 'This will allow to add the review',
                'category' => 'review'
            ],
            [
                'name' => 'reviewUpdate',
                'description' => 'This will allow to update the review',
                'category' => 'review'
            ],
            [
                'name' => 'reviewDelete',
                'description' => 'This will allow to delete the review',
                'category' => 'review'
            ],
            [
                'name' => 'saleView',
                'description' => 'This will allow to view the sale',
                'category' => 'sale'
            ],
            [
                'name' => 'saleAdd',
                'description' => 'This will allow to add the sale',
                'category' => 'sale'
            ],
            [
                'name' => 'saleUpdate',
                'description' => 'This will allow to update the sale',
                'category' => 'sale'
            ],
            [
                'name' => 'saleDelete',
                'description' => 'This will allow to delete the sale',
                'category' => 'sale'
            ],
            [
                'name' => 'addressView',
                'description' => 'This will allow to view the address',
                'category' => 'address'
            ],
            [
                'name' => 'addressAdd',
                'description' => 'This will allow to add the address',
                'category' => 'address'
            ],
            [
                'name' => 'addressUpdate',
                'description' => 'This will allow to update the address',
                'category' => 'address'
            ],
            [
                'name' => 'addressDelete',
                'description' => 'This will allow to delete the address',
                'category' => 'address'
            ],
            [
                'name' => 'sizesView',
                'description' => 'This will allow to view the sizes',
                'category' => 'sizes'
            ],
            [
                'name' => 'sizesAdd',
                'description' => 'This will allow to add the sizes',
                'category' => 'sizes'
            ],
            [
                'name' => 'sizesUpdate',
                'description' => 'This will allow to update the sizes',
                'category' => 'sizes'
            ],
            [
                'name' => 'sizesDelete',
                'description' => 'This will allow to delete the sizes',
                'category' => 'sizes'
            ],
            [
                'name' => 'customersView',
                'description' => 'This will allow to view the customers',
                'category' => 'customers'
            ],
            [
                'name' => 'customersAdd',
                'description' => 'This will allow to add the customers',
                'category' => 'customers'
            ],
            [
                'name' => 'customersUpdate',
                'description' => 'This will allow to update the customers',
                'category' => 'customers'
            ],
            [
                'name' => 'customersDelete',
                'description' => 'This will allow to delete the customers',
                'category' => 'customers'
            ],
        ];
    }
}
