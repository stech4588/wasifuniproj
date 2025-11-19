<template>
  <div v-if="loading === false">
    <div v-if="metatagsView === true">
      <h1>Meta Tags</h1>
      <div class="container my-4">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <form @submit.prevent="SearchMetatag"  @keydown="form.onKeydown($event)">
              <div class="input-group mb-3">
                  <input v-model="form.searchInput" :class="{ 'is-invalid': form.errors.has('searchInput') }"
                         placeholder="Search records..." class="form-control" type="text" name="searchInput"
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
        <router-link v-if="metatagsAdd" :to="{ name: 'metatags.add' }" class="btn linked-icon">
            <font-awesome-icon icon="plus" fixed-width />
        </router-link>
      </div>
      <div v-if="currentPageData && currentPageData.length > 0">
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
                Meta Tag Name
              </th>
              <th class="col-2">
                Content
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
              <td>{{ item.name }}</td>
              <td>{{ item.content }}</td>
              <td class="text-center">
                <router-link :to="{ path: `/viewMetatags/${item.id}` }" class="btn linked-icon">
                    <font-awesome-icon icon="eye" fixed-width />
                </router-link>
                <router-link v-if="metatagsUpdate" :to="{ path: `/updateMetatags/${item.id}` }" class="btn linked-icon">
                    <font-awesome-icon icon="pen" fixed-width />
                </router-link>
                <button v-if="metatagsDelete" class="btn linked-icon" @click="deleteMetatags(item.id)">
                    <font-awesome-icon icon="trash" fixed-width />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="d-flex justify-content-center align-items-center">
        <h3>NO RECORD FOUND</h3>
      </div>

      <!-- Pagination links -->
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
  name: 'Metatag',

  scrollToTop: false,
  data () {
    return {
        form: new this.$form({
            searchInput: ''
        }),
      metatags: [],
      currentPage: 1,
      totalPages: 1,
      currentPageData: [],
      roleId: 0,
      roleDetails: [],
      metatagsUpdate: false,
      metatagsDelete: false,
      metatagsAdd: false,
      metatagsView: false,
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

  async mounted () {
      scrollToTop();
      this.$useHead({
          title: 'metatags',
          description: 'metatags page'
      });
    if (this.authStore.user === null) {
      this.loading = false
    } else {
        let ifPermissionViewExist = false;

      try {
      const permissionsToCheck = ['metatagsView','metatagsAdd','metatagsDelete','metatagsUpdate'];
      const permissionResults = await checkPermissions( permissionsToCheck);
      permissionsToCheck.forEach(metatags => {
        if (permissionResults[metatags]) {
          this[metatags] = true;
          if (metatags === 'metatagsView') {
              ifPermissionViewExist = true;
          }
        }
      });
      } catch (e) {
          handleError(e,this.$toast);
      }
        // Fetch the product list using the extracted method
        await this.fetchMetaTagList(ifPermissionViewExist);
    }
  },

  methods: {
    async changePage (page) {
      try {
        const response = await this.$axios.get(`/api/metatags?page=${page}&view=${this.view}&search=${this.form.searchInput}`)
          if (response.status === 200){
              this.currentPageData = response.data.data.data
              this.currentPage = page
          }
      } catch (e) {
          handleError(e,this.$toast);
      }
    },

      async fetchMetaTagList(ifPermissionViewExist) {

          if (ifPermissionViewExist){
              try {
                  await this.$axios
                      .get(`/api/metatags?view=${this.view}`)
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

    async deleteMetatags (id) {
      const permission = await this.showConfirmationDialog('Are you sure you want to delete this Permission?')
      if (permission) {
        try {
          const response = await this.$axios.delete(`/api/metatags/${id}`)

          if (response.data.status === 200) {
              this.$toast.success(response.data.message, { position: 'top-right', duration: 3000 });
              // Fetch and update the role list after successful delete
              this.fetchMetaTagList(true);
          }
        } catch (e) {
            handleError(e,this.$toast);
        }
      }
    },
    SearchMetatag () {
        try{
      this.$axios.get('/api/metatags', {
          params: {
            search: this.form.searchInput,
              view: this.view
          }
        })
        .then((response) => {
            if (response.status === 200) {
                this.currentPageData = response.data.data.data
                this.totalPages = response.data.data.last_page
            }
        })
        } catch (e) {
            handleError(e,this.$toast);
        }
    }
  }
}
</script>
