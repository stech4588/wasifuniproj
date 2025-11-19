<template>
    <div v-if="loading === false">
        <div v-if="otherChargeView === true">
            <h1 class="pt-5 mb-3">
                Other Charges Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ othercharge.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Name
                    </td>
                    <td>{{ othercharge.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Amount
                    </td>
                    <td>{{ othercharge.amount }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="otherChargeUpdate" :to="`/othercharge/${othercharge.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/othercharge`" class="btn grey-bg mx-2">back</router-link>
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
    name: 'View',
    inject: ['authStore'],
    data() {
        return {
            roleDetails: [],
            rolePermissions: [],
            otherChargeView: false,
            otherChargeUpdate: false,
            otherChargeDelete: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['otherChargeAdd', 'otherChargeUpdate', 'otherChargeView', 'otherChargeDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'otherChargeView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/othercharge/${this.$route.params.id}`);
            this.othercharge = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
