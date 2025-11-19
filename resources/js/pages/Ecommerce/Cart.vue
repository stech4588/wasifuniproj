<template>
<div class="cart-page">
    <header class="cart-hero">
        <div>
            <p class="cart-eyebrow">Your atelier collection</p>
            <h1>Cart</h1>
        </div>
        <button class="ghost-btn" @click="$router.push('/')">Continue shopping</button>
    </header>

    <section v-if="cart.length > 0" class="cart-layout">
        <div class="cart-items">
            <article v-for="(item,index) in cart" :key="index" class="cart-card">
                <div class="cart-media">
                    <img :src="item.image_url || '/images/no_image.jpg'" :alt="item.name">
                </div>
                <div class="cart-info">
                    <div class="cart-headline">
                        <h2>{{ item.name }}</h2>
                        <button class="text-link" @click="removeItem(index)">Remove</button>
                    </div>
                    <p class="cart-subline">
                        <span v-if="item.size">Size: {{ item.size }}</span>
                        <span v-if="item.color">Colour: {{ item.color }}</span>
                        <span>Variant ID: {{ item.variant_id }}</span>
                    </p>
                    <div class="cart-price">
                        <div v-if="item.sale">
                            <span class="price-old">Rs. {{ formatPrice(item.price) }}</span>
                            <span class="price-new">Rs. {{ formatPrice(applySale(item.price, item.sale.sale_price)) }}</span>
                            <span class="badge-sale">-{{ item.sale.sale_price }}%</span>
                        </div>
                        <div v-else>Rs. {{ formatPrice(item.price) }}</div>
                    </div>
                    <div class="cart-actions">
                        <div class="quantity-control">
                            <button @click="decreaseQuantity(index)" :disabled="item.quantity === 1">-</button>
                            <span>{{ item.quantity }}</span>
                            <button @click="increaseQuantity(index)" :disabled="item.quantity === 100">+</button>
                        </div>
                        <p class="cart-note">Ships within 7–8 working days</p>
                    </div>
                </div>
            </article>
        </div>

        <aside class="cart-summary">
            <form @submit.prevent="placeOrder" @keydown="form.onKeydown($event)" class="summary-card">
                <h2>Order summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rs. {{ formatPrice(calculateSubtotal()) }}</span>
                </div>
                <div class="summary-row coupon-row">
                    <div>
                        <label>Apply coupon</label>
                        <input type="text" v-model="coupon" placeholder="Enter code">
                    </div>
                    <button type="button" class="outline-btn" :disabled="!coupon" @click="applyCoupon">Apply</button>
                </div>
                <p class="coupon-message" v-if="couponMessage">{{ couponMessage }}</p>
                <div class="summary-row total">
                    <span>Total amount</span>
                    <span>Rs. {{ formatPrice(calculateTotal()) }}</span>
                </div>

                <div class="address-section">
                    <div class="address-headline">
                        <p>Delivery details</p>
                        <button type="button" class="text-link" @click="openModal()">Add new address</button>
                    </div>
                    <label>Billing address</label>
                    <select v-model="form.billing_address_id" required>
                        <option v-for="billingAddress in billingAddresses" :key="billingAddress.id" :value="billingAddress.id">
                            {{ billingAddress.street_1 + ', ' +  billingAddress.city.name + ', ' + billingAddress.country.country_name}}
                        </option>
                    </select>
                    <has-error :form="form" field="billing_address_id" />

                    <label>Shipping address</label>
                    <select v-model="form.shipping_address_id" required>
                        <option v-for="shippingAddress in shippingAddresses" :key="shippingAddress.id" :value="shippingAddress.id">
                            {{ shippingAddress.street_1 + ', ' +  shippingAddress.city.name + ', ' + shippingAddress.country.country_name}}
                        </option>
                    </select>
                    <has-error :form="form" field="shipping_address_id" />
                </div>

                <div class="payment-section">
                    <p class="payment-headline">Payment method</p>
                    <label class="payment-option">
                        <input type="radio" value="cod" v-model="paymentMethod">
                        <span>Cash on delivery</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" value="stripe" v-model="paymentMethod">
                        <span>Card (Stripe)</span>
                    </label>
                    <div class="card-element-wrapper" v-if="paymentMethod === 'stripe'">
                        <div id="card-element" class="card-element"></div>
                        <p class="stripe-error" v-if="stripeError">{{ stripeError }}</p>
                    </div>
                </div>

                <label>Order note</label>
                <textarea class="order-note" placeholder="Leave a note for the atelier team..."></textarea>

                <button type="submit" class="primary-btn">Check out</button>
                <p class="checkout-note">Discount codes and gift cards can be added at checkout.</p>
            </form>
        </aside>
    </section>

    <section v-else class="empty-cart">
        <div class="empty-card">
            <h2>Your curated cart is empty</h2>
            <p>Add pieces that invite calm and craftsmanship into your everyday rituals.</p>
            <button class="primary-btn" @click="$router.push('/')">Browse collections</button>
        </div>
    </section>

    <section class="recently-viewed" v-if="hotSellingProducts.length">
        <h2>Curated for you</h2>
        <div class="recent-grid">
            <article
                class="recent-card"
                v-for="(item, index) in hotSellingProducts"
                :key="index"
                @click="$router.push(`/product-detail/${item.id}`)"
            >
                <img :src="item.image_url || '/images/no_image.jpg'" :alt="item.name">
                <div class="recent-info">
                    <p>{{ item.name }}</p>
                </div>
            </article>
        </div>
    </section>

    <div id="productModal" class="address-modal" v-if="showAddressModal" @click.self="closeModal">
        <div class="modal-dialog">
            <button class="modal-close" @click="closeModal" aria-label="Close">&times;</button>
            <form @submit.prevent="submitAddress" class="address-form">
                <h2>Add address</h2>
                <div class="form-row">
                    <label>Country</label>
                    <select v-model="addressform.country_id" @change="fetchCities" required>
                        <option v-for="unit in country" :key="unit.id" :value="unit.id">{{ unit.country_name }}</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>City</label>
                    <select v-model="addressform.city_id" required>
                        <option v-for="unit in city" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Type</label>
                    <select v-model="addressform.type" required>
                        <option v-for="unit in type" :key="unit.id" :value="unit.name">{{ unit.name }}</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Address line 1</label>
                    <input type="text" v-model="addressform.street_1" required />
                </div>
                <div class="form-row">
                    <label>Address line 2</label>
                    <input type="text" v-model="addressform.street_2" required />
                </div>
                <button type="submit" class="primary-btn">Save address</button>
            </form>
        </div>
    </div>
