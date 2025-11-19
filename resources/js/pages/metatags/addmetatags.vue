<template>
  <div v-if="loading === false">
    <div v-if="metatagsAdd === true">
      <h1>
        Add Meta Tag
      </h1>
      <div class="row">
        <div class="col-lg-10 m-auto">
            <form @submit.prevent="addMetatags" @keydown="form.onKeydown($event)">
              <!-- Name -->
              <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-md-end">{{ ('Name') }}</label>
                <div class="col-md-7">
                  <input v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" class="form-control"
                    type="text" name="name" required>
                  <has-error :form="form" field="name" />
                </div>
              </div>
              <!-- Content -->
              <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-md-end">{{ ('Content') }}</label>
                <div class="col-md-7">
                  <input v-model="form.content" :class="{ 'is-invalid': form.errors.has('content') }"
                    class="form-control" type="text" name="content" required>
                  <has-error :form="form" field="content" />
                </div>
              </div>
              <!-- Page -->
              <div class="mb-3 row">
                <label class="col-md-3 col-form-label text-md-end">{{ ('Page') }}</label>
                <div class="col-md-7">
                    <select v-model="form.page_id" class="form-control" name="page_id" required>
                        <option v-for="page in pages" :key="page.id" :value="page.id">{{ page.name }}</option>
                    </select>
                </div>
              </div>

              <div class="mb-3 row">
                <div class="col-md-7 offset-md-3 d-flex">
                  <!-- Submit Button -->
                  <button class="btn grey-bg" :disabled="form.busy">
                    {{ 'Add' }}
                  </button>
                  <button @click="$router.push({ name: 'metatags' })" class="btn black-bg ms-1">
                    {{ 'Cancel' }}
                  </button>
                </div>
              </div>
            </form>
        </div>
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
  name: 'Add',

  data () {
        return {
      form: new this.$form({
          name: '',
          content: '',
          page_id: null
      }),
            metatagsAdd: false,
            pages: [],
      loading: true
  }
  },

  async mounted() {
      this.$useHead({
          title: 'Permission',
          description: 'Add Permission page'
      });
    if (this.authStore.user === null) {
      this.loading = false
    } else {
      try {
        const permissionsToCheck = ['metatagsAdd'];
      const permissionResults = await checkPermissions( permissionsToCheck);
      permissionsToCheck.forEach(permission => {
        if (permissionResults[permission]) {
          this[permission] = true;
          if (permission === 'metatagsAdd') {
            this.loading = false;
          }
        }
      });
      } catch (e) {
          this.loading = true
          handleError(e,this.$toast);
      }

        try {
            const response = await this.$axios.get('/api/pages')
            if (response.status === 200){
                this.pages = response.data.data
            }

        } catch (e) {
            handleError(e,this.$toast);
        }

    }
  },

  methods: {
    async addMetatags() {
      try {
        const { data } = await this.form.post('/api/metatags')
        if (data.status === 200) {
          this.$toast.success( data.message,{ position: 'top-right', duration: 3000 })
          this.$router.push('/metatags')
        }
      } catch (e) {
          handleError(e,this.$toast);
      }
    }
  }
}
</script>

<style scoped></style>
