<template>
    <div v-if="loading === false">
        <div v-if="addressView === true">
            <h1 class="pt-5 mb-3">
                Address {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">ID</th>
                    <th class="col-4">{{ address.id }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">Type</td>
                    <td>{{ address.type }}</td>
                </tr>
                <tr>
                    <td class="boldText">Country</td>
                    <td>{{ address.country.country_name }}</td>
                </tr>
                <tr>
                    <td class="boldText">City</td>
                    <td>{{ address.city.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">Address 1</td>
                    <td>{{ address.street_1 }}</td>
                </tr>
                <tr>
                    <td class="boldText">Address 2</td>
                    <td>{{ address.street_2 }}</td>
                </tr>
                <tr>
                    <td class="boldText">User</td>
                    <td>{{ address.user.name }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="addressUpdate" :to="`/address/${address.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/address`" class="btn grey-bg mx-2">back</router-link>
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
            addressUpdate: false,
            addressDelete: false,
            addressView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['addressAdd', 'addressUpdate', 'addressView', 'addressDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'addressView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/address/${this.$route.params.id}`);
            this.address = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
