<template>
    <div v-if="loading === false">
        <div v-if="settingUpdate === true">
            <h1 class="pt-5 mb-3">
                Edit Setting
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="updateSetting" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Module Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.module_name" class="form-control" type="text" name="module_name" required>
                                <has-error :form="form" field="module_name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Value') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.value" class="form-control" type="text" name="value" required>
                                <has-error :form="form" field="value" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <VButton :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Update') }}
                                </VButton>
                                <router-link :to="{name:'setting' }" class="btn grey-bg ms-3">
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
                module_name: '',
                name: '',
                value: ''
            }),
            settingView: false,
            settingAdd: false,
            settingUpdate: false,
            settingDelete: false,
            loading: true
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Setting',
            description: 'Setting page'
        });

        if (this.authStore.user === null) {
            this.loading = false
        } else {
            try {
                const permissionsToCheck = ['settingAdd', 'settingUpdate', 'settingView', 'settingDelete'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'settingUpdate') {
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
                const response = await this.$axios.get(`/api/setting/${this.$route.params.id}`);
                this.form.module_name = response.data.data.module_name;
                this.form.name = response.data.data.name;
                this.form.value = response.data.data.value;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async updateSetting() {
            try {
                await this.form.put(`/api/setting/${this.$route.params.id}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/setting');
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
