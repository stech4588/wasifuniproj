<template>
    <div v-if="loading === false">
        <div v-if="categoryView === true">
            <h1 class="pt-5 mb-3">
                Category Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">ID</th>
                    <th class="col-4">{{ category.id }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">Image</td>
                    <td>
                        <img :src="category.image_url ? category.image_url : '/images/no_image.jpg'" alt="Category Image" :width="150" :height="100">
                    </td>
                </tr>
                <tr>
                    <td class="boldText">Name</td>
                    <td>{{ category.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">Type</td>
                    <td>{{ category.type }}</td>
                </tr>
                <tr>
                    <td class="boldText">Parent Category</td>
                    <td>{{ category.parent_category_name !== null ? category.parent_category_name : 'Null' }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="categoryUpdate" :to="`/category/${category.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/category`" class="btn grey-bg mx-2">back</router-link>
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
            categoryUpdate: false,
            categoryDelete: false,
            categoryView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
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
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/category/${this.$route.params.id}`);
            this.category = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
