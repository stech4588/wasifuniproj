<template>
    <div v-if="loading === false">
        <div v-if="couponsUpdate === true">
            <h1>
                Update Coupons {{ $route.params.id }}
            </h1>
            <div class="row">
                <div class="col-lg-12 m-auto">
                    <form @submit.prevent="updateCoupons" @keydown="form.onKeydown($event)">
                        <!-- Title -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control"
                                       type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Amount') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.amount" :class="{ 'is-invalid': form.errors.has('amount') }" class="form-control"
                                       type="number" name="amount" required>
                                <has-error :form="form" field="amount" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Percentage') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.percentage" :class="{ 'is-invalid': form.errors.has('percentage') }" class="form-control"
                                       type="number" name="percentage" required>
                                <has-error :form="form" field="percentage" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Type') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.type" :class="{ 'is-invalid': form.errors.has('type') }" class="form-control" name="type" required>
                                    <option value="amount">Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                                <has-error :form="form" field="type" />
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
                                <!-- Update Button -->
                                <button class="btn grey-bg" :disabled="form.busy">
                                    {{ 'Update' }}
                                </button>
                                <button @click="$router.push({ name: 'coupons' })" class="btn black-bg ms-1">
                                    {{ 'Cancel' }}
                                </button>
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

    data() { return {
        form: new this.$form({
            name: '',
            amount: null,
            percentage: null,
            type: ['amount','percentage'],
            start_date: '',
            end_date: ''
        }),
        permissions: {},
        updatedSuccessful: false,
        couponsUpdate: false,
        loading: true
    }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['couponsUpdate'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'couponsUpdate') {
                            this.loading = false;
                        }
                    }
                });

                await this.$axios
                    .get(`/api/coupons/${this.$route.params.id}`)
                    .then(response => {
                        this.form.name = response.data.data.name
                        this.form.amount = response.data.data.amount
                        this.form.percentage = response.data.data.percentage
                        this.form.type = response.data.data.type
                        this.form.start_date = response.data.data.start_date
                        this.form.end_date = response.data.data.end_date
                    })

            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    },

    methods: {
        async updateCoupons() {
            try {
                const { data } = await this.form.put(`/api/coupons/${this.$route.params.id}`)
                if (data.status === 200) {
                    this.$toast.success(data.message, { position: 'top-right', duration: 3000 })
                    this.$router.push('/coupons')
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    }
}
</script>

<style scoped></style>
