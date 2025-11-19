<template>
    <div class="main-layout">
        <Navbar />
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <div class="col-auto px-0 ">
                    <div id="sidebar"  class="border-end">
                        <div id="sidebar-nav" v-if="authStore.isAuthenticated" class="list-group border-0 rounded-0 text-sm-start min-vh-100">
                            <router-link :to="{ name: 'dashboard' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                                <h5 class="sidebar-title" :class="{ 'active': $route.name === 'dashboard' }">
                                    <font-awesome-icon icon="home" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Home</span>
                                </h5>
                            </router-link>
                          <router-link v-if="roleView" :to="{ name: 'role' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'role' }">
                                  <font-awesome-icon icon="user" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Role</span>
                              </h5>
                          </router-link>
                          <router-link v-if="productUnitView" :to="{ name: 'productUnit' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'productUnit' }">
                                  <font-awesome-icon icon="cubes" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Product Unit</span>
                              </h5>
                          </router-link>
                          <router-link v-if="categoryView" :to="{ name: 'category' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'category' }">
                                  <font-awesome-icon icon="list" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Category</span>
                              </h5>
                          </router-link>
                            <router-link v-if="sizesView" :to="{ name: 'sizes' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'sizes' }">
                                  <font-awesome-icon icon="tags" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Category Sizes</span>
                              </h5>
                          </router-link>
                          
                          <router-link v-if="productView" :to="{ name: 'product' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'product' }">
                                  <font-awesome-icon icon="shopping-cart" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Product</span>
                              </h5>
                          </router-link>
                          <router-link v-if="productView" :to="{ name: 'productImage' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'productImage' }">
                                  <font-awesome-icon icon="image" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Product Images</span>
                              </h5>
                          </router-link>
                          <router-link v-if="reviewView" :to="{ name: 'review' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'review' }">
                                  <font-awesome-icon icon="ranking-star" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Product Reviews</span>
                              </h5>
                          </router-link>
                          <router-link v-if="orderView" :to="{ name: 'order' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'order' }">
                                  <font-awesome-icon icon="shopping-basket" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Order</span>
                              </h5>
                          </router-link>
                            <router-link v-if="customersView" :to="{ name: 'customers' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'customers' }">
                                  <font-awesome-icon icon="users" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Customers</span>
                              </h5>
                          </router-link>
                          <router-link v-if="invoiceView" :to="{ name: 'invoice' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'invoice' }">
                                  <font-awesome-icon icon="file-invoice" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Invoice</span>
                              </h5>
                          </router-link>
                          <router-link v-if="couponsView" :to="{ name: 'coupons' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'coupons' }">
                                  <font-awesome-icon icon="ticket-alt" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Coupons</span>
                              </h5>
                          </router-link>
                          <router-link v-if="metatagsView" :to="{ name: 'metatags' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'metatags' }">
                                  <font-awesome-icon icon="tags" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Meta Tags</span>
                              </h5>
                          </router-link>
                          <router-link v-if="bannersView" :to="{ name: 'banners' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'banners' }">
                                  <font-awesome-icon icon="flag" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Banners</span>
                              </h5>
                          </router-link>
                          <router-link v-if="otherChargeView" :to="{ name: 'othercharge' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'othercharge' }">
                                  <font-awesome-icon icon="money-bill-wave" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Other Charges</span>
                              </h5>
                          </router-link>
                          <router-link v-if="addressView" :to="{ name: 'address' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'address' }">
                                  <font-awesome-icon icon="map-marker-alt" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Address</span>
                              </h5>
                          </router-link>
                          <router-link v-if="saleView" :to="{ name: 'sale' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'sale' }">
                                  <font-awesome-icon icon="money-check" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Sale</span>
                              </h5>
                          </router-link>
                          <router-link v-if="settingView" :to="{ name: 'setting' }" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar">
                              <h5 class="sidebar-title" :class="{ 'active': $route.name === 'setting' }">
                                  <font-awesome-icon icon="cogs" fixed-width />
                                    <span class="sidebar-label" :class="{ 'hidden': isSidebarCollapsed }">Setting</span>
                              </h5>
                          </router-link>
                        </div>
                    </div>
                </div>
                <main class="col ps-md-2 pt-2 content " >
                    <a href="#" @click="toggleSidebar"   class="border rounded-3 p-1 text-decoration-none"><font-awesome-icon icon="bars" fixed-width /></a>
                    <child />
                </main>
            </div>
        </div>
<!--        <div class=" mt-4 container" style="overflow: auto">-->
<!--            <child />-->
<!--        </div>-->
    </div>
</template>
<script>

export default {
    inject: ['authStore'],
    name: 'MainLayout',
    data(){
        return {
            roleView:false,
            productUnitView: false,
            categoryView: false,
            productView: false,
            productImagesView: false,// permission not made
            reviewView: false,
            orderView: false,
            invoiceView: false,
            customersView: false,
            couponsView: false,
            metatagsView: false,
            bannersView: false,
            otherChargeView: false,
            addressView: false,
            saleView: false,
            settingView: false,
            sizesView: false,
            isSidebarCollapsed: false,
        }
    },
    async mounted() {
        if (this.authStore.user !== null) {

            const permissionsToCheck = [
                'roleView',
                'productUnitView',
                'categoryView',
                'productView',
                'reviewView',
                'orderView',
                'invoiceView',
                'customersView',
                'couponsView',
                'metatagsView',
                'bannersView',
                'otherChargeView',
                'addressView',
                'saleView',
                'settingView',
                'sizesView',
            ];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;

                }
            });
        }
    },
    methods: {
        toggleSidebar() {
            this.isSidebarCollapsed = !this.isSidebarCollapsed;
        },
    },

}
</script>

<style scoped>
.hidden {
    display: none;
}
</style>
