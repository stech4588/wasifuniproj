import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store/auth';
function isAuthenticated() {
    return useAuthStore().isAuthenticated
}

const router = createRouter({
    history: createWebHistory(),
    routes: [
        //Ecommerce
        { path: '/', name: 'landingPage', component: ()=> import('../pages/LandingPage.vue'),meta: { layout: 'basic' }},
        { path: '/product-detail/:id', name: 'productdetail', component:  ()=> import('../pages/ecommerce/ProductDetail.vue'),meta: { layout: 'basic' }},
        { path: '/collection/:id', name: 'collection', component:  ()=> import('../pages/ecommerce/CategoryDetail.vue'),meta: { layout: 'basic' }},
        { path: '/cart', name: 'cart', component:  ()=> import('../pages/ecommerce/Cart.vue'),meta: { layout: 'basic',requiresAuth: true  }},
        { path: '/userDashboard', name: 'userDashboard', component:  ()=> import('../pages/ecommerce/UserDashboard.vue'),meta: { layout: 'basic',requiresAuth: true }},
        { path: '/orderDetail/:id', name: 'orderDetail', component:  ()=> import('../pages/ecommerce/OrderDetail.vue'),meta: { layout: 'basic',requiresAuth: true }},
        { path: '/userAddresses', name: 'userAddresses', component:  ()=> import('../pages/ecommerce/UserAddresses.vue'),meta: { layout: 'basic',requiresAuth: true }},
        { path: '/review/create', name: 'review.add', component: ()=> import('../pages/ecommerce/addReview.vue'),meta: { layout: 'basic',requiresAuth: true }},

        { path: '/login', name: 'login', component:  ()=> import('../pages/auth/Login.vue'),meta: { layout: 'basic' }},
        { path: '/register', name: 'register', component:  ()=> import('../pages/auth/Register.vue'),meta: { layout: 'basic' }},
        { path: '/dashboard', name: 'dashboard', component:  ()=> import('../pages/dashboard.vue'),meta: { requiresAuth: true }},
        { path: '/admin', redirect: { name: 'dashboard' }},
        // Permissions Routes
        { path: '/permission', name: 'permission', component:  ()=> import('../pages/permission/permission.vue'),meta: { requiresAuth: true } },
        { path: '/addpermission', name: 'permission.add', component:  ()=> import('../pages/permission/addpermission.vue'),meta: { requiresAuth: true } },
        { path: '/updatepermission/:id', name: 'permission.update', component:  ()=> import('../pages/permission/updatepermission.vue'),meta: { requiresAuth: true } },
        { path: '/viewpermission/:id', name: 'permission.view', component:  ()=> import('../pages/permission/viewpermission.vue'),meta: { requiresAuth: true } },
        // Role Routes
        { path: '/role', name: 'role', component:  ()=> import('../pages/role/role.vue'),meta: { requiresAuth: true } },
        { path: '/addrole', name: 'role.add', component:  ()=> import('../pages/role/addrole.vue'),meta: { requiresAuth: true } },
        { path: '/updaterole/:id', name: 'role.update', component:  ()=> import('../pages/role/updaterole.vue'),meta: { requiresAuth: true } },
        { path: '/viewrole/:id', name: 'role.view', component:  ()=> import('../pages/role/viewrole.vue'),meta: { requiresAuth: true } },
        // Coupons Routes
        { path: '/coupons', name: 'coupons', component:  ()=> import('../pages/coupons/listCoupons.vue'),meta: { requiresAuth: true } },
        { path: '/addcoupons', name: 'coupons.add', component:  ()=> import('../pages/coupons/addCoupons.vue'),meta: { requiresAuth: true } },
        { path: '/updatecoupons/:id', name: 'coupons.update', component:  ()=> import('../pages/coupons/updateCoupons.vue'),meta: { requiresAuth: true } },
        { path: '/viewcoupons/:id', name: 'coupons.view', component:  ()=> import('../pages/coupons/viewCoupons.vue'),meta: { requiresAuth: true } },
        // Customers Routes
        { path: '/customers', name: 'customers', component:  ()=> import('../pages/customers/listCustomers.vue'),meta: { requiresAuth: true } },
        { path: '/addcustomers', name: 'customers.add', component:  ()=> import('../pages/customers/addCustomers.vue'),meta: { requiresAuth: true } },
        { path: '/updatecustomers/:id', name: 'customers.update', component:  ()=> import('../pages/customers/updateCustomers.vue'),meta: { requiresAuth: true } },
        { path: '/viewcustomers/:id', name: 'customers.view', component:  ()=> import('../pages/customers/viewCustomers.vue'),meta: { requiresAuth: true } },
        // Banners Routes
        { path: '/banners', name: 'banners', component:  ()=> import('../pages/banners/listBanners.vue'),meta: { requiresAuth: true } },
        { path: '/addbanners', name: 'banners.add', component:  ()=> import('../pages/banners/addBanners.vue'),meta: { requiresAuth: true } },
        { path: '/updatebanners/:id', name: 'banners.update', component:  ()=> import('../pages/banners/updateBanners.vue'),meta: { requiresAuth: true } },
        { path: '/viewbanners/:id', name: 'banners.view', component:  ()=> import('../pages/banners/viewBanners.vue'),meta: { requiresAuth: true } },
        // Meta tags Routes
        { path: '/metatags', name: 'metatags', component:  ()=> import('../pages/metatags/metatags.vue'),meta: { requiresAuth: true } },
        { path: '/addMetatags', name: 'metatags.add', component:  ()=> import('../pages/metatags/addmetatags.vue'),meta: { requiresAuth: true } },
        { path: '/updateMetatags/:id', name: 'metatags.update', component:  ()=> import('../pages/metatags/updatemetatags.vue'),meta: { requiresAuth: true } },
        { path: '/viewMetatags/:id', name: 'metatags.view', component:  ()=> import('../pages/metatags/viewmetatags.vue'),meta: { requiresAuth: true } },
        // Sizes Routes
        { path: '/listSizes', name: 'sizes', component:  ()=> import('../pages/sizes/listSizes.vue'),meta: { requiresAuth: true } },
        { path: '/addSizes', name: 'sizes.add', component:  ()=> import('../pages/sizes/addSizes.vue'),meta: { requiresAuth: true } },
        { path: '/updateSizes/:id', name: 'sizes.update', component:  ()=> import('../pages/sizes/updateSizes.vue'),meta: { requiresAuth: true } },
        { path: '/viewSizes/:id', name: 'sizes.view', component:  ()=> import('../pages/sizes/viewSizes.vue'),meta: { requiresAuth: true } },

        { path: '/productUnit', name: 'productUnit', component:  ()=> import('../pages/productUnit/listProductUnit.vue'),meta: { requiresAuth: true }},
        { path: '/productUnit/:id', name: 'productUnit.view', component:  ()=> import('../pages/productUnit/viewProductUnit.vue'),meta: { requiresAuth: true }},
        { path: '/productUnit/create', name: 'productUnit.add', component:  ()=> import('../pages/productUnit/addProductUnit.vue'),meta: { requiresAuth: true }},
        { path: '/productUnit/:id/edit', name: 'productUnit.edit', component:  ()=> import('../pages/productUnit/updateProductUnit.vue'),meta: { requiresAuth: true }},

        { path: '/category', name: 'category', component:  ()=> import('../pages/category/listCategory.vue'),meta: { requiresAuth: true }},
        { path: '/category/:id', name: 'category.view', component:  ()=> import('../pages/category/viewCategory.vue'),meta: { requiresAuth: true }},
        { path: '/category/create', name: 'category.add', component:  ()=> import('../pages/category/addCategory.vue'),meta: { requiresAuth: true }},
        { path: '/category/:id/edit', name: 'category.edit', component:  ()=> import('../pages/category/updateCategory.vue'),meta: { requiresAuth: true }},

        { path: '/product', name: 'product', component:  ()=> import('../pages/product/listProduct.vue'),meta: { requiresAuth: true }},
        { path: '/product-image', name: 'productImage', component:  ()=> import('../pages/product/productImages.vue'),meta: { requiresAuth: true }},
        { path: '/product/:id', name: 'product.view', component:  ()=> import('../pages/product/viewProduct.vue'),meta: { requiresAuth: true }},
        { path: '/product/create', name: 'product.add', component:  ()=> import('../pages/product/addProduct.vue'),meta: { requiresAuth: true }},
        { path: '/product/:id/edit', name: 'product.edit', component:  ()=> import('../pages/product/updateProduct.vue'),meta: { requiresAuth: true }},

        { path: '/invoice', name: 'invoice', component:  ()=> import('../pages/invoice/listInvoice.vue'),meta: { requiresAuth: true }},
        { path: '/invoice/:id', name: 'invoice.view', component:  ()=> import('../pages/invoice/viewInvoice.vue'),meta: { requiresAuth: true }},
        { path: '/invoice/create', name: 'invoice.add', component:  ()=> import('../pages/invoice/addInvoice.vue'),meta: { requiresAuth: true }},
        { path: '/invoice/:id/edit', name: 'invoice.edit', component:  ()=> import('../pages/invoice/updateInvoice.vue'),meta: { requiresAuth: true }},

        { path: '/order', name: 'order', component:  ()=> import('../pages/order/listOrder.vue'),meta: { requiresAuth: true }},
        { path: '/order/:id', name: 'order.view', component:  ()=> import('../pages/order/viewOrder.vue'),meta: { requiresAuth: true }},

        { path: '/review', name: 'review', component:  ()=> import('../pages/review/listReview.vue'),meta: { requiresAuth: true }},

        { path: '/setting', name: 'setting', component:  ()=> import('../pages/setting/listSetting.vue'),meta: { requiresAuth: true }},
        { path: '/setting/:id', name: 'setting.view', component:  ()=> import('../pages/setting/viewSetting.vue'),meta: { requiresAuth: true }},
        { path: '/setting/create', name: 'setting.add', component:  ()=> import('../pages/setting/addSetting.vue'),meta: { requiresAuth: true }},
        { path: '/setting/:id/edit', name: 'setting.edit', component:  ()=> import('../pages/setting/updateSetting.vue'),meta: { requiresAuth: true }},

        { path: '/othercharge', name: 'othercharge', component:  ()=> import('../pages/otherCharge/listOtherCharge.vue'),meta: { requiresAuth: true }},
        { path: '/othercharge/:id', name: 'othercharge.view', component:  ()=> import('../pages/otherCharge/viewOtherCharge.vue'),meta: { requiresAuth: true }},
        { path: '/othercharge/create', name: 'othercharge.add', component:  ()=> import('../pages/otherCharge/addOtherCharge.vue'),meta: { requiresAuth: true }},
        { path: '/othercharge/:id/edit', name: 'othercharge.edit', component:  ()=> import('../pages/otherCharge/updateOtherCharge.vue'),meta: { requiresAuth: true }},

        { path: '/address', name: 'address', component:  ()=> import('../pages/address/listAddress.vue'),meta: { requiresAuth: true }},
        { path: '/address/:id', name: 'address.view', component:  ()=> import('../pages/address/viewAddress.vue'),meta: { requiresAuth: true }},
        { path: '/address/create', name: 'address.add', component:  ()=> import('../pages/address/addAddress.vue'),meta: { requiresAuth: true }},
        { path: '/address/:id/edit', name: 'address.edit', component:  ()=> import('../pages/address/updateAddress.vue'),meta: { requiresAuth: true }},

        { path: '/sale', name: 'sale', component:  ()=> import('../pages/sale/listSale.vue'),meta: { requiresAuth: true }},
        { path: '/sale/:id', name: 'sale.view', component:  ()=> import('../pages/sale/viewSale.vue'),meta: { requiresAuth: true }},
        { path: '/sale/create', name: 'sale.add', component:  ()=> import('../pages/sale/addSale.vue'),meta: { requiresAuth: true }},
        { path: '/sale/:id/edit', name: 'sale.edit', component:  ()=> import('../pages/sale/updateSale.vue'),meta: { requiresAuth: true }},
        {
            path: '/settings',
            component: ()=> import('../pages/settings/index.vue'),
            children: [
                { path: '', redirect: { name: 'settings.profile' } },
                { path: 'profile', name: 'settings.profile', component: ()=> import('../pages/settings/profile.vue') },
                { path: 'password', name: 'settings.password', component: ()=> import('../pages/settings/password.vue') }
            ]
            ,meta: { requiresAuth: true }
        },
        {
            path: '/:catchAll(.*)',
            redirect: (to) => {
                if (to.meta.requiresAuth && !isAuthenticated()) {
                    return { name: 'login' };
                }
            },
        },
    ],

});
router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !isAuthenticated()) {
        next({ name: 'login' });
    } else {
        next();
    }
});

export default router;
