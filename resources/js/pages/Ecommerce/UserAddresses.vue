<template>
    <div class="addresses-page">
        <header class="addresses-hero">
            <div>
                <p class="addresses-eyebrow">My addresses</p>
                <h1>Delivery destinations</h1>
                <p class="addresses-subtitle">Curate the locations that bring your handcrafted selections home.</p>
            </div>
            <button class="primary-btn" @click="showAddressForm">{{ showForm ? 'Cancel' : 'Add address' }}</button>
        </header>

        <transition name="form-fade">
            <section v-if="showForm" class="address-form-card">
                <h2>New address</h2>
                <p class="form-note">We’ll remember these details for faster checkout and tailored recommendations.</p>
                <form @submit.prevent="submitAddress" class="address-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Country</label>
                            <select v-model="form.country_id" @change="fetchCities" required>
                                <option v-for="unit in country" :key="unit.id" :value="unit.id">{{ unit.country_name }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <select v-model="form.city_id" required>
                                <option v-for="unit in city" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select v-model="form.type" required>
                                <option v-for="unit in type" :key="unit.id" :value="unit.name">{{ unit.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address line 1</label>
                        <input type="text" v-model="form.street_1" placeholder="Street, apartment, etc." required />
                    </div>
                    <div class="form-group">
                        <label>Address line 2</label>
                        <input type="text" v-model="form.street_2" placeholder="Additional details" required />
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="primary-btn">Save address</button>
                        <button type="button" class="ghost-btn" @click="showAddressForm">Cancel</button>
                    </div>
                </form>
            </section>
        </transition>

        <section v-if="loading" class="addresses-loader">
            <Loader/>
        </section>

        <section v-else class="addresses-grid">
            <article v-for="Address in addresses" :key="Address.id" class="address-card">
                <div class="address-headline">
                    <span class="address-type">{{ Address.type }}</span>
                    <button class="text-link" @click="Delete(Address.id)">Delete</button>
                </div>
                <ul class="address-details">
                    <li>
                        <span class="label">Country</span>
                        <span>{{ Address.country.country_name }}</span>
                    </li>
                    <li>
                        <span class="label">City</span>
                        <span>{{ Address.city.name }}</span>
                    </li>
                    <li>
                        <span class="label">Address 1</span>
                        <span>{{ Address.street_1 }}</span>
                    </li>
                    <li>
                        <span class="label">Address 2</span>
                        <span>{{ Address.street_2 }}</span>
                    </li>
                </ul>
            </article>
        </section>

        <section v-if="!loading && addresses.length === 0" class="empty-addresses">
            <h3>No addresses yet</h3>
            <p>Add a shipping or billing address to streamline your next checkout.</p>
            <button class="outline-btn" @click="showAddressForm">Add your first address</button>
        </section>

        <div class="pagination" v-if="!loading && addresses.length">
            <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
            <span>Page {{ currentPage }} of {{ Math.ceil(totalOrders / perPage) }}</span>
            <button @click="nextPage" :disabled="currentPage === Math.ceil(totalOrders / perPage)">Next</button>
        </div>
    </div>
</template>

<script>
export default {
    inject: ['authStore'],
    name: "UserAddresses",
    data() {
        return {
            showForm: false,
            form: new this.$form({
                type: '',
                country_id: 0,
                city_id: 0,
                state_id: 0,
                street_1: '',
                street_2: '',
            }),
            country:[],
            city:[],
            state:[],
            type:[
                { name:'Shipping'},
                { name:'Billing'},
            ],
            currentPage: 1, // Current page
            perPage: 5, // Number of items per page
            totalOrders: 0,
            loading:true,
            addresses:[],

        };
    },
    async mounted() {
        await this.getCountries();

        if (this.authStore.user != null) {
            await this.fetchData();
            this.loading = false;
        } else{
            this.$toast.error( "You Are Not Login.", { position: 'top-right', duration: 3000 })
            this.$router.push('/login')
        }

    },
    methods: {
        async getCountries(){
            try {
                await this.$axios
                    .get('/api/countries')
                    .then(response => {
                        this.country = response.data.data.Country
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        showAddressForm() {
            this.showForm = !this.showForm;
            if (!this.showForm) {
                this.form.reset();
                this.form.clear();
            }
        },
        async submitAddress() {
            try{
                const response = await this.form.post('/api/address')
                if (response.status === 200) {
                    this.$toast.success( response.message, { position: 'top-right', duration: 3000 })
                    this.form.reset();
                    this.form.clear();
                    await this.fetchData();
                    this.showForm = false;
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        // async fetchStates() {
        //     try {
        //         const response = await this.$axios.get(`/api/states/${this.form.country_id}`);
        //         this.state = response.data.data.states;
        //     } catch (e) {
        //         this.handleError(e, this.$toast);
        //     }
        // },
        async fetchCities() {
            try {
                const response = await this.$axios.get(`/api/cities/${this.form.country_id}`);
                this.city = response.data.data.cities;
            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        async fetchData() {
            try {
                await this.$axios
                    .get(`/api/address?view=${this.perPage}&&page=${this.currentPage}`)
                    .then(response => {
                        this.addresses = response.data.data.data
                        this.totalOrders = response.data.data.total;
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
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
        async Delete (id) {
            const permission = await this.showConfirmationDialog('Are you sure you want to delete this Address?')
            if (permission) {
                try {
                    const response = await this.$axios.delete(`/api/address/${id}`)

                    if (response.data.status === 200) {
                        this.$toast.success(response.data.message, { position: 'top-right', duration: 3000 });
                        // Fetch and update the role list after successful delete
                        this.fetchData();
                    }
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
        },
    },
}
</script>

<style scoped>
.addresses-page {
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 60%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    padding: clamp(48px, 6vw, 80px) clamp(24px, 6vw, 96px) clamp(72px, 8vw, 120px);
    display: flex;
    flex-direction: column;
    gap: clamp(32px, 5vw, 52px);
}

.addresses-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.addresses-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.addresses-hero h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 4vw, 3.1rem);
}

.addresses-subtitle {
    margin: 12px 0 0;
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(58, 58, 58, 0.7);
    max-width: 560px;
}

.primary-btn,
.ghost-btn,
.outline-btn,
.text-link button {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.primary-btn {
    border: none;
    padding: 12px 24px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:hover {
    transform: translateY(-2px);
}

.ghost-btn {
    border: 1px solid rgba(58, 58, 58, 0.28);
    background: rgba(253, 251, 247, 0.9);
    color: rgba(58, 58, 58, 0.75);
    padding: 12px 24px;
}

.ghost-btn:hover {
    transform: translateY(-2px);
}

.address-form-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 32px;
    padding: clamp(24px, 4vw, 40px);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.address-form-card h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.9rem;
}

.form-note {
    margin: 0;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.7);
}

.address-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.form-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.form-group {
    flex: 1 1 220px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 0.8rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.68);
    font-weight: 600;
}

.form-group select,
.form-group input {
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 12px 14px;
    font-size: 0.95rem;
    color: #3A3A3A;
}

.form-actions {
    display: flex;
    gap: 12px;
}

.addresses-loader {
    display: flex;
    justify-content: center;
    padding: 40px 0;
}

.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 320px));
    gap: 20px;
    justify-content: center;
}

.address-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 28px;
    padding: 18px 20px;
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.14);
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.address-headline {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.address-type {
    text-transform: uppercase;
    letter-spacing: 0.18em;
    font-size: 0.75rem;
    color: rgba(58, 58, 58, 0.6);
}

.text-link {
    background: transparent;
    border: none;
    padding: 0;
    color: rgba(58, 58, 58, 0.6);
    cursor: pointer;
}

.text-link:hover {
    color: #3A3A3A;
}

.address-details {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.address-details li {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 0.95rem;
}

.address-details .label {
    font-weight: 600;
    color: rgba(58, 58, 58, 0.6);
}

.empty-addresses {
    text-align: center;
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 28px;
    padding: clamp(32px, 5vw, 56px);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

.empty-addresses h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.empty-addresses p {
    margin: 0;
    color: rgba(58, 58, 58, 0.7);
}

.outline-btn {
    border: 1px solid rgba(212, 175, 55, 0.5);
    padding: 12px 24px;
    background: transparent;
    color: #3A3A3A;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.16);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 12px;
}

.pagination button {
    border: 1px solid rgba(58, 58, 58, 0.28);
    background: transparent;
    padding: 10px 18px;
    border-radius: 999px;
    cursor: pointer;
}

.pagination button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.form-fade-enter-active,
.form-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.form-fade-enter-from,
.form-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .addresses-grid {
        grid-template-columns: 1fr;
    }
}
</style>
