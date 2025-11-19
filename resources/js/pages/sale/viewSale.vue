<template>
    <div v-if="loading === false">
        <div v-if="saleView === true">
            <h1 class="pt-5 mb-3">
                Sale {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">ID</th>
                    <th class="col-4">{{ sale.id }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">Sale Price</td>
                    <td>{{ sale.sale_price }}</td>
                </tr>
                <tr>
                    <td class="boldText">Products</td>
                    <td>{{ sale.product_names }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="saleUpdate" :to="`/sale/${sale.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/sale`" class="btn grey-bg mx-2">back</router-link>
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
            saleUpdate: false,
            saleDelete: false,
            saleView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['saleAdd', 'saleUpdate', 'saleView', 'saleDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'saleView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/sale/${this.$route.params.id}`);
            this.sale = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
