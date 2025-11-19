<template>
    <div class="dashboard" v-if="adminUser === true">
        <div class="dashboard-main-charts">
            <SalesChart/>
            <OrdersChart/>
        </div>
        <div class="dashboard-second-section">
            <div class="sales-overview second-section-1">
                <h6>SALES OVERVIEW</h6>
                <select v-model="selectedDateRange" @change="fetchSalesOverview">
                    <option value="all">All Time</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_year">This Year</option>
                    <option value="today">Today</option>
                </select>
                <br/>
                <br/>
                <div class="sales-overview-listing overflow-auto">
                    <div class="sales-overview-list">
                        <div class="sales-overview-name">Metric</div>
                        <div class="sales-overview-value text-uppercase">{{selectedDateRange}}</div>
                    </div>
                    <hr class="grey-bg"/>
                    <div v-for="(value, key) in salesOverview">
                        <div class="sales-overview-list">
                            <div class="sales-overview-name">{{key}}</div>
                            <div class="sales-overview-value">{{value}}</div>
                        </div>
                        <hr class="grey-bg"/>
                    </div>
                </div>
            </div>

            <div class="customers-overview second-section-2">
                <div class="new-customers">
                    <div>NEW CUSTOMERS</div>
                    <select v-model="selectedDateRangeCustomers" @change="fetchCustomers">
                        <option value="all">All Time</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="today">Today</option>
                    </select>
                    <br/>
                    <br/>
                    {{newCustomers}}
                </div>
                <div class="total-customers">
                    <div>TOTAL CUSTOMERS</div>
                    <br/>
                    <br/>
                    {{totalCustomers}}
                </div>
            </div>

            <div class="shopping-cart-overview second-section-3" >
                <h6>SHOPPING CART OVERVIEW</h6>
                <CartChart/>
            </div>

            <div class="top-products-by-sale second-section-4">
                <h6>TOP PRODUCTS BY SALES</h6>
                <div v-for="item in totalProducts">
                    <div>{{item.name}}</div>
                    <span>{{item.total_quantity_sold}}</span>
                </div>
            </div>
        </div>
    </div>
    <div v-else-if="adminUser === false">
        <Unauthorized />
    </div>
    <div v-else>
        <Loader />
    </div>
</template>

<script>

export default {
    inject: ['authStore'],
    name: "Dashboard",
    data () {
        return {
            selectedDateRange: 'all',
            selectedDateRangeCustomers: 'all',
            salesOverview: {},
            totalCustomers: 0,
            newCustomers: 0,
            totalProducts: [],
            adminUser: null
        }
    },
    async mounted() {

        try {
            const response = await this.$axios.get(`/api/page-metatags?pageName=login`)
            if (response.status === 200){
                const metaTags = response.data.data
                const metaTagElements = metaTags.map((tag) => {
                    return {
                        ...tag,
                        content: tag.content || '', // Provide a default value for content
                    };
                });

                this.$useHead({
                    title: "login",
                    meta: metaTagElements,
                });
            }
            await this.fetchSalesOverview();
            await this.fetchCustomers();
            await this.fetchProducts();
        } catch (e) {
            handleError(e,this.$toast)
        }
        try {
            if (this.authStore.user === null) {
                this.adminUser = false
            } else {
                const permissionsToCheck = ['adminUser'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        if (permission === 'adminUser') {
                            this.adminUser = true;
                        }
                    }else{
                        this.adminUser = false
                    }
                });
            }
        }
        catch (e) {
                handleError(e, this.$toast)
            }

    },
    methods: {
        async fetchSalesOverview() {
            try {
                const response = await this.$axios.get('/api/salesOverview', {
                    params: {
                        selectedDateRange: this.selectedDateRange,
                    },
                });

                this.salesOverview = response.data.data

            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        async fetchCustomers() {
            try {
                const response = await this.$axios.get('/api/customersOverview', {
                    params: {
                        selectedDateRange: this.selectedDateRangeCustomers,
                    },
                });

                this.totalCustomers = response.data.data.totalCustomers
                this.newCustomers = response.data.data.newCustomers

            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        async fetchProducts() {
            try {
                const response = await this.$axios.get('/api/productsOverview', {
                    params: {
                        selectedDateRange: this.selectedDateRangeCustomers,
                    },
                });

                this.totalProducts = response.data.data.productName

            } catch (e) {
                handleError(e, this.$toast);
            }
        }
    }
}
</script>

<style scoped>

</style>
