<template>
    <div v-if="loading === false">
        <div v-if="addressUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Address
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateAddress" @keydown="form.onKeydown($event)">
                        <!-- Radio button group for selecting billing or shipping -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Type') }}</label>
                            <div class="col-md-7">
                                <label class="radio-inline">
                                    <input type="radio" v-model="form.type" value="billing"> Billing
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" v-model="form.type" value="shipping"> Shipping
                                </label>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Country') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.country_id" class="form-control" name="country" required @change="fetchCities">
                                    <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.country_name }}</option>
                                </select>
                                <has-error :form="form" field="country_id" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('City') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.city_id" class="form-control" name="city" required>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                                </select>
                                <has-error :form="form" field="city_id" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Address 1') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.street_1" class="form-control" type="text" name="street_1">
                                <has-error :form="form" field="street_1" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Address 2') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.street_2" class="form-control" type="text" name="street_2">
                                <has-error :form="form" field="street_2" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'address' }" class="btn grey-bg ms-3">
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
                type: '',
                country_id: null,
                city_id: null,
                street_1: '',
                street_2: '',
            }),
            cities: [],
            countries: [],
            addedSuccessful: false,
            loading: true,
            addressUpdate: false
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Address',
            description: 'Address Update page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['addressUpdate'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'addressUpdate') {
                            this.loading = false;
                        }
                    }
                });
            } catch (error) {
                handleError(e,this.$toast)
            }
        }
    },

    async created() {

        // Fetch units from API here
        await this.fetchEditData();
        // If a country is already selected, fetch cities for that country
        if (this.form.country_id) {
            await this.fetchCities();
        }
    },
    methods: {

        async fetchCities() {
            try {
                if (this.form.country_id) {
                    const response = await this.$axios.get(`/api/cities/${this.form.country_id}`);
                    this.cities = response.data.data.cities;
                    // this.form.city_id = null;
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchEditData() {
            try {
                const response = await this.$axios.get(`/api/address/${this.$route.params.id}/edit`); // Replace with your API endpoint
                this.form.type = response.data.data.address.type;
                this.form.country_id = response.data.data.address.country.id;
                this.form.city_id = response.data.data.address.city.id;
                this.form.street_1 = response.data.data.address.street_1;
                this.form.street_2 = response.data.data.address.street_2;
                this.countries = response.data.data.countries;

            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async updateAddress() {
            try {
                await this.form.put(`/api/address/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/address');
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
