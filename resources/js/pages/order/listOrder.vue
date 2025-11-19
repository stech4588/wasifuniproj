<template>
    <div v-if="loading === false">
        <div v-if="orderView === true">
            <h1>Order</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="SearchProduct" @keydown="form.onKeydown($event)">
                            <div class="input-group mb-3">
                                <input v-model="form.searchInput" :class="{ 'is-invalid': form.errors.has('searchInput') }"
                                       placeholder="Search Records By TITLE..." class="form-control" type="text" name="searchInput"
                                >
                                <has-error :form="form" field="searchInput" />
                                <div class="input-group-append">
                                    <button class="btn grey-bg ms-2" :disabled="form.busy">
                                        {{ 'Search' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <th class="col">#</th>
                    <th class="col-1">Order No</th>
                    <th class="col-2">Address</th>
                    <th class="col-2">Coupen No</th>
                    <th class="col-2">Other Charges</th>
                    <th class="col-2">Status</th>
                    <th class="col-2">Total Amount</th>
                    <th class="col-3 text-center">Actions</th>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td>{{ item.order_no ?? item.id }}</td>
                        <td>{{ item.address_id !== null ? item.address_id : 'Null' }}</td>
                        <td>{{ item.coupon_id !== null ? item.coupon_id : 'Null' }}</td>
                        <td>{{ resolveOtherCharge(item) }}</td>
                        <td>{{ item.status }}</td>
                        <td>{{ item.total_amount }}</td>
                        <td class="text-center">
                            <div>
                                <!-- Show "Approve" button for inactive products -->
                                <button v-if="item.status !== 'confirmed' && canApproved" class="btn grey-bg" @click="confirmedOrder(item.id)">
                                    Confirm
                                </button>

                                <!-- Show view, edit, and delete buttons for active products -->
                                <router-link v-else :to="{ path: `/order/${item.id}` }" class="btn linked-icon">
                                    <font-awesome-icon icon="eye" fixed-width />
                                </router-link>
                                <router-link v-if="orderUpdate && item.is_active" :to="{ path: `/product/${item.id}/edit` }" class="btn linked-icon">
                                    <font-awesome-icon icon="pen" fixed-width />
                                </router-link>
                                <button v-if="orderDelete && item.is_active" class="btn linked-icon" @click="deleteProduct(item.id)">
                                    <font-awesome-icon icon="trash" fixed-width />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div>
                    <pagination
                :currentPage="currentPage"
                :totalPages="totalPages"
                :visiblePaginationLinks="visiblePaginationLinks"
                :changePage="changePage"
            ></pagination>

                </div>
            </div>
            <div v-else class="d-flex justify-content-center align-items-center">
                <h3>NO RECORD FOUND</h3>
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
    name: 'Order',
    scrollToTop: false,
    inject: ['authStore'],

    data () {
        return {
            form: new this.$form({
                searchInput: ''
            }),
            currentPage: 1,
            totalPages: 1,
            currentPageData: [],
            roleId: 0,
            orderView: false,
            orderAdd: false,
            orderUpdate: false,
            orderDelete: false,
            canApproved: false,
            role: false,
            loading: true,
            view: 5
        }
    },

    computed: {
        visiblePaginationLinks () {
            // Limit the number of visible pagination links to a reasonable number (e.g., 5)
            const maxVisibleLinks = 5
            const halfVisibleLinks = Math.floor(maxVisibleLinks / 2)
            let startPage = Math.max(1, this.currentPage - halfVisibleLinks)
            let endPage = Math.min(this.totalPages, startPage + maxVisibleLinks - 1)

            if (this.totalPages >= maxVisibleLinks && endPage === this.totalPages) {
                startPage = Math.max(1, endPage - maxVisibleLinks + 1)
            }

            const links = []
            for (let i = startPage; i <= endPage; i++) {
                links.push(i)
            }
            return links
        },

    },

    async mounted() {
        scrollToTop();
        this.$useHead({
            title: 'Order',
            description: 'Order page'
        });
        if (this.authStore.user === null) {
            this.loading = true;
        } else {
            ;
            const permissionsToCheck = ['orderAdd', 'orderUpdate', 'orderView', 'orderDelete', 'canApproved'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'orderView') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch the product list using the extracted method
        await this.fetchOrderList();
        try {
            await this.$axios
                .get(`/api/order?view=${this.view}`)
                .then(response => {
                    this.currentPageData = response.data.data.data
                    this.totalPages = response.data.data.last_page
                })
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {
        async changePage (page) {
            const response = await this.$axios.get(`api/order?page=${page}&view=${this.view}`)
            this.currentPage = response.data.data.current_page
            this.currentPageData = response.data.data.data
        },

        async fetchOrderList() {
            try {
                await this.$axios
                    .get(`/api/order?view=${this.view}`)
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async confirmedOrder(id) {
            try {
                const status = 'confirmed';
                await this.$axios
                    .put(`/api/order/${id}/${status}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success('Order mark as confirmed successfully.', { position: 'bottom-right', duration: 3000 });
                            // Fetch and update the product list after successful approval
                            this.fetchOrderList();
                        } else {
                            handleError(e,this.$toast);
                        }
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async deleteProduct(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Order?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/order/${id}`)
                        .then(response => {
                            if (response.data.status === 200) {
                                this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                                // Fetch and update the product list after successful delete
                                this.fetchOrderList();
                            } else {
                                handleError(e,this.$toast);
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },

        async SearchProduct () {
            await this.$axios
                .get('/api/order', {
                    params: {
                        search: this.form.searchInput,
                        view: this.view
                    }
                })
                .then(response => {
                    this.currentPageData = response.data.data.data
                    this.totalPages = response.data.data.last_page
                })
                .catch(e => {
                    handleError(e,this.$toast);
                })
        },

        resolveOtherCharge(item) {
            const value = item.other_charge_id ?? item.other_charger_id;
            return value !== null && value !== undefined ? value : 'Null';
        },




    }
}
</script>

<style scoped></style>
