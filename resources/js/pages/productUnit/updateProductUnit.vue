<template>
    <div v-if="loading === false">
        <div v-if="productUnitUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Product Unit
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateProductUnit" @keydown="form.onKeydown($event)">
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
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'productUnit' }" class="btn grey-bg ms-3">
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
            productUnitView: false,
            productUnitAdd: false,
            productUnitUpdate: false,
            productUnitDelete: false,
            loading: true
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Product Units',
            description: 'Product Units page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['productUnitAdd', 'productUnitUpdate', 'productUnitView', 'productUnitDelete'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'productUnitUpdate') {
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
        await this.fetchProductUnitData();
    },
    methods: {
        async fetchProductUnitData() {
            try {
                const response = await this.$axios.get(`/api/productunit/${this.$route.params.id}`);
                this.form.name = response.data.data.name;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async updateProductUnit() {
            try {
                 await this.form.put(`/api/productunit/${this.$route.params.id}`)
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

    }
}
</script>

<style scoped> </style>
