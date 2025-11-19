<template>
    <div v-if="loading === false">
        <div v-if="sizesUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Category Size
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateSizes" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                            <div class="mb-3 row">
                                <label class="col-md-3 col-form-label text-md-end">{{ ('Category') }}</label>
                                <div class="col-md-7">
                                    <select v-model="form.category_id" class="form-control" name="unit" required>
                                        <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
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
                                <router-link :to="{name:'sizes' }" class="btn grey-bg ms-3">
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
                category_id: '',
            }),
            sizesView: false,
            sizesAdd: false,
            sizesUpdate: false,
            sizesDelete: false,
            loading: true,
            categories: []
        }
    },
    async mounted() {
        this.$useHead({
            title: 'sizes',
            description: 'sizespage'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['sizesAdd', 'sizesUpdate', 'sizesView', 'sizesDelete'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'sizesUpdate') {
                            this.loading = false;
                        }
                    }
                });
                try {
                    const response = await this.$axios.get('/api/category')
                    if (response.status === 200){
                        this.categories = response.data.data
                    }

                } catch (e) {
                    handleError(e,this.$toast);
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    },

    async created() {
        // Fetch the existing product unit data and populate the form fields
        await this.fetchProductUnitData();
    },
    methods: {
        async fetchProductUnitData() {
            try {
                const response = await this.$axios.get(`/api/sizes/${this.$route.params.id}`);
                this.form.name = response.data.data.name;
                this.form.category_id = response.data.data.category.id;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async updateSizes() {
            try {
                await this.form.put(`/api/sizes/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/listSizes');
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
