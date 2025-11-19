<template>
    <div v-if="loading === false">
        <div v-if="productUnitView === true">
            <h1 class="pt-5 mb-3">
                Product Unit Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ productunit.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Name
                    </td>
                    <td>{{ productunit.name }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="productUnitUpdate" :to="`/productunit/${productunit.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/productunit`" class="btn grey-bg mx-2">back</router-link>
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
            roleDetails: [],
            rolePermissions: [],
            productUnitView: false,
            productUnitUpdate: false,
            productUnitDelete: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['productUnitAdd', 'productUnitUpdate', 'productUnitView', 'productUnitDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'productUnitView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/productunit/${this.$route.params.id}`);
            this.productunit = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
