<template>
    <div v-if="loading === false">
        <div v-if="productView === true">
            <h1 class="pt-5 mb-3">
                Product Detail {{ $route.params.id }}
            </h1>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="col-4 boldText">ID</th>
                    <th class="col-4">{{ product.id }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="boldText">Image</td>
                    <td>
                        <img v-for="image in product.image_url" :key="image" :src="image ? image : '/images/no_image.jpg'" alt="Product Image" :width="150" :height="100">
                    </td>

                </tr>
                <tr>
                    <td class="boldText">Name</td>
                    <td>{{ product.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">Unit</td>
                    <td>{{ product.unit.name }}</td>
                </tr>
                <tr>
                    <td class="boldText">Serial No</td>
                    <td>{{ product.serial_no }}</td>
                </tr>
                <tr>
                    <td class="boldText">Description</td>
                    <td>{{ product.description }}</td>
                </tr>
                <tr>
                    <td class="boldText">Category</td>
                    <td>{{ product.category.name }}</td>
                </tr>
<!--                <tr>-->
<!--                    <td class="boldText">Quantity</td>-->
<!--                    <td>{{ product.quantity }}</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td class="boldText">Price</td>-->
<!--                    <td>{{ product.price }}</td>-->
<!--                </tr>-->
                <tr v-for="variant in product.product_variant" :key="variant">
                    <td>Product Variants</td>
                    <td>
                        Color: {{ variant.color }} ,
                        Size: {{ variant.size ? variant.size.name : 'N/A' }} ,
                        Price: {{ variant.price }} ,
                        Quantity: {{ variant.quantity }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="text-center mt-2">
                <router-link v-if="productUpdate" :to="`/product/${product.id}/edit`" class="btn grey-bg mx-2">Edit</router-link>
                <router-link :to="`/product`" class="btn grey-bg mx-2">back</router-link>
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
            productUpdate: false,
            productDelete: false,
            productView: false,
            loading: true
        }
    },

    async mounted() {
        if (this.authStore.user === null) {
            this.loading = false
        } else {
            const permissionsToCheck = ['productAdd', 'productUpdate', 'productView', 'productDelete'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'productView') {
                        this.loading = false;
                    }
                }
            });
        }
    },

    async created() {
        try {
            const response = await this.$axios.get(`/api/product/${this.$route.params.id}`);
            this.product = response.data.data;
        } catch (e) {
            handleError(e,this.$toast);
        }
    },

    methods: {

    }
}
</script>
