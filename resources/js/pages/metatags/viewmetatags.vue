<template>
  <div v-if="loading === false">
    <div v-if="viewMetatagDetails === true">
      <h1>
          Meta Tag Detail {{ $route.params.id }}
      </h1>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="col-4 boldText">
              ID
            </th>
            <th class="col-4">
              {{ currentPageData.id }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="boldText">
              Name
            </td>
            <td>{{ currentPageData.name }}</td>
          </tr>
          <tr>
            <td class="boldText">
              Content
            </td>
            <td>{{ currentPageData.content }}</td>
          </tr>
          <tr>
            <td class="boldText">
              Page
            </td>
            <td>{{ currentPageData.page.name }}</td>
          </tr>
          <tr>
            <td class="boldText">
              Created At
            </td>
            <td>{{ currentPageData.created_at }}</td>
          </tr>
          <tr>
            <td class="boldText">
              Updated At
            </td>
            <td>{{ currentPageData.updated_at }}</td>
          </tr>
        </tbody>
      </table>
      <button class="btn black-bg" @click="$router.push({ name: 'metatags' })">
        {{ 'Back' }}
      </button>
      <button class="btn linked-icon" @click="$router.push({ path: `/updateMetatags/${currentPageData.id}` })">
        {{ 'Edit Metatags' }}
      </button>
      <button class="btn linked-icon" @click="deletePermission(currentPageData.id)">
        Delete Metatags
      </button>
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
  name: 'View',
  data() {
    return {
        currentPageData: [],
        viewMetatagDetails: false,
      loading: true
    }
  },

  async mounted() {
    if (this.authStore.user === null) {
      this.loading = true
    } else {
      try {
        const permissionsToCheck = ['viewMetatagDetails'];
        const permissionResults = await checkPermissions( permissionsToCheck);
        permissionsToCheck.forEach(permission => {
          if (permissionResults[permission]) {
            this[permission] = true;
            if (permission === 'viewMetatagDetails') {
              this.loading = false;
            }
          }
        });

        await this.$axios.get(`/api/metatags/${this.$route.params.id}`)
          .then((response) => {
            this.currentPageData = response.data.data
          })
      } catch (e) {
          handleError(e,this.$toast);
      }
    }
  },

  methods: {
    async deletePermission(id) {
      const permission = await this.showConfirmationDialog('Are you sure you want to delete this Meta Tag?')
      if (permission) {
        try {
          await this.$axios
            .delete(`/api/metatags/${id}`)
            .then(response => {
              if (response.data.status === 200) {
                  this.$toast.success(response.data.message, { position: 'top-right', duration: 3000 });
                this.$router.push('/metatags')
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
