<template>
    <div v-if="loading === false">
        <div v-if="productView === true">
            <h1>Product Images</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="SearchProductImage" @keydown="form.onKeydown($event)">
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

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <th class="col-1">#</th>
                    <th class="col-3">Image</th>
                    <th class="col-1">Value</th>
                    <th class="col-3">Product</th>
                    <th class="col-4 text-center">Actions</th>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td class="text-center">
                            <img :src="item.image_url ? item.image_url : '/images/no_image.jpg'" alt="Product Image" :width="150" :height="100">
                        </td>
                        <td>{{ item.default }}</td>
                        <td>{{ item.product ? item.product.name : 'Null' }}</td>
                        <td class="text-center">
                            <button v-if="productView && !item.default" class="btn grey-bg" @click="defaultProductImage(item.id, item.module_id)">
                                Set as Default
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
    name: 'Product Image',
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
            productView: false,
            role: false,
            loading: true,
            view: 5,
            filter: 'inactive'
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
            title: 'Product Image',
            description: 'Product Image page'
        });
        if (this.authStore.user === null) {
            this.loading = true;
        } else {
            const permissionsToCheck = ['productView'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'productView') {
                        this.loading = false;
                    }
                }
            });

            // Fetch the product Image list using the extracted method
            await this.fetchProductImageList();
        }
        try {
            await this.$axios
                .get(`/api/image?view=${this.view}`)
                .then(response => {
                    this.currentPageData = response.data.data.data
                    this.totalPages = response.data.data.last_page
                })
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {
        async changePage (page) {
            const response = await this.$axios.get(`api/image?page=${page}&view=${this.view}`)
            this.currentPage = response.data.data.current_page
            this.currentPageData = response.data.data.data
        },

        async fetchProductImageList() {
            try {
                await this.$axios
                    .get(`/api/image?view=${this.view}`)
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async defaultProductImage(id, productId) {
                try {
                    await this.$axios
                        .put(`api/image/${id}/product/${productId}/default`)
                        .then(response => {
                            this.$toast.success('Image set as default successfully.', { position: 'bottom-right', duration: 3000 });
                            this.fetchProductImageList();
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
        },

        async SearchProductImage () {
            await this.$axios
                .get('/api/image', {
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
