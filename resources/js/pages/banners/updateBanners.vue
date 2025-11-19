<template>
    <div v-if="loading!==true">
        <div v-if="bannersUpdate===true">
            <h1>
                Update Banners {{ $route.params.id }}
            </h1>
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <form @submit.prevent="addBanners" @keydown="form.onKeydown($event)">
                        <!-- Name -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control"
                                       type="text" name="name" required
                                >
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <!-- Content -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Tag Line') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.tag_line" :class="{ 'is-invalid': form.errors.has('tag_line') }"
                                       class="form-control" type="text" name="tag_line" required
                                >
                                <has-error :form="form" field="tag_line" />
                            </div>
                        </div>
                        <!-- Page -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Page') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.page_id" class="form-control" name="page_id" required>
                                    <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Image') }}</label>
                            <div class="col-md-7">
                                <input @change="handleImageChange" class="form-control" type="file" name="image" accept="image/*" required>
                                <has-error :form="form" field="image" />
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-7 offset-md-3 d-flex">
                                <!-- Submit Button -->
                                <button class="btn grey-bg" :disabled="form.busy">
                                    {{ 'Update' }}
                                </button>
                                <button @click="$router.push({ name: 'banners' })" class="btn black-bg ms-1">
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
    inject: ['authStore'],
    name: 'Update',

    data (){ return{
        form: new this.$form({
            name: '',
            tag_line: '',
            page_id: null,
            image: null
        }),
        pages: [],
        loading: true,
        bannersUpdate: false
    }

    },

    async mounted () {
        if (this.authStore.user === null) {
            this.loading = true
        } else {
            try {
                const permissionsToCheck = ['bannersUpdate'];
                const permissionResults = await checkPermissions( permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'bannersUpdate') {
                            this.loading = false;
                        }
                    }
                });

                await this.$axios.get(`/api/banners/${this.$route.params.id}`)
                    .then((response) => {
                        this.form.name = response.data.data.name
                        this.form.tag_line = response.data.data.tag_line
                        this.form.page_id = response.data.data.page_id
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
            try {
                const response = await this.$axios.get('/api/pages')
                if (response.status === 200){
                    this.pages = response.data.data
                }

            } catch (e) {
                handleError(e,this.$toast);
            }
        }
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
        async addBanners () {
            try {
                const { data } = await this.form.post(`/api/banners/${this.$route.params.id}`)

                if (data.status === 200) {
                    this.$toast.success( data.message,{position: 'top-right', duration: 3000})
                    this.$router.push('/banners')
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    }
}
</script>

<style scoped></style>
