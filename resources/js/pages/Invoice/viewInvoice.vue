<template>
    <div v-if="loading === false">
        <div v-if="invoiceView === true">
            <h1 class="pt-5 mb-3">
                Product Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">ID</th>
                    <th class="col-4">{{ invoice.id }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">Order</td>
                    <td>{{ invoice.order ? (invoice.order.order_no || invoice.order.id) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="boldText">Price</td>
                    <td>{{ invoice.price }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">

                <button v-if="invoice.status === 'draft' && canApproved" class="btn linked-icon" @click="paidInvoice(invoice.id)">
                    Paid
                </button>

                <router-link v-if="invoiceUpdate" :to="`/invoice/${invoice.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/invoice`" class="btn grey-bg mx-2">back</router-link>
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
            invoiceUpdate: false,
            invoiceDelete: false,
            invoiceView: false,
            canApproved: false,
            loading: true,
            invoice: null,
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['invoiceAdd', 'invoiceUpdate', 'invoiceView', 'invoiceDelete', 'canApproved'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'invoiceView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/invoice/${this.$route.params.id}`);
            this.invoice = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {


        async paidInvoice(id) {
            try {
                const status = 'paid';
                await this.$axios
                    .put(`/api/invoice/${id}/${status}`)
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success('Invoice mark as paid successfully.', { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/invoice');
                        } else {
                            handleError(e,this.$toast);
                        }
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
    }
}
</script>
