<template>
    <div v-if="loading === false">
        <div v-if="bannersView === true">
            <h1>Banners</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="Searchbanners" @keydown="form.onKeydown($event)">
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
                <router-link v-if="bannersAdd" :to="{ name: 'banners.add' }" class="btn linked-icon">
                    <font-awesome-icon icon="plus" fixed-width />
                </router-link>
            </div>

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <tr>
                        <th class="">
                            ID
                        </th>
                        <th class="col-1">
                            Page Name
                        </th>
                        <th class="col-2">
                            Tag Line
                        </th>
                        <th class="col-2">
                            Image
                        </th>
                        <th class="col-8 text-center">
                            Action
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td>{{ item.page.name }}</td>
                        <td>{{ item.tag_line }}</td>
                        <td class="text-center">
                            <img :src="item.image_url ? item.image_url : '/images/no_image.jpg'" alt="Banner Image" :width="150" :height="100">
                        </td>
                        <td class="text-center">
                            <router-link :to="{ path: `/viewbanners/${item.id}` }" class="btn linked-icon">
                                <font-awesome-icon icon="eye" fixed-width />
                            </router-link>
                            <router-link v-if="bannersUpdate " :to="{ path: `/updatebanners/${item.id}` }" class="btn linked-icon">
                                <font-awesome-icon icon="pen" fixed-width />
                            </router-link>
                            <button v-if="bannersDelete " class="btn linked-icon" @click="deletebanners(item.id)">
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
    name: 'Banners',
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
            bannersId: 0,
            bannersUpdate: false,
            bannersAdd: false,
            bannersDelete: false,
            banners: false,
            loading: true,
            bannersView: false,
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
        if (this.authStore.user === null) {
            this.loading = false;
        } else {
            let ifPermissionViewExist = false;
            try {
                const permissionsToCheck = ['bannersAdd', 'bannersUpdate', 'bannersView', 'bannersDelete'];
                const permissionResults = await checkPermissions(permissionsToCheck);
                permissionsToCheck.forEach(permission => {
                    if (permissionResults[permission]) {
                        this[permission] = true;
                        if (permission === 'bannersView') {
                            ifPermissionViewExist = true;
                        }
                    }
                });
            } catch (e) {
                handleError(e,this.$toast);
            }

            // Fetch and update the role list after successful delete
            this.fetchBannersList(ifPermissionViewExist);
        }
    },

    methods: {
        async changePage (page) {
            try {
                const response = await this.$axios.get(`/api/banners?page=${page}&view=${this.view}&search=${this.form.searchInput}`)
                this.currentPage = response.data.data.current_page
                this.currentPageData = response.data.data.data
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchBannersList(ifPermissionViewExist) {

            if (ifPermissionViewExist){
                try {
                    await this.$axios
                        .get(`/api/banners?view=${this.view}`)
                        .then(response => {
                            this.currentPageData = response.data.data.data
                            this.totalPages = response.data.data.last_page

                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
                this.loading = false;
            } else{
                this.loading = false;
            }
        },

        async deletebanners(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this banners?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/banners/${id}`)
                        .then(response => {
                            if(response.data.status === 200){
                                this.$toast.success( response.data.message, { position: 'top-right', duration: 3000 })
                                // Fetch and update the role list after successful delete
                                this.fetchBannersList(true);
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },

        async Searchbanners () {
            try {
                await this.$axios
                    .get('/api/banners', {
                        params: {
                            search: this.form.searchInput,
                            view: this.view
                        }
                    })
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        }
    }
}
</script>

<style scoped></style>
