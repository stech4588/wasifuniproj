<template>
    <div v-if="loading === false">
        <div v-if="otherChargeUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Other Charges
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateOtherCharge" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Amount') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.amount" class="form-control" type="number" name="amount" required>
                                <has-error :form="form" field="amount" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'othercharge' }" class="btn grey-bg ms-3">
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
    name: 'Update',
    inject: ['authStore'],
    data() {
        return {
            form: new this.$form({
                name: ''
            }),
            otherChargeView: false,
            otherChargeAdd: false,
            otherChargeUpdate: false,
            otherChargeDelete: false,
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
                const permissionsToCheck = ['otherChargeAdd', 'otherChargeUpdate', 'otherChargeView', 'otherChargeDelete'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'otherChargeUpdate') {
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
        await this.fetchOtherChargeData();
    },
    methods: {
        async fetchOtherChargeData() {
            try {
                const response = await this.$axios.get(`/api/othercharge/${this.$route.params.id}`);
                this.form.name = response.data.data.name;
                this.form.amount = response.data.data.amount;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async updateOtherCharge() {
            try {
                await this.form.put(`/api/othercharge/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/othercharge');
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
