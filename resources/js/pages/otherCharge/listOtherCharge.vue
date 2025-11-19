<template>
    <div v-if="loading === false">
        <div v-if="otherChargeView === true">
            <h1>Other Charges</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="SearchOtherCharges" @keydown="form.onKeydown($event)">
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
                <router-link v-if="otherChargeAdd" :to="{ name: 'othercharge.add' }" class="btn linked-icon">
                    <font-awesome-icon icon="plus" fixed-width />
                </router-link>
            </div>

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <tr>
                        <th class="1">
                            ID
                        </th>
                        <th class="col-2">
                            Name
                        </th>
                        <th class="col-2">
                            Amount
                        </th>
                        <th class="col-7 text-center">
                            Action
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td>{{ item.name }}</td>
                        <td>{{ item.amount }}</td>
                        <td class="text-center">
                            <router-link :to="{ path: `/othercharge/${item.id}` }" class="btn linked-icon">
                                <font-awesome-icon icon="eye" fixed-width />
                            </router-link>
                            <router-link v-if="otherChargeUpdate " :to="{ path: `/othercharge/${item.id}/edit` }" class="btn linked-icon">
                                <font-awesome-icon icon="pen" fixed-width />
                            </router-link>
                            <button v-if="otherChargeDelete " class="btn linked-icon" @click="deleteOtherCharge(item.id)">
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
    name: 'OtherCharge',
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
            otherChargeView: false,
            otherChargeAdd: false,
            otherChargeUpdate: false,
            otherChargeDelete: false,
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
        scrollToTop();
        this.$useHead({
            title: 'Other Charges',
            description: 'Other Charges page'
        });
        if (this.authStore.user === null) {
            this.loading = true;
        } else {
            const permissionsToCheck = ['otherChargeAdd', 'otherChargeUpdate', 'otherChargeView', 'otherChargeDelete'];
            const permissionResults = await checkPermissions( permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'otherChargeView') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch the product list using the extracted method
        await this.fetchOtherChargeList();
    },

    methods: {
        async changePage (page) {
            const response = await this.$axios.get(`api/othercharge?page=${page}&view=${this.view}&search=${this.form.searchInput}`)
            this.currentPage = response.data.data.current_page
            this.currentPageData = response.data.data.data
        },

        async fetchOtherChargeList() {
            try {
                await this.$axios
                    .get(`/api/othercharge?view=${this.view}`)
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async deleteOtherCharge(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Other Charge?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/othercharge/${id}`)
                        .then(response => {
                            if (response.data.status === 200) {
                                this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                                // Fetch and update the product Unit list after successful delete
                                this.fetchOtherChargeList();
                            } else {
                                handleError(e,this.$toast);
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },

        async SearchOtherCharges () {
            await this.$axios
                .get('/api/othercharge', {
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
