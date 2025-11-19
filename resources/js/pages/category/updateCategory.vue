<template>
    <div v-if="loading === false">
        <div v-if="categoryUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Category
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateCategory" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>

                        <!-- Radio button group for selecting parent or child -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Type') }}</label>
                            <div class="col-md-7">
                                <label class="radio-inline">
                                    <input type="radio" v-model="form.type" value="parent"> Parent
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" v-model="form.type" value="child"> Child
                                </label>
                            </div>
                        </div>

                        <!-- Show image and category fields only when form.type is "child" -->

                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Image') }}</label>
                                <div class="col-md-7">
                                    <input @change="handleImageChange" class="form-control" type="file" name="image" accept="image/*" >
                                    <has-error :form="form" field="image" />
                                    <img :src="image" alt="Category Image" v-if="image" :width="150" :height="100" />
                                </div>
                            </div>
                        <div v-if="form.type === 'child'">
                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Category') }}</label>
                                <div class="col-md-7">
                                    <select v-model="form.parent_category_id" class="form-control" name="category" required>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'category' }" class="btn grey-bg ms-3">
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
                type: null,
                image: '',
                parent_category_id: null,
            }),
            image: '',
            categories: [],
            addedSuccessful: false,
            loading: true,
            categoryUpdate: false,
            filter: 'parent'
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Category',
            description: 'Category Update page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['categoryUpdate'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'categoryUpdate') {
                            this.loading = false;
                        }
                    }
                });
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    },

    async created() {
        // Fetch the existing product unit data and populate the form fields
        await this.fetchCategoryData();
        await this.fetchCategories();
    },
    methods: {
        handleImageChange(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                this.form.image = file; // Store the selected file
            } else {
                event.target.value = ''; // Clear the input
                this.form.image = null; // Reset the stored file
                this.$toast.error('Please select a valid image file.', { position: 'bottom-right', duration: 3000 });
            }
        },

        async fetchCategoryData() {
            try {
                const response = await this.$axios.get(`/api/category/${this.$route.params.id}`);
                this.form.name = response.data.data.name;
                this.form.type = response.data.data.type;
                // this.form.image = response.data.data.image_url;
                this.image = response.data.data.image_url;
                this.form.parent_category_id = response.data.data.parent_category_id;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchCategories() {
            try {
                const response = await this.$axios.get(`/api/category?filter=${this.filter}`); // Replace with your API endpoint
                this.categories = response.data.data;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async updateCategory() {
            try {
                if (this.form.type === 'parent') {
                    // Set image and parent_category_id to null if parent type is selected
                    // this.form.image = null;
                    this.form.parent_category_id = null;
                }

                await this.form.post(`/api/category/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/category');
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
