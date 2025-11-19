<template>
    <div v-if="loading === false">
        <div v-if="reviewAdd === true">
            <h1 class="pt-5">
                Add Review
            </h1>
            <div class="order-detail-products">
                <div class="order-history-content" v-if="product">
                    <div class="history-content " >
                        <div class="cart-image " >
                            <img :src="product.image_url" :alt="product.product_name" />
                        </div>
                        <div>
                            Name: {{product.product_name}}
                        </div>
                        <div>
                            Price: {{product.price}}
                        </div>
                        <div>
                            Quantity: {{product.quantity}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addReview" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Stars') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.stars" class="form-control" type="number" name="stars" min="1" max="5" required>
                                <has-error :form="form" field="stars" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Description') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.description" class="form-control" type="text" name="description" required>
                                <has-error :form="form" field="description" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="`/orderDetail/${product.order_id}`" class="btn grey-bg ms-3">
                                    {{ 'Cancel' }}
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
                stars: '',
                description: '',
                product_id: ''
            }),
            addedSuccessful: false,
            loading: true,
            reviewAdd: false,
            product: {}
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Review',
            description: 'Review create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            await this.getPermission();
        }
    },

    created() {
        this.getProductDataFromLocalStorage();
    },

    methods: {
        async getPermission(){
            const permissionsToCheck = ['reviewAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'reviewAdd') {
                        this.loading = false;
                    }
                }
            });
        },
        async addReview() {
            try {
                this.form.product_id = this.product.product_id;

                await this.form.post('/api/review')
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push(`/orderDetail/${this.product.order_id}`);
                        } else {
                            handleError(e,this.$toast);
                        }
                    });
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        getProductDataFromLocalStorage() {
            const productData = JSON.parse(localStorage.getItem("productDetails"));
            if (productData) {
                // If cart data exists in local storage, assign it to the cart property
                this.product = productData;
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
