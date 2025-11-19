<template>
    <div v-if="loading === false">
        <div v-if="saleAdd === true">
            <h1 class="pt-5">
                Add Sale
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addSale" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Sale Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Sale Price') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.sale_price" class="form-control" type="number" name="sale_price" required>
                                <has-error :form="form" field="sale_price" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <div @click="selectProducts = !selectProducts" class="btn text-dark search-grey-bg">
                                    {{ ('Select Products') }}
                                </div>

                                <div @click="selectCategory = !selectCategory"  class="btn text-dark search-grey-bg">
                                    {{ ('Select Category') }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row" v-if="selectProducts">
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
                        </div >
                        <div class="mb-3 row" v-if="selectCategory">
                            <label class="col-md-3 col-form-label text-md-end">{{ 'Category' }}</label>
                            <div class="col-md-7">
                                <!-- Product Search Input -->
                                <input
                                    v-model="categorySearch"
                                    @input="searchCategory"
                                    class="form-control"
                                    type="text"
                                    placeholder="Search Category..."
                                />

                                <!-- Product List with Checkboxes -->
                                <div class="product-list">
                                    <div
                                        v-for="category in filteredCategory"
                                        :key="category.id"
                                        class="product-item"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="category.id"
                                            v-model="form.selectedcategoryIds"
                                        />
                                        <label>{{ category.name }}</label>
                                    </div>
                                </div>

                                <has-error :form="form" field="product" />
                            </div>
                        </div>


<!--                        <div class="mb-3 row">-->
<!--                            <label class="col-md-3 col-form-label text-md-end">{{ ('Products') }}</label>-->
<!--                            <div class="col-md-7">-->
<!--                                <select class="form-control" name="products" required multiple>-->
<!--                                    <option v-for="product in products" :key="product.id" :value="product.id" @click="toggleProductSelection(product.id)">-->
<!--                                        {{ product.name }}-->
<!--                                        &lt;!&ndash; Check if the product is selected, and if so, display a tick mark &ndash;&gt;-->
<!--                                        <span v-if="form.selectedProductIds.includes(product.id)" class="selected-product-tick">&#10003;</span>-->
<!--                                    </option>-->
<!--                                </select>-->
<!--                                <has-error :form="form" field="product" />-->
<!--                            </div>-->
<!--                        </div>-->
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
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'sale' }" class="btn grey-bg  ms-3">
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
                selectedcategoryIds: [],
            }),
            addedSuccessful: false,
            loading: true,
            saleAdd: false,
            products: [],
            category: [],
            productSearch: '',
            categorySearch: '',
            selectProducts: false,
            selectCategory: false,
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Sale',
            description: 'Sale create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            const permissionsToCheck = ['saleAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'saleAdd') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch units from API here
        await this.fetchCreateData();
    },
    computed: {
        filteredProducts() {
            // Filter products based on the search input
            const searchTerm = this.productSearch.toLowerCase().trim();
            return this.products.filter((product) =>
                product.name.toLowerCase().includes(searchTerm)
            );
        },filteredCategory() {
            // Filter products based on the search input
            const searchTerm = this.categorySearch.toLowerCase().trim();
            return this.category.filter((product) =>
                product.name.toLowerCase().includes(searchTerm)
            );
        },
    },
    methods: {

        toggleProductSelection(productId) {
            const index = this.form.selectedProductIds.indexOf(productId);
            if (index !== -1) {
                this.form.selectedProductIds.splice(index, 1); // Unselect the product
            } else {
                this.form.selectedProductIds.push(productId); // Select the product
            }
        },
        async searchProducts() {
            // try {
            //     const response = await this.$axios.get(`/api/product?page=${this.currentPage}&view=${this.view}`);
            //     this.currentPageData = response.data.data.data;
            // } catch (e) {
            //     handleError(e,this.$toast);
            // }
        },

        async addSale() {
            try {

                await this.form.post('/api/sale')
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/sale');
                        }
                    });
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchCreateData() {
            try {
                const response = await this.$axios.get(`/api/sale/create`); // Replace with your API endpoint
                this.products = response.data.data.products;
                this.category = response.data.data.categories;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        handleError(e) {
            if (e.response && e.response.data.status === 403) {
                this.$toast.error(e.response.data.message, { position: 'bottom-right', duration: 3000 });
            } else if (e.response && e.response.status === 422) {
                this.$toast.error(e.response.data.message, { position: 'bottom-right', duration: 3000 });
            } else if (e.response && e.response.status === 500){
                this.$toast.error(e.response.data.message, { position: 'bottom-right', duration: 3000 });
            }
        },
    }
}
</script>

<style scoped> </style>