</div>
</template>

<script>
import { loadStripe } from '@stripe/stripe-js';

export default {
    inject: ['authStore'],
    name: "Cart",
    data() {
        return{
            showAddressModal: false,
            form: new this.$form({
                product_id: [],
                coupon_id: 0,
                billing_address_id: null,
                shipping_address_id: null,
                other_charge_id: 0,
                sub_total: 0,
                discountType: '',
                discount: 0,
                total_amount: 0,
                payment_method: 'cod',
                payment_intent_id: null,
                currency: null,
            }),
            cart: [ ] ,
            hotSellingProducts: [
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 1' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 2' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 3' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 4' },
            ],
            subTotal: 0,
            totalAmount: 0,
            coupon: null,
            couponMessage: '',
            couponAmount: 0,
            paymentMethod: 'cod',
            stripe: null,
            stripeElements: null,
            cardElement: null,
            stripeClientSecret: null,
            stripePublicKey: null,
            stripeProcessing: false,
            stripeError: '',
            currency: 'usd',
            billingAddresses: [],
            shippingAddresses: [],
            addressform: new this.$form({
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

        }
    },
    async created() {
        this.getCartDataFromLocalStorage();
        this.getHotSellingProducts();
        if (this.authStore.user != null) {
            this.fetchBillingAddress();
            this.fetchShippingAddress();
            await this.fetchStripeConfig();
        }else{
            this.$toast.error( "First Login to place order.", { position: 'top-right', duration: 3000 })
        }

        },
    mounted() {

    },
    beforeUnmount() {
        this.destroyStripe();
    },
    watch: {
        paymentMethod(value) {
            if (value === 'stripe') {
                this.$nextTick(() => {
                    this.initializeStripe();
                });
            } else {
                this.destroyStripe();
                this.form.payment_method = 'cod';
                this.form.payment_intent_id = null;
            }
        }
    },
    methods: {
        async fetchCities() {
            try {
                const response = await this.$axios.get(`/api/cities/${this.addressform.country_id}`);
                this.city = response.data.data.cities;
            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        async submitAddress() {

            try{
                const response = await this.addressform.post('/api/address')
                if (response.status === 200) {
                    this.$toast.success( response.message, { position: 'top-right', duration: 3000 })
                    await this.fetchBillingAddress();
                    await this.fetchShippingAddress();
                    this.addressform.reset();
                    this.addressform.clear();
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
            this.showAddressModal = false;
        },
        async openModal() {
            if (this.authStore.user != null) {
                try {
                    await this.$axios
                        .get('/api/countries')
                        .then(response => {
                            this.country = response.data.data.Country
                        })
                } catch (e) {
                    handleError(e,this.$toast);
                }
                this.addressform.reset();
                this.addressform.clear();
                this.city = [];
                this.showAddressModal = true;
            } else {
                this.$toast.error("Please login to manage addresses.", { position: 'top-right', duration: 3000 });
                this.$router.push({ name: 'login', query: { returnUrl: 'cart' } });
            }

        },
        increaseQuantity(i) {
            this.totalAmount = 0;
            this.couponAmount = 0;
            if (this.cart[i].quantity < 100) {
                this.cart[i].quantity++;
            }
            localStorage.setItem("cart", JSON.stringify(this.cart));
            this.calculateSubtotal();
            this.calculateTotal();
        },
        decreaseQuantity(i) {
            this.totalAmount = 0;
            this.couponAmount = 0;
            if (this.cart[i].quantity > 1) {
                this.cart[i].quantity--;
            }
            localStorage.setItem("cart", JSON.stringify(this.cart));
            this.calculateSubtotal();
            this.calculateTotal();
        },
        getCartDataFromLocalStorage() {
            const cartData = JSON.parse(localStorage.getItem("cart"));
            if (cartData) {
                // If cart data exists in local storage, assign it to the cart property
                this.cart = cartData;
            }
        },
        removeItem(index) {
            this.cart.splice(index, 1);
            // Update local storage with the modified cart array
            localStorage.setItem("cart", JSON.stringify(this.cart));
            this.calculateSubtotal();
            this.calculateTotal();
    },
        calculateSubtotal() {
            this.subTotal = this.cart.reduce((total, item) => {
                if (item.sale && item.sale.sale_price > 0) {
                    // If the item has a sale, calculate the subtotal based on the sale price
                    total += (item.quantity * (item.price - (item.price * (item.sale.sale_price / 100))));
                } else {
                    // If no sale, calculate the subtotal based on the regular price
                    total += (item.quantity * item.price);
                }
                return total;
            }, 0);
            // this.totalAmount = this.subTotal;
        return this.subTotal;
    },
        calculateTotal() {
            this.totalAmount = this.subTotal - this.couponAmount;
        return this.totalAmount;
    },
        async applyCoupon() {
            if (this.coupon) {
                this.calculateSubtotal();
                try{
                    const response = await this.$axios.get(`/api/applyCoupon/${this.coupon}`)
                    if (response.status === 200) {
                        if (response.data.data.expired) {
                            this.couponMessage = response.data.data.expired;
                            this.totalAmount = this.subTotal;
                        }
                        else{
                            const type = response.data.data.type
                            if (type === 'amount'){
                                this.couponAmount = response.data.data.amount
                            } else if (type === 'percentage') {
                                const couponPercentage = response.data.data.percentage
                                this.couponAmount = (this.subTotal/100) * (couponPercentage)
                            }
                            if (this.couponAmount < this.subTotal){
                                this.couponMessage = 'Applied Successful'
                            } else{
                                this.couponAmount = 0;
                                this.coupon = null;
                                this.couponMessage = 'Low Order Amount'
                            }

                        }
                    }
                } catch (e) {
                    handleError(e,this.$toast);
                }
            }
            this.calculateTotal();
        },
        closeModal() {
            this.showAddressModal = false;
        },
        async placeOrder() {
            if (!this.cart.length) {
                this.$toast.error("Your cart is empty.", { position: 'top-right', duration: 3000 });
                return;
            }
            if (this.authStore.user != null){
                    const subtotal = this.calculateSubtotal();
                    const total = this.calculateTotal();
                    this.form.product_details = this.cart.map(item => ({ id: item.id, name: item.name, quantity: item.quantity, price: item.price, variant_id: item.variant_id, sale: item.sale }));
                    this.form.coupon_id= this.coupon
                    this.form.other_charge_id= 1
                    this.form.sub_total= subtotal
                    this.form.discount= this.couponAmount
                    this.form.total_amount= total
                    this.form.payment_method = this.paymentMethod

            if (this.paymentMethod === 'stripe') {
                if (this.stripeProcessing) {
                    return;
                }
                const paymentOk = await this.handleStripePayment(total);
                if (!paymentOk) {
                    return;
                }
            } else {
                this.form.payment_intent_id = null;
                this.form.currency = null;
            }

            try{
                    const response = await this.form.post('/api/order')
                if (response.status === 200) {
                    this.$toast.success( response.message, { position: 'top-right', duration: 3000 })
                    localStorage.removeItem('cart');
                    if (this.paymentMethod === 'stripe') {
                        this.destroyStripe();
                        this.paymentMethod = 'cod';
                    }
                    this.$router.push('/userDashboard')
                }
            } catch (e) {
                handleError(e,this.$toast);
            }
            } else {
                this.$toast.error( "First Login to place order.", { position: 'top-right', duration: 3000 })
                this.$router.push({ name: 'login', query: { returnUrl: 'cart' } });
            }
        },
        async fetchBillingAddress() {
            try {
                await this.$axios
                    .get(`/api/address?filter=billing`)
                    .then(response => {
                        this.billingAddresses = response.data.data
                        if (response.data.data[0]) {
                            const lastElement = response.data.data[0];
                            this.form.billing_address_id = lastElement.id;
                        }
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchShippingAddress() {
            try {
                await this.$axios
                    .get(`/api/address?filter=shipping`)
                    .then(response => {
                        this.shippingAddresses = response.data.data
                        if (response.data.data[0]) {
                            const lastElement = response.data.data[0];
                            this.form.shipping_address_id = lastElement.id;
                        }
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async fetchStripeConfig() {
            try {
                const response = await this.$axios.get('/api/stripe/config');
                this.stripePublicKey = response.data.data.public_key;
                if (response.data.data.currency) {
                    this.currency = response.data.data.currency;
                }
            } catch (e) {
                console.error('Unable to load Stripe config', e);
            }
        },
        async initializeStripe() {
            try {
                if (!this.stripePublicKey) {
                    await this.fetchStripeConfig();
                }

                if (!this.stripePublicKey) {
                    this.$toast.error('Stripe configuration is missing.', { position: 'bottom-right', duration: 3000 });
                    this.paymentMethod = 'cod';
                    return;
                }

                if (!this.stripe) {
                    this.stripe = await loadStripe(this.stripePublicKey);
                }

                if (!this.stripeElements && this.stripe) {
                    this.stripeElements = this.stripe.elements();
                }

                if (this.stripeElements && !this.cardElement) {
                    this.cardElement = this.stripeElements.create('card');
                    this.cardElement.mount('#card-element');
                    this.cardElement.on('change', (event) => {
                        this.stripeError = event.error ? event.error.message : '';
                    });
                }
            } catch (e) {
                handleError(e, this.$toast);
                this.paymentMethod = 'cod';
            }
        },
        destroyStripe() {
            if (this.cardElement) {
                this.cardElement.destroy();
                this.cardElement = null;
            }
            this.stripeElements = null;
            this.stripeClientSecret = null;
            this.stripeError = '';
            this.stripeProcessing = false;
        },
        async handleStripePayment(total) {
            if (!this.stripe || !this.cardElement) {
                await this.initializeStripe();
            }

            if (!this.stripe || !this.cardElement) {
                return false;
            }

            try {
                this.stripeProcessing = true;
                this.stripeError = '';

                const response = await this.$axios.post('/api/stripe/payment-intent', {
                    amount: total,
                    currency: this.currency,
                });

                const clientSecret = response.data.data.client_secret;
                const paymentIntentId = response.data.data.payment_intent_id;

                const billingDetails = {
                    name: this.authStore.user ? this.authStore.user.name : undefined,
                    email: this.authStore.user ? this.authStore.user.email : undefined,
                };

                const confirmation = await this.stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: this.cardElement,
                        billing_details: billingDetails,
                    }
                });

                if (confirmation.error) {
                    this.stripeError = confirmation.error.message;
                    this.$toast.error(confirmation.error.message, { position: 'bottom-right', duration: 3000 });
                    return false;
                }

                if (confirmation.paymentIntent.status !== 'succeeded' && confirmation.paymentIntent.status !== 'requires_capture') {
                    this.$toast.error('Card payment could not be completed. Please try again.', { position: 'bottom-right', duration: 3000 });
                    return false;
                }

                this.stripeClientSecret = clientSecret;
                this.form.payment_intent_id = paymentIntentId;
                this.form.currency = this.currency;
                return true;
            } catch (e) {
                handleError(e, this.$toast);
                return false;
            } finally {
                this.stripeProcessing = false;
            }
        },
        async getHotSellingProducts() {
            try {
                await this.$axios
                    .get('/api/hot-selling-products')
                    .then(response => {
                        this.hotSellingProducts = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
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
        applySale(price, salePercent) {
            if (!price || !salePercent) {
                return price;
            }
            return price - (price * salePercent) / 100;
        }
    },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.cart-page {
    min-height: 100vh;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 60%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    padding: clamp(48px, 6vw, 80px) clamp(24px, 6vw, 96px) clamp(72px, 8vw, 120px);
    display: flex;
    flex-direction: column;
    gap: clamp(40px, 6vw, 64px);
}

.cart-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.cart-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.cart-hero h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 4vw, 3.2rem);
}

.ghost-btn,
.primary-btn,
.outline-btn,
.text-link {
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

.cart-layout {
    display: grid;
    grid-template-columns: minmax(320px, 2fr) minmax(280px, 1fr);
    gap: clamp(28px, 5vw, 56px);
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-card {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    padding: clamp(18px, 3vw, 28px);
    display: grid;
    grid-template-columns: minmax(120px, 160px) 1fr;
    gap: 18px;
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.14);
}

.cart-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}

.cart-headline {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 12px;
}

.cart-headline h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
}

.text-link {
    border: none;
    background: transparent;
    padding: 0;
    color: rgba(58, 58, 58, 0.6);
}

.text-link:hover {
    color: #3A3A3A;
}

.cart-subline {
    margin: 8px 0;
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.65);
}

.cart-price {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1rem;
    margin: 8px 0 12px;
}

.price-old {
    text-decoration: line-through;
    color: rgba(58, 58, 58, 0.45);
}

.price-new {
    font-weight: 700;
}

.badge-sale {
    background: rgba(212, 175, 55, 0.92);
    color: #3A3A3A;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.cart-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 1.05rem;
}

.quantity-control button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(212, 175, 55, 0.85);
    color: #3A3A3A;
    cursor: pointer;
}

.quantity-control button:disabled {
    background: rgba(234, 218, 192, 0.6);
    cursor: not-allowed;
}

.cart-note {
    margin: 0;
    font-size: 0.85rem;
    color: rgba(58, 58, 58, 0.6);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.cart-summary {
    position: sticky;
    top: 96px;
}

.summary-card {
    background: rgba(253, 251, 247, 0.95);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 32px;
    padding: clamp(24px, 4vw, 40px);
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
}

.summary-card h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1rem;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: 700;
}

.coupon-row {
    gap: 12px;
    flex-wrap: wrap;
}

.coupon-row input {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 12px 14px;
    font-size: 0.95rem;
    color: #3A3A3A;
}

.coupon-message {
    margin: 0;
    font-size: 0.85rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(212, 106, 79, 0.8);
}

.address-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.address-headline {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.address-section label {
    font-size: 0.8rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.65);
    font-weight: 600;
}

.address-section select {
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 12px 14px;
}

.payment-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-headline {
    font-size: 0.8rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.65);
    font-weight: 600;
    margin: 0;
}

.payment-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 12px;
    background: rgba(253, 251, 247, 0.85);
    cursor: pointer;
}

.payment-option input {
    width: 18px;
    height: 18px;
    accent-color: rgba(212, 175, 55, 0.85);
    cursor: pointer;
}

.payment-option span {
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.8);
}

.payment-option.disabled,
.payment-option.disabled input {
    cursor: not-allowed;
}

.payment-option.disabled {
    opacity: 0.6;
}

.card-element-wrapper {
    margin-top: 12px;
}

.card-element {
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
}

.stripe-error {
    color: #d9534f;
    margin-top: 8px;
    font-size: 0.9rem;
}

.order-note {
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 14px;
    min-height: 110px;
    resize: vertical;
    font-size: 0.95rem;
}

.primary-btn {
    border: none;
    padding: 14px 22px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:hover {
    transform: translateY(-2px);
}

.outline-btn {
    border: 1px solid rgba(212, 175, 55, 0.5);
    padding: 12px 24px;
    color: #3A3A3A;
    background: transparent;
}

.outline-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.outline-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.16);
}

.checkout-note {
    margin: 0;
    text-align: center;
    font-size: 0.85rem;
    color: rgba(58, 58, 58, 0.6);
}

.empty-cart {
    display: flex;
    justify-content: center;
    align-items: center;
}

.empty-card {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 32px;
    padding: clamp(32px, 6vw, 56px);
    text-align: center;
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-width: 520px;
}

.empty-card h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
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
    display: flex;
    flex-wrap: wrap;
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

.address-modal {
    position: fixed;
    inset: 0;
    background: rgba(58, 58, 58, 0.45);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
    padding: 24px;
}

.modal-dialog {
    position: relative;
    background: rgba(253, 251, 247, 0.95);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.2);
    padding: clamp(24px, 4vw, 36px);
    max-width: min(520px, 100%);
    width: 100%;
}

.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    border: none;
    background: rgba(234, 218, 192, 0.6);
    color: #3A3A3A;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
}

.address-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.address-form h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.address-form label {
    font-size: 0.75rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.65);
    font-weight: 600;
}

.address-form select,
.address-form input {
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 12px 14px;
}

@media (max-width: 1024px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        position: static;
    }
}

@media (max-width: 640px) {
    .cart-page {
        padding: 40px 18px 64px;
    }

    .cart-card {
        grid-template-columns: 1fr;
    }

    .cart-media img {
        height: 220px;
    }

    .cart-headline {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
