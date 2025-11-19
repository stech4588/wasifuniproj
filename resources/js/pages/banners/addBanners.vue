<template>
    <div v-if="loading === false">
        <div v-if="bannersAdd === true">
            <h1>
                Add Banners
            </h1>
            <div class="row">
                <div class="col-lg-12 m-auto">
                    <form @submit.prevent="addInbanners" @keydown="form.onKeydown($event)">
                        <!-- Page -->
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Page') }}</label>
                            <div class="col-md-7">
                                <select v-model="form.page_id" class="form-control" name="unit" required>
                                    <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control"
                                       type="text" name="name" required
                                >
                                <has-error :form="form" field="name" />
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-3 col-form-label text-md-end">{{ ('Tag Line') }}</label>
                            <div class="col-md-7">
                                <input v-model="form.tag_line" :class="{ 'is-invalid': form.errors.has('tag_line') }" class="form-control"
                                       type="text" name="tag_line" required
                                >
                                <has-error :form="form" field="tag_line" />
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
                                <button class="btn grey-bg" :disabled="form.busy">
                                    {{ 'Add' }}
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
    name: 'Add',

    data() {
        return {
            form: new this.$form({
                image: null,
                tag_line: '',
                name: '',
                page_id: null,
            }),
            permissions: {},
            pages: [],
            addedSuccessful: false,
            loading: true,
            bannersAdd: false
        }
    },

    async mounted () {
        if (this.authStore.user === null) {
            this.loading = true
        } else {

            const permissionsToCheck = ['bannersAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'bannersAdd') {
                        this.loading = false;
                    }
                }
            });
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
        async addInbanners () {
            try {
                const { data } = await this.form.post('/api/banners')

                if (data.status === 200) {
                    this.$toast.success( data.message, { position: 'top-right', duration: 3000 })
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
