<template>
<div class="dashboard-page">
    <header class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">S-Tech Atelier</p>
            <h1>My Account</h1>
            <p class="dashboard-subtitle">Track your orders, manage addresses, and keep your atelier profile in tune with your rituals.</p>
    </div>
        <button class="ghost-btn" @click="$router.push('/')">Return to collections</button>
    </header>

    <section class="dashboard-layout">
        <aside class="profile-card">
            <div class="profile-avatar">
                <span>{{ nameInitials }}</span>
            </div>
            <h2>{{ nameDisplay }}</h2>
            <p class="profile-note">Keep your details fresh so we can tailor each delivery perfectly.</p>
            <button class="primary-btn" @click="$router.push('userAddresses')">Manage addresses</button>
        </aside>

        <div class="orders-card">
            <header class="orders-head">
                        <div>
                    <p class="orders-eyebrow">Order history</p>
                    <h2>Your recent journeys</h2>
                </div>
                <div class="pagination" v-if="orders.length">
                    <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
                    <span>Page {{ currentPage }} of {{ totalPages }}</span>
                    <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
                </div>
            </header>

            <div v-if="loading" class="orders-loader">
                <Loader/>
            </div>

            <div v-else-if="orders.length" class="orders-table">
                <div class="orders-header">
                    <span>Order</span>
                    <span>Status</span>
                    <span>Invoice</span>
                    <span>Total</span>
                </div>
                <article
                    v-for="order in orders"
                    :key="order.id"
                    class="orders-row"
                    @click="$router.push(`/orderDetail/${order.id}`)"
                >
                    <span>#{{ order.id }}</span>
                    <span class="status-pill" :class="order.status">{{ formatStatus(order.status) }}</span>
                    <span class="status-pill invoice">{{ formatStatus(order.invoice_status) }}</span>
                    <span>Rs. {{ formatPrice(order.total_amount) }}</span>
                </article>
            </div>

            <div v-else class="empty-orders">
                <h3>No orders yet</h3>
                <p>Discover pieces that feel made for you, and they’ll appear here once ordered.</p>
                <button class="outline-btn" @click="$router.push('/')">Browse collections</button>
            </div>
        </div>
    </section>
</div>
</template>

<script>
export default {
    inject: ['authStore'],
    name: "UserDashboard",
    data(){
        return{
            orders: [
                {id:10,status:'ok',invoice_status:'done',total_amount: 100},
                {id:10,status:'ok',invoice_status:'done',total_amount: 100},
                {id:10,status:'ok',invoice_status:'done',total_amount: 100},
                {id:10,status:'ok',invoice_status:'done',total_amount: 100},
            ],
            currentPage: 1, // Current page
            perPage: 5, // Number of items per page
            totalOrders: 0,
            loading:true,
            name: null,
            nameInitials: 'S',
            nameDisplay: 'Valued Member'
        }
    },
    async mounted() {
        if (this.authStore.user != null) {
            this.name = this.authStore.user.data
            this.calculateInitials();
            await this.fetchData();
             this.loading = false;
        } else{
            this.$toast.error( "You Are Not Login.", { position: 'top-right', duration: 3000 })
            this.$router.push('/login')
        }
    },
    methods:{
        async fetchData() {
            try {
                await this.$axios
                    .get(`/api/order?view=${this.perPage}&&page=${this.currentPage}`)
                    .then(response => {
                        this.orders = response.data.data.data
                        this.totalOrders = response.data.data.total;
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        calculateInitials() {
            if (!this.name) {
                this.nameInitials = 'S';
                this.nameDisplay = 'Valued Member';
                return;
            }

            const parts = String(this.name).split(' ');
            this.nameDisplay = this.name;
            this.nameInitials = parts.slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('') || 'S';
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
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchData();
            }
        },
        // Go to the next page
        nextPage() {
            if (this.currentPage < Math.ceil(this.totalOrders / this.perPage)) {
                this.currentPage++;
                this.fetchData();
            }
        },
    }
}
</script>

<style scoped>
.dashboard-page {
    min-height: 100vh;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 60%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    padding: clamp(48px, 6vw, 80px) clamp(24px, 6vw, 96px) clamp(72px, 8vw, 120px);
    display: flex;
    flex-direction: column;
    gap: clamp(40px, 5vw, 64px);
}

.dashboard-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.dashboard-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.dashboard-hero h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.6rem, 4vw, 3.4rem);
}

.dashboard-subtitle {
    margin: 12px 0 0;
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(58, 58, 58, 0.7);
    max-width: 520px;
}

.ghost-btn,
.primary-btn,
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

.dashboard-layout {
    display: grid;
    grid-template-columns: minmax(280px, 320px) minmax(420px, 1fr);
    gap: clamp(24px, 4vw, 56px);
}

.profile-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 32px;
    padding: clamp(24px, 4vw, 40px);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 20px;
    text-align: center;
    align-items: center;
}

