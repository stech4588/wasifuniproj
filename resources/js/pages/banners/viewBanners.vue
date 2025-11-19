<template>
    <div v-if="loading === false">
        <div v-if="bannersView === true">
            <h1>
                Banners Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ bannersDetails.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Title
                    </td>
                    <td>{{ bannersDetails.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Tag Line
                    </td>
                    <td>{{ bannersDetails.tag_line }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Image
                    </td>
                    <td>
                        <img :src="bannersDetails.image_url ? bannersDetails.image_url : '/images/no_image.jpg'" alt="Banner Image" :width="150" :height="100">
                    </td>
                </tr>


                </tbody>
            </table>
            <button @click="$router.push({ name: 'banners' })" class="btn black-bg">
                {{ 'Back' }}
            </button>
            <button v-if="bannersUpdate" @click="$router.push({ path: `/updatebanners/${bannersDetails.id}` })" class="btn linked-icon">
                {{ 'Edit' }}
            </button>
            <button v-if="bannersDelete" class="btn linked-icon" @click="deletebanners(bannersDetails.id)">
                Delete
            </button>
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
    name: 'View',
    inject: ['authStore'],

    data() {
        return {
            bannersDetails: [],
            bannersPermissions: [],
            bannersUpdate: false,
            bannersDelete: false,
            bannersView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {


            const permissionsToCheck = ['bannersView', 'bannersDelete', 'bannersUpdate'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'bannersView') {
                        this.loading = false;
                    }
                }
            });

            try {
                await this.$axios
                    .get(`/api/banners/${this.$route.params.id}`)
                    .then(response => {
                        this.bannersDetails = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    },
    methods: {
        async deletebanners(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this banners?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/banners/${id}`)
                        .then(response => {
                            if (response.status === 200) {
                                this.$toast.success( response.data.message, { position: 'top-right', duration: 3000 })
                                // Redirect home.
                                this.$router.push('/banners')
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }

        }
    }
}
</script>

<style scoped></style>
