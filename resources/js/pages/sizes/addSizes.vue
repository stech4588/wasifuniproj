<template>
    <div v-if="loading === false">
        <div v-if="sizesAdd === true">
            <h1 class="pt-5">
                Add Category Sizes
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addSizes" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Category') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.category_id" class="form-control" name="unit" required>
                                    <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'sizes' }" class="btn grey-bg  ms-3">
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
            addedSuccessful: false,
            loading: true,
            sizesAdd: false,
            categories: []
        }
    },
    async mounted() {
        this.$useHead({
            title: 'sizes',
            description: 'sizes create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            const permissionsToCheck = ['sizesAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'sizesAdd') {
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
        }
    },

    methods: {
        async addSizes() {
            try {
                await this.form.post('/api/sizes')
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
