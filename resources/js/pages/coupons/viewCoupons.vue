<template>
    <div v-if="loading === false">
        <div v-if="couponsView === true">
            <h1>
                Coupons Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ couponsDetails.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Title
                    </td>
                    <td>{{ couponsDetails.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Amount
                    </td>
                    <td>{{ couponsDetails.amount }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Percentage
                    </td>
                    <td>{{ couponsDetails.percentage }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Type
                    </td>
                    <td>{{ couponsDetails.type }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Start Date
                    </td>
                    <td>{{ couponsDetails.start_date }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        End Date
                    </td>
                    <td>{{ couponsDetails.end_date }}</td>
                </tr>

                </tbody>
            </table>
            <button @click="$router.push({ name: 'coupons' })" class="btn black-bg">
                {{ 'Back' }}
            </button>
            <button v-if="couponsUpdate" @click="$router.push({ path: `/updatecoupons/${couponsDetails.id}` })" class="btn linked-icon">
                {{ 'Edit' }}
            </button>
            <button v-if="couponsDelete" class="btn linked-icon" @click="deleteCoupons(couponsDetails.id)">
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
            couponsDetails: [],
            couponsPermissions: [],
            couponsUpdate: false,
            couponsDelete: false,
            couponsView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {

            const permissionsToCheck = ['couponsView', 'couponsDelete', 'couponsUpdate'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'couponsView') {
                        this.loading = false;
                    }
                }
            });

            try {
                await this.$axios
                    .get(`/api/coupons/${this.$route.params.id}`)
                    .then(response => {
                        this.couponsDetails = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    },
    methods: {
        async deleteCoupons(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Coupons?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/coupons/${id}`)
                        .then(response => {
                            if (response.status === 200) {
                                this.$toast.success( response.data.message, { position: 'top-right', duration: 3000 })
                                // Redirect home.
                                this.$router.push('/coupons')
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
