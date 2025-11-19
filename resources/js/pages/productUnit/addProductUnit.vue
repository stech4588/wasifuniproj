<template>
    <div v-if="loading === false">
        <div v-if="productUnitAdd === true">
            <h1 class="pt-5">
                Add Product Unit
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addProductUnit" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'productUnit' }" class="btn grey-bg  ms-3">
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
                name: ''
            }),
            addedSuccessful: false,
            loading: true,
            productUnitAdd: false
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Product units',
            description: 'Product units create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            const permissionsToCheck = ['productUnitAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'productUnitAdd') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    methods: {
        async addProductUnit() {
            try {
                await this.form.post('/api/productunit')
                    .then(response => {
                    if (response.data.status === 200) {
                        this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                        // Redirect to the landing page
                        this.$router.push('/productunit');
                    } else {
                        handleError(e,this.$toast);
                    }
                });
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
