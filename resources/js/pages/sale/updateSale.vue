<template>
    <div v-if="loading === false">
        <div v-if="saleUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Sale
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateSale" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Sale Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div><div class="mb-3 row">
                        <label class="col-md-3 col-form-label text-md-end">{{ ('Sale Price') }}</label>
                        <div class="col-md-7">
                            <input v-model="form.sale_price" class="form-control" type="number" name="sale_price" required>
                            <has-error :form="form" field="sale_price" />
                        </div>
                    </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ 'Products' }}</label>
                            <div class="col-md-7">
                                <!-- Product Search Input -->
                                <input
                                    v-model="productSearch"
                                    @input="searchProducts"
                                    class="form-control"
                                    type="text"
                                    placeholder="Search products..."
                                />

                                <!-- Product List with Checkboxes -->
                                <div class="product-list">
                                    <div
                                        v-for="product in filteredProducts"
                                        :key="product.id"
                                        class="product-item"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="product.id"
                                            v-model="form.selectedProductIds"
                                        />
                                        <label>{{ product.name }}</label>
                                    </div>
                                </div>

                                <has-error :form="form" field="product" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Start Date') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.start_date" :class="{ 'is-invalid': form.errors.has('start_date') }" class="form-control"
                                       type="date" name="start_date" required>
                                <has-error :form="form" field="start_date" />
                            </div>
                        </div>
                        <!-- End Date -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('End Date') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.end_date" :class="{ 'is-invalid': form.errors.has('end_date') }" class="form-control"
                                       type="date" name="end_date" required>
                                <has-error :form="form" field="end_date" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'sale' }" class="btn grey-bg ms-3">
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
                name: '',
                sale_price: '',
                start_date: '',
                end_date: '',
                selectedProductIds: [],
            }),
            products: [],
            addedSuccessful: false,
            loading: true,
            saleUpdate: false,
            productSearch: ''

        }
    },
    computed: {
        filteredProducts() {
            // Filter products based on the search input
            const searchTerm = this.productSearch.toLowerCase().trim();
            return this.products.filter((product) =>
                product.name.toLowerCase().includes(searchTerm)
            );
        },
    },

    async mounted() {
        this.$useHead({
            title: 'Sale',
            description: 'Sale Update page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['saleUpdate'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'saleUpdate') {
                            this.loading = false;
                        }
                    }
                });
            } catch (e) {
                handleError(e,this.$toast)
            }
        }

        // Fetch units from API here
        await this.fetchEditData();
    },

    methods: {

        async fetchEditData() {
            try {
                const response = await this.$axios.get(`/api/sale/${this.$route.params.id}/edit`);
                this.form.sale_price = response.data.data.sale.sale_price;
                this.form.name = response.data.data.sale.name;
                this.form.start_date = response.data.data.sale.start_date;
                this.form.end_date = response.data.data.sale.end_date;
                this.form.selectedProductIds = response.data.data.sale.product_id;
                this.products = response.data.data.products;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        searchProducts() {
            // Triggered when the user types in the search input
            // This will automatically update the filteredProducts computed property
        },

        async updateSale() {
            try {

                await this.form.put(`/api/sale/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/sale');
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