.profile-avatar {
    width: 88px;
    height: 88px;
    border-radius: 24px;
    background: linear-gradient(135deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.profile-card h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.7rem;
}

.profile-note {
    margin: 0;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.7);
    line-height: 1.6;
}

.primary-btn {
    border: none;
    padding: 12px 22px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:hover {
    transform: translateY(-2px);
}

.orders-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.22);
    border-radius: 32px;
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    padding: clamp(24px, 4vw, 40px);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.orders-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.orders-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.orders-head h2 {
    margin: 4px 0 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3vw, 2.6rem);
}

.pagination {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 0.9rem;
}

.pagination button {
    border: 1px solid rgba(58, 58, 58, 0.28);
    background: transparent;
    padding: 8px 16px;
    border-radius: 999px;
    cursor: pointer;
}

.pagination button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.orders-loader {
    display: flex;
    justify-content: center;
    padding: 40px 0;
}

.orders-table {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.orders-header,
.orders-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(100px, 1fr));
    gap: 18px;
    align-items: center;
}

.orders-header {
    font-size: 0.85rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.6);
}

.orders-row {
    padding: 18px 20px;
    border-radius: 20px;
    background: rgba(234, 218, 192, 0.35);
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.orders-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 42px rgba(58, 58, 58, 0.16);
}

.status-pill {
    border-radius: 999px;
    padding: 6px 14px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    background: rgba(58, 58, 58, 0.12);
    color: rgba(58, 58, 58, 0.75);
}

.status-pill.invoice {
    background: rgba(212, 175, 55, 0.2);
    color: #3A3A3A;
}

.orders-row span:last-child {
    font-weight: 600;
}

.empty-orders {
    text-align: center;
    padding: clamp(32px, 5vw, 52px);
    border-radius: 24px;
    background: rgba(234, 218, 192, 0.35);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.empty-orders h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
}

.empty-orders p {
    margin: 0;
    color: rgba(58, 58, 58, 0.7);
}

.outline-btn {
    border: 1px solid rgba(212, 175, 55, 0.5);
    padding: 12px 24px;
    background: transparent;
    color: #3A3A3A;
    align-self: center;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.16);
}

.orders-subline {
    margin: 0;
    font-size: 0.92rem;
    color: rgba(58, 58, 58, 0.72);
    line-height: 1.6;
}

.recently-viewed {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.recently-viewed h2 {
    margin: 0;
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3vw, 2.4rem);
}

.recent-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.recent-card {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.recent-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.recent-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 22px 50px rgba(58, 58, 58, 0.22);
}

.recent-info {
    padding: 14px;
    text-align: center;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.7);
}

@media (max-width: 1024px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
    }

    .orders-header,
    .orders-row {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
}

@media (max-width: 640px) {
    .dashboard-page {
        padding: 40px 18px 64px;
    }

    .dashboard-layout {
        gap: 32px;
    }

    .orders-row {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 16px;
    }
}
</style>
