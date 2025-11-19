<template>
    <div v-if="loading === false">
        <div v-if="settingView === true">
            <h1 class="pt-5 mb-3">
                Setting Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">
                        ID
                    </th>
                    <th class="col-4">
                        {{ setting.id }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">
                        Module Name
                    </td>
                    <td>{{ setting.module_name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Name
                    </td>
                    <td>{{ setting.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">
                        Value
                    </td>
                    <td>{{ setting.value }}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="settingUpdate" :to="`/setting/${setting.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/setting`" class="btn grey-bg mx-2">back</router-link>
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
            settingView: false,
            settingUpdate: false,
            settingDelete: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['settingAdd', 'settingUpdate', 'settingView', 'settingDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'settingView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/setting/${this.$route.params.id}`);
            this.setting = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
