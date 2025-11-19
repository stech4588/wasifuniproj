<template>
    <div v-if="loading === false">
        <div v-if="productAdd === true">
            <h1 class="pt-5">
                Add Product
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addProduct" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Unit') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.unit_id" class="form-control" name="unit" required>
                                    <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Image') }}</label>
                            <div class="col-md-7">
                                <input @change="handleImageChange" class="form-control" type="file" name="image[]" accept="image/*" multiple required>
                                <has-error :form="form" field="image" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Selected Images') }}</label>
                            <div class="col-md-7">
                                <ul>
                                    <li v-for="(image, index) in selectedImages" :key="index">
                                        {{ image.name }}
                                        <font-awesome-icon @click="removeImage(index)" icon="trash" fixed-width class="clickable-icon"/>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Description') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.description" class="form-control" type="text" name="description">
                                <has-error :form="form" field="description" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Category') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.category_id" class="form-control" name="category" required @change="fetchSizes">
                                    <option :value="null" disabled>{{ ('Select Category') }}</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div  class="mb-3 row  " >
                            <div class="my-2"  v-for="(item, index) in form.product_variant" :key="index">
                                <h3>Item {{ index+1 }}</h3>
                                <div class="row">
                                    <div class="col-sm-2">

                                    </div>
                                    <div class="col-sm-2">
                                        <label>Size</label>
                                        <select
                                            v-model="form.product_variant[index].size_id"
                                            class="form-control"
                                            name="size"
                                            required
                                            :disabled="sizes.length === 0"
                                        >
                                            <option :value="null" disabled>{{ ('Select Size') }}</option>
                                            <option v-for="size in sizes" :key="size.id" :value="size.id">{{ size.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <label>Color</label>
                                        <input type="text" class="form-control" v-model="form.product_variant[index].color" placeholder="Color">
                                    </div>
                                    <div class="col-sm-2">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" v-model="form.product_variant[index].quantity" placeholder="Quantity">
                                    </div>
                                    <div class="col-sm-2">
                                        <label>Price</label>
                                        <input type="number" class="form-control" v-model="form.product_variant[index].price" placeholder="Price">
                                    </div>
                                    <div class="col-sm-2 pt-4">
                                        <button type="button" class="btn btn-danger btn-sm" @click="removeRow(index)">x</button>&nbsp;
                                        <button type="button" class="btn btn-success btn-sm" @click="addRow">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'product' }" class="btn grey-bg  ms-3">
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
                unit_id: null,
                image: [],
                description: '',
                category_id: null,
                product_variant: [
                    {
                        size_id: null, color:'', quantity:0, price:0
                    }
                ]
            }),
            selectedImages: [],
            units: [],
            categories: [],
            sizes: [],
            addedSuccessful: false,
            loading: true,
            productAdd: false,
            filter: 'child'
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Product',
            description: 'Product create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {

            const permissionsToCheck = ['productAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'productAdd') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch units from API here
        await this.fetchCreateData();
    },

    methods: {

        addRow() {
            this.form.product_variant.push({ size_id:null, color:'', quantity:0, price:0})
        },

        removeRow(index) {
            if( this.form.product_variant.length > 1){
                this.form.product_variant.splice(index,1)
            }
        },

        removeImage(index) {
            // Remove the image from the selectedImages array
            this.selectedImages.splice(index, 1);

            // Remove the image from the form.image array
            this.form.image.splice(index, 1);
        },

        handleImageChange(event) {
            const files = event.target.files;
            if (files.length > 0) {
                // Append the selected files to the existing array
                this.form.image = [...this.form.image, ...Array.from(files)];

                // Update the selectedImages array with the selected files
                for (let i = 0; i < files.length; i++) {
                    this.selectedImages.push(files[i]);
                }
            } else {
                event.target.value = ''; // Clear the input
                this.$toast.error('Please select valid image files.', { position: 'bottom-right', duration: 3000 });
            }
        },

        async addProduct() {
            try {
                this.form.product_variant = JSON.stringify(this.form.product_variant);

                await this.form.post('/api/product')
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/product');
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
                const [categoryResponse, unitResponse] = await Promise.all([
                    this.$axios.get('/api/category', { params: { filter: 'child' } }),
                    this.$axios.get('/api/productunit')
                ]);

                this.categories = categoryResponse.data.data ?? [];
                this.units = unitResponse.data.data ?? [];

                if (this.form.category_id) {
                    await this.fetchSizes();
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchSizes() {
            try {
                if (!this.form.category_id) {
                    this.sizes = [];
                    this.form.product_variant.forEach(variant => {
                        variant.size_id = null;
                    });
                    return;
                }

                const response = await this.$axios.get('/api/sizes', {
                    params: { category_id: this.form.category_id }
                });

                const payload = response?.data?.data ?? [];
                const resolvedSizes = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload?.data)
                        ? payload.data
                        : [];

                this.sizes = resolvedSizes;
                this.form.product_variant.forEach(variant => {
                    const hasSelectedSize = this.sizes.some(size => size.id === variant.size_id);
                    if (!hasSelectedSize) {
                        variant.size_id = null;
                    }
                });
            } catch (e) {
                this.sizes = [];
                this.form.product_variant.forEach(variant => {
                    variant.size_id = null;
                });
                handleError(e,this.$toast);
            }
        }

    }
}
</script>

<style scoped>

.clickable-icon {
    cursor: pointer;
}

.clickable-icon:hover {
    color: red; /* Optional: Change the color on hover */
}

</style>
