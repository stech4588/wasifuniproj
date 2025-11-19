<template>
<div class="order-page">
    <header class="order-hero">
        <div>
            <p class="order-eyebrow">Order details</p>
            <h1>Order #{{ data.id }}</h1>
            <p class="order-subtitle">Review everything about your handcrafted delivery in one serene dashboard.</p>
        </div>
        <button class="ghost-btn" @click="$router.push('/userDashboard')">Back to account</button>
    </header>

    <section class="order-meta">
        <article class="meta-card">
            <span class="meta-label">Status</span>
            <span class="status-pill" :class="data.status">{{ formatStatus(data.status) }}</span>
        </article>
        <article class="meta-card">
            <span class="meta-label">Invoice</span>
            <span class="status-pill invoice" :class="data.invoice_status">{{ formatStatus(data.invoice_status) }}</span>
        </article>
        <article class="meta-card">
            <span class="meta-label">Total</span>
            <span class="meta-value">Rs. {{ formatPrice(data.total_amount) }}</span>
        </article>
    </section>

    <section class="order-items" v-if="data.order_items && data.order_items.length">
        <h2>Items in this order</h2>
        <div class="items-grid">
            <article v-for="prod in data.order_items" :key="prod.id" class="item-card">
                <div class="item-media">
                    <img :src="prod.image_url || '/images/no_image.jpg'" :alt="prod.product_name">
                </div>
                <div class="item-info">
                    <h3>{{ prod.product_name }}</h3>
                    <p class="item-price">Rs. {{ formatPrice(prod.price) }}</p>
                    <p class="item-qty">Quantity: {{ prod.quantity }}</p>
                    <button
                        v-if="reviewAdd && data.status === 'confirmed'"
                        class="outline-btn"
                        @click="storeID(prod)"
                    >
                        <font-awesome-icon icon="plus" fixed-width /> Add review
                    </button>
                </div>
            </article>
        </div>
    </section>

    <section v-else class="empty-state">
        <h3>No items found</h3>
        <p>This order doesn’t list any items yet. If you believe this is an error, reach out to our support team.</p>
    </section>
</div>
</template>

<script>
export default {
    name: "OrderDetail",
    inject: ['authStore'],

    data() {
        return{
            data:{},
            reviewAdd: false,

        }
    },
    async mounted() {
        if (this.authStore.user != null) {
            await this.fetchData(this.$route.params.id);
            await this.getPermission();
        } else{
            this.$toast.error( "You Are Not Login.", { position: 'top-right', duration: 3000 })
            this.$router.push('/login')
        }

    },
    methods: {
        async getPermission(){
            const permissionsToCheck = ['reviewAdd'];
            const permissionResults = await checkPermissions( permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    // if (permission === 'reviewAdd') {
                    //     this.loading = false;
                    // }
                }
            });
        },
        async fetchData(id) {
            try {
                await this.$axios
                    .get(`/api/order/${id}`)
                    .then(response => {
                        this.data = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        storeID(product) {
            // Create a product object with relevant details
            const productToAdd = {
                order_id: product.order_id,
                product_id: product.product_id,
                product_name: product.product_name,
                image_url: product.image_url,
                price: product.price,
            };

            // Store the product object directly in local storage, overwriting any existing data
            localStorage.setItem("productDetails", JSON.stringify(productToAdd));

            // You can also redirect the user to the cart page or perform any other desired action
            this.$router.push("/review/create");
        },
        formatPrice(value) {
            if (value === null || value === undefined) {
                return '--';
            }
            const number = Number(value);
            if (Number.isNaN(number)) {
                return value;
            }
            return number.toFixed(2);
        },
        formatStatus(value) {
            if (!value) return '--';
            return value.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
        }
    }
}
</script>

<style scoped>
.order-page {
    min-height: 100vh;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 60%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    padding: clamp(48px, 6vw, 80px) clamp(24px, 6vw, 96px) clamp(72px, 8vw, 120px);
    display: flex;
    flex-direction: column;
    gap: clamp(28px, 5vw, 48px);
}

.order-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.order-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.order-hero h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 4vw, 3.1rem);
}

.order-subtitle {
    margin: 12px 0 0;
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(58, 58, 58, 0.7);
    max-width: 520px;
}

.ghost-btn,
.outline-btn {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.ghost-btn {
    border: 1px solid rgba(58, 58, 58, 0.28);
    background: rgba(253, 251, 247, 0.9);
    color: rgba(58, 58, 58, 0.75);
    padding: 12px 24px;
}

.ghost-btn:hover {
    transform: translateY(-2px);
    border-color: rgba(58, 58, 58, 0.45);
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
}

.meta-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 24px;
    padding: 18px 20px;
    box-shadow: 0 18px 40px rgba(58, 58, 58, 0.14);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.meta-label {
    font-size: 0.75rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.6);
}

.meta-value {
    font-size: 1.2rem;
    font-weight: 700;
}

.status-pill {
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 0.85rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    background: rgba(58, 58, 58, 0.12);
    color: rgba(58, 58, 58, 0.75);
    align-self: flex-start;
}

.status-pill.invoice {
    background: rgba(212, 175, 55, 0.2);
    color: #3A3A3A;
}

.order-items {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.22);
    border-radius: 32px;
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    padding: clamp(24px, 4vw, 40px);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-items h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3vw, 2.6rem);
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 320px));
    gap: 20px;
    justify-content: center;
}

.item-card {
    background: rgba(234, 218, 192, 0.35);
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-width: 360px;
    margin: 0 auto;
}

.item-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-media {
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
}

.item-info {
    padding: 16px 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-info h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
}

.item-price {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.item-qty {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.65);
}

.outline-btn {
    border: 1px solid rgba(212, 175, 55, 0.5);
    padding: 10px 18px;
    background: transparent;
    color: #3A3A3A;
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.16);
}

.empty-state {
    text-align: center;
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.22);
    border-radius: 28px;
    padding: clamp(32px, 5vw, 56px);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.14);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.empty-state h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.empty-state p {
    margin: 0;
    color: rgba(58, 58, 58, 0.7);
}

@media (max-width: 768px) {
    .order-hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .items-grid {
        grid-template-columns: 1fr;
    }
}
</style>
