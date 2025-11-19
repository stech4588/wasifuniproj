<template>
    <div v-if="loading === false">
        <div v-if="invoiceUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Invoice
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateInvoice" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div>
                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Order') }}</label>
                                <div class="col-md-7">
                                    <select v-model="form.order_id" class="form-control" name="unit" required @change="updatePrice">
                                        <option v-for="order in orders" :key="order.id" :value="order.id">{{ order.order_no }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Price') }}</label>
                                <div class="col-md-7">
                                    <input v-model="form.price" class="form-control" type="number" name="price" required>
                                    <has-error :form="form" field="price" />
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'invoice' }" class="btn grey-bg ms-3">
                                    {{ ('Cancel') }}
                                </router-link>
                            </div>
                        </div>
                    </form>
                </div>
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
            form: new this.$form({
                order_id: null,
                price: 0,
            }),
            orders: [],
            addedSuccessful: false,
            loading: true,
            invoiceUpdate: false
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Invoice',
            description: 'Invoice Update page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['invoiceUpdate'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'invoiceUpdate') {
                            this.loading = false;
                        }
                    }
                });
            } catch (error) {
                handleError(e,this.$toast)
            }
        }
    },

    async created() {
        // Fetch the existing product unit data and populate the form fields
        await this.fetchInvoiceData();
        await this.fetchOrders();
    },
    methods: {

        async fetchInvoiceData() {
            try {
                const response = await this.$axios.get(`/api/invoice/${this.$route.params.id}`);
                this.form.order_id = response.data.data.order_id;
                this.form.price = response.data.data.price;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchOrders() {
            try {
                const response = await this.$axios.get('/api/order');
                this.orders = response.data.data;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async updateInvoice() {
            try {
                await this.form.put(`/api/invoice/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/invoice');
                        } else {
                            handleError(e,this.$toast);
                        }
                    });
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

    }
}
</script>

<style scoped> </style>
