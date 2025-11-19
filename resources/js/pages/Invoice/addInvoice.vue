<template>
    <div v-if="loading === false">
        <div v-if="invoiceAdd === true">
            <h1 class="pt-5">
                Add Invoice
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addInvoice" @keydown="form.onKeydown($event)">

                        <div>
                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Order') }}</label>
                                <div class="col-md-7">
                                    <select v-model="form.order_id" class="form-control" name="unit" required @change="updatePrice">
                                        <option v-for="order in orders" :key="order.id" :value="order.id">{{ order.id }}</option>
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
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'invoice' }" class="btn grey-bg  ms-3">
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
            invoiceAdd: false,
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Invoice',
            description: 'Invoice create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {

            const permissionsToCheck = ['invoiceAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'invoiceAdd') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch units from API here
        await this.fetchCreateData();
    },

    methods: {
        updatePrice() {
            // Find the selected order based on order_id
            const selectedOrder = this.orders.find(order => order.id === this.form.order_id);

            if (selectedOrder) {
                this.form.price = selectedOrder.total_amount; // Update the price in the form
            }
        },

        async addInvoice() {
            try {

                await this.form.post('/api/invoice')
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

        async fetchCreateData() {
            try {
                const response = await this.$axios.get('/api/invoice/create');
                this.orders = response.data.data.orders;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },


    }
}
</script>

<style scoped> </style>
