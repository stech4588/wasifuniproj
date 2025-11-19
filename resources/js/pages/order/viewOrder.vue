<template>
    <div v-if="!loading">
        <div v-if="orderView">
            <h1 class="pt-5 mb-3">Order #{{ order?.id }}</h1>
            <div class="row mb-4">
                <div class="col-lg-8">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <td class="boldText">Status</td>
                            <td>{{ order?.status || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Invoice Status</td>
                            <td>{{ order?.invoice_status || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Payment Method</td>
                            <td>{{ order?.payment_method || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Payment Status</td>
                            <td>{{ order?.payment_status || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Payment Reference</td>
                            <td>{{ order?.payment_reference || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Payment Amount</td>
                            <td>{{ order?.payment_amount || 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Subtotal</td>
                            <td>{{ order?.sub_total }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Discount</td>
                            <td>{{ order?.discount || 0 }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Total Amount</td>
                            <td>{{ order?.total_amount }}</td>
                        </tr>
                        <tr>
                            <td class="boldText">Customer</td>
                            <td>{{ order?.user ? order.user.name : 'N/A' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-4">
                    <div class="card p-3">
                        <h5>Metadata</h5>
                        <p class="mb-1"><strong>Created:</strong> {{ formatDate(order?.created_at) }}</p>
                        <p class="mb-1"><strong>Updated:</strong> {{ formatDate(order?.updated_at) }}</p>
                        <p class="mb-0"><strong>Coupon:</strong> {{ order?.coupon_id || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <h2 class="h4">Items</h2>
            <div v-if="orderItems.length">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in orderItems" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.product_name || 'N/A' }}</td>
                        <td>{{ item.product_variant_id || 'N/A' }}</td>
                        <td>{{ item.price }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ (item.price || 0) * (item.quantity || 0) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="alert alert-info">No items found for this order.</div>

            <div class="mt-3">
                <router-link :to="{ name: 'order' }" class="btn grey-bg">Back to Orders</router-link>
            </div>
        </div>
        <div v-else>
            <Unauthorized />
        </div>
    </div>
    <div v-else>
        <Loader />
    </div>
</template>

<script>
export default {
    inject: ['authStore'],
    data() {
        return {
            order: null,
            orderItems: [],
            orderView: false,
            orderUpdate: false,
            orderDelete: false,
            canApproved: false,
            loading: true,
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Order Detail',
            description: 'View order detail'
        });
        if (this.authStore.user === null) {
            this.loading = false;
            return;
        }

        const permissionsToCheck = ['orderAdd', 'orderUpdate', 'orderView', 'orderDelete', 'canApproved'];
        const permissionResults = await checkPermissions(permissionsToCheck);
        permissionsToCheck.forEach(permission => {
            if (permissionResults[permission]) {
                this[permission] = true;
            }
        });

        if (!this.orderView) {
            this.loading = false;
            return;
        }

        await this.fetchOrder();
    },
    methods: {
        async fetchOrder() {
            try {
                const response = await this.$axios.get(`/api/order/${this.$route.params.id}`);
                this.order = response.data.data;
                this.orderItems = Array.isArray(this.order?.order_items) ? this.order.order_items : [];
            } catch (e) {
                handleError(e, this.$toast);
            } finally {
                this.loading = false;
            }
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleString();
        },
    },
}
</script>

<style scoped>
.boldText {
    font-weight: 600;
}
</style>
