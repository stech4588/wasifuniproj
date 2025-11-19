<template>
    <div v-if="loading === false">
        <div v-if="invoiceView === true">
            <h1>Invoice</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="SearchInvoice" @keydown="form.onKeydown($event)">
                            <div class="input-group mb-3">
                                <input v-model="form.searchInput" :class="{ 'is-invalid': form.errors.has('searchInput') }"
                                       placeholder="Search Records By TITLE..." class="form-control" type="text" name="searchInput"
                                >
                                <has-error :form="form" field="searchInput" />
                                <div class="input-group-append">
                                    <button class="btn grey-bg ms-2" :disabled="form.busy">
                                        {{ 'Search' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="text-end my-3">
                <router-link v-if="invoiceAdd" :to="{ name: 'invoice.add' }" class="btn linked-icon">
                    <font-awesome-icon icon="plus" fixed-width />
                </router-link>
            </div>

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <th class="col">#</th>
                    <th class="col-2">Order No</th>
                    <th class="col-2">Invoice No</th>
                    <th class="col-2">Status</th>
                    <th class="col-2">Price</th>
                    <th class="col-3 text-center">Actions</th>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td>{{ item.order ? (item.order.order_no || item.order.id) : 'N/A' }}</td>
                        <td>{{ item.invoice_no }}</td>
                        <td>{{ item.status }}</td>
                        <td>{{ item.price }}</td>
                        <td class="text-center">
                            <router-link :to="{ path: `/invoice/${item.id}` }" class="btn linked-icon">
                                <font-awesome-icon icon="eye" fixed-width />
                            </router-link>
                            <router-link v-if="invoiceUpdate " :to="{ path: `/invoice/${item.id}/edit` }" class="btn linked-icon">
                                <font-awesome-icon icon="pen" fixed-width />
                            </router-link>
                            <button v-if="invoiceDelete " class="btn linked-icon" @click="deleteInvoice(item.id)">
                                <font-awesome-icon icon="trash" fixed-width />
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div>
                    <pagination
                :currentPage="currentPage"
                :totalPages="totalPages"
                :visiblePaginationLinks="visiblePaginationLinks"
                :changePage="changePage"
            ></pagination>

                </div>
            </div>
            <div v-else class="d-flex justify-content-center align-items-center">
                <h3>NO RECORD FOUND</h3>
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
    name: 'Invoice',
    scrollToTop: false,
    inject: ['authStore'],

    data () {
        return {
            form: new this.$form({
                searchInput: ''
            }),
            currentPage: 1,
            totalPages: 1,
            currentPageData: [],
            roleId: 0,
            invoiceView: false,
            invoiceAdd: false,
            invoiceUpdate: false,
            invoiceDelete: false,
            role: false,
            loading: true,
            view: 5
        }
    },

    computed: {
        visiblePaginationLinks () {
            // Limit the number of visible pagination links to a reasonable number (e.g., 5)
            const maxVisibleLinks = 5
            const halfVisibleLinks = Math.floor(maxVisibleLinks / 2)
            let startPage = Math.max(1, this.currentPage - halfVisibleLinks)
            let endPage = Math.min(this.totalPages, startPage + maxVisibleLinks - 1)

            if (this.totalPages >= maxVisibleLinks && endPage === this.totalPages) {
                startPage = Math.max(1, endPage - maxVisibleLinks + 1)
            }

            const links = []
            for (let i = startPage; i <= endPage; i++) {
                links.push(i)
            }
            return links
        },

    },

    async mounted() {
        this.$useHead({
            title: 'Invoice',
            description: 'Invoice page'
        });
        if (this.authStore.user === null) {
            this.loading = true;
        } else {

            const permissionsToCheck = ['invoiceAdd', 'invoiceUpdate', 'invoiceView', 'invoiceDelete'];
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

        // Fetch the product list using the extracted method
        await this.fetchSettingList();
    },

    methods: {
        async changePage (page) {
            const response = await this.$axios.get(`api/invoice?page=${page}&view=${this.view}&search=${this.form.searchInput}`)
            this.currentPage = response.data.data.current_page
            this.currentPageData = response.data.data.data
        },

        async fetchSettingList() {
            try {
                await this.$axios
                    .get(`/api/invoice?view=${this.view}`)
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        // async approveProduct(id) {
        //     try {
        //         await this.$axios
        //             .put(`/api/invoice/${id}/active`)
        //             .then(response => {
        //                 if (response.data.status === 200) {
        //                     this.$toast.success(response.data.message.message, { position: 'bottom-right', duration: 3000 });
        //                     window.location.reload()
        //                 } else {
        //                     handleError(e,this.$toast);
        //                 }
        //             })
        //     } catch (e) {
        //         handleError(e,this.$toast);
        //     }
        // },

        async deleteInvoice(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Invoice?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/invoice/${id}`)
                        .then(response => {
                            if (response.data.status === 200) {
                                this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                                // Fetch and update the product Unit list after successful delete
                                this.fetchSettingList();
                            } else {
                                handleError(e,this.$toast);
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },

        async SearchInvoice () {
            await this.$axios
                .get('/api/invoice', {
                    params: {
                        search: this.form.searchInput,
                        view: this.view
                    }
                })
                .then(response => {
                    this.currentPageData = response.data.data.data
                    this.totalPages = response.data.data.last_page
                })
                .catch(e => {
                    handleError(e,this.$toast);
                })
        },




    }
}
</script>

<style scoped></style>
