<template>
    <div v-if="loading === false">
        <div v-if="settingAdd === true">
            <h1 class="pt-5">
                Add Setting
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addSetting" @keydown="form.onKeydown($event)">
                        <!-- Module Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Module Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.module_name" class="form-control" type="text" name="module_name" required>
                                <has-error :form="form" field="module_name" />
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" class="form-control" type="text" name="name" required>
                                <has-error :form="form" field="name" />
                            </div>
                        </div>

                        <!-- Name -->
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
                                <v-button :loading="form.busy" class="btn text-dark search-grey-bg">
                                    {{ ('Save') }}
                                </v-button>

                                <router-link :to="{ name: 'setting' }" class="btn grey-bg  ms-3">
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
            addedSuccessful: false,
            loading: true,
            productUnitAdd: false
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Setting',
            description: 'Setting create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            const permissionsToCheck = ['settingAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'settingAdd') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    methods: {
        async addSetting() {
            try {
                await this.form.post('/api/setting')
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
