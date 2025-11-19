<template>
    <div v-if="loading === false">
        <div v-if="sizesView === true">
            <h1 class="pt-5 mb-3">
                Category Size  {{ $route.params.id }} Detail
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ size.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Name
                    </td>
                    <td>{{ size.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Category
                    </td>
                    <td>{{ size.category.name }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="sizesUpdate" :to="`/updateSizes/${size.id}`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/listSizes`" class="btn grey-bg mx-2">back</router-link>
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
            size: '',
            sizesView: false,
            sizesUpdate: false,
            sizesDelete: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['sizesAdd', 'sizesUpdate', 'sizesView', 'sizesDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'sizesView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/sizes/${this.$route.params.id}`);
            this.size = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },
}
</script>
