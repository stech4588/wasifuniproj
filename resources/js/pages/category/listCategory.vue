<template>
    <div v-if="loading === false">
        <div v-if="categoryView === true">
            <h1>Category</h1>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form @submit.prevent="SearchCategory" @keydown="form.onKeydown($event)">
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
                <router-link v-if="categoryAdd" :to="{ name: 'category.add' }" class="btn linked-icon">
                    <font-awesome-icon icon="plus" fixed-width />
                </router-link>
            </div>

            <div v-if="currentPageData.length > 0">
                <table class="table table-bordered ">
                    <thead class="text-white grey-bg">
                    <th class="col">#</th>
                    <th class="col-1">Image</th>
                    <th class="col-3">Name</th>
                    <th class="col-2">Type</th>
                    <th class="col-2">Parent Category</th>
                    <th class="col-3 text-center">Actions</th>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in currentPageData" :key="index">
                        <td>{{ item.id }}</td>
                        <td class="text-center">
                            <img :src="item.image_url ? item.image_url : '/images/no_image.jpg'" alt="Category Image" :width="150" :height="100">
                        </td>
                        <td>{{ item.name }}</td>
                        <td>{{ item.type }}</td>
                        <td>{{ item.parent_category_name !== null ? item.parent_category_name : 'Null' }}</td>
                        <td class="text-center">
                            <router-link :to="{ path: `/category/${item.id}` }" class="btn linked-icon">
                                <font-awesome-icon icon="eye" fixed-width />
                            </router-link>
                            <router-link v-if="categoryUpdate " :to="{ path: `/category/${item.id}/edit` }" class="btn linked-icon">
                                <font-awesome-icon icon="pen" fixed-width />
                            </router-link>
                            <button v-if="categoryDelete " class="btn linked-icon" @click="deleteCategory(item.id)">
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
    name: 'Product',
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
            categoryView: false,
            categoryAdd: false,
            categoryUpdate: false,
            categoryDelete: false,
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
            title: 'Category',
            description: 'Category page'
        });
        if (this.authStore.user === null) {
            this.loading = true;
        } else {
            const permissionsToCheck = ['categoryAdd', 'categoryUpdate', 'categoryView', 'categoryDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'categoryView') {
                        this.loading = false;
                    }
                }
            });
        }
        // Fetch the product list using the extracted method
        await this.fetchCategoryList();
    },

    methods: {
        async changePage (page) {
            const response = await this.$axios.get(`api/category?page=${page}&view=${this.view}&search=${this.form.searchInput}`)
            this.currentPage = response.data.data.current_page
            this.currentPageData = response.data.data.data
        },

        async fetchCategoryList() {
            try {
                await this.$axios
                    .get(`/api/category?view=${this.view}`)
                    .then(response => {
                        this.currentPageData = response.data.data.data
                        this.totalPages = response.data.data.last_page
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async deleteCategory(id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Category?')
            if (permission) {
                try {
                    await this.$axios
                        .delete(`/api/category/${id}`)
                        .then(response => {
                            if (response.data.status === 200) {
                                this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                                // Fetch and update the product Unit list after successful delete
                                this.fetchCategoryList();
                            } else {
                                handleError(e,this.$toast);
                            }
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },

        async SearchCategory () {
            await this.$axios
                .get('/api/category', {
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
