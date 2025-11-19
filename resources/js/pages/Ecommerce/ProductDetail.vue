<template>
    <div class="product-page">
        <div class="product-hero">
            <div class="gallery">
                <div class="thumb-column">
                    <button class="thumb-nav" @click="prevSlide" aria-label="Previous image">
                        <font-awesome-icon icon="angle-up" />
                    </button>
                    <div class="thumb-track">
                        <button
                            v-for="(image, index) in images"
                            :key="index"
                            class="thumb"
                            :class="{ active: index === currentSlide }"
                            @click="selectImage(index)"
                        >
                            <img :src="image" :alt="`Preview ${index + 1}`">
                        </button>
                    </div>
                    <button class="thumb-nav" @click="nextSlide" aria-label="Next image">
                        <font-awesome-icon icon="angle-down" />
                    </button>
                </div>
                <div class="main-view">
                    <vue-magnifier :src="images[currentSlide]" :width="580" :zoom="2" />
                </div>
            </div>

            <div class="product-summary">
                <p class="product-eyebrow">Signature piece</p>
                <h1>{{ data.name }}</h1>
                <p class="product-serial" v-if="data.serial_no">SKU {{ data.serial_no }}</p>

                <div class="price-block">
                            <span v-if="data.sale && selectedVariant">
                        <span class="price-old">Rs. {{ selectedVariant.price.toFixed(2) }}</span>
                        <span class="price-new">Rs. {{ (selectedVariant.price - (selectedVariant.price / 100) * data.sale.sale_price).toFixed(2) }}</span>
                        <span class="badge-sale">-{{ data.sale.sale_price }}%</span>
                            </span>
                    <span v-else-if="selectedVariant" class="price-new">
                                    Rs. {{ selectedVariant.price.toFixed(2) }}
                                </span>
                    <span v-else class="price-prompt">Select a size and colour to reveal price</span>
                    </div>

                <p class="product-description">
                    {{ data.description || defaultDescription }}
                </p>

                <div class="option-group" v-if="product_variant.length">
                        <label>Size</label>
                    <div class="option-grid">
                        <button
                                    v-for="(item, index) in product_variant"
                                    :key="index"
                            class="option-chip"
                            :class="{ active: selectedSize === item.size.name }"
                                    @click="selectSize(item)"
                                >
                                    {{ item.size.name }}
                        </button>
                    </div>
                </div>

                <div class="option-group" v-if="availableColors.length">
                    <label>Colour</label>
                    <div class="option-grid">
                        <button
                                v-for="(color, index) in availableColors"
                                :key="index"
                            class="option-chip"
                            :class="{ active: selectedColor === color }"
                                @click="selectColor(color)"
                            >
                                {{ color }}
                        </button>
                    </div>
                        </div>

                <div class="option-group quantity">
                    <label>Quantity</label>
                    <div class="quantity-control">
                        <button @click="decreaseQuantity" :disabled="quantity === 1">-</button>
                        <span>{{ quantity }}</span>
                        <button @click="increaseQuantity" :disabled="quantity === 100">+</button>
                    </div>
                    <p class="stock-note" v-if="data.quantity !== undefined">
                        <span v-if="data.quantity < 1">Out of stock</span>
                        <span v-else-if="data.quantity <= 10">Limited stock available</span>
                        <span v-else>Ready to ship</span>
                        </p>
                    </div>

                <div class="cta-group">
                    <button class="primary-btn" :disabled="!selectedVariant" @click="addToCart">
                        Add to cart
                    </button>
                    <button class="ghost-btn" @click="$router.push('/collection/' + (data.category_id || ''))">
                        Continue exploring
                    </button>
                                    </div>

                <div class="info-accordion">
                    <details>
                        <summary>
                            <span>Shipping information</span>
                            <font-awesome-icon :icon="angleIcons.shippingInformation" />
                        </summary>
                        <p>Complimentary delivery nationwide on orders above Rs. 5,000. Standard timelines: 7–8 working days.</p>
                    </details>
                    <details>
                        <summary>
                            <span>Ask a question</span>
                            <font-awesome-icon :icon="angleIcons.askAQuestion" />
                        </summary>
                        <form @submit.prevent="submitQuestion" class="question-form">
                            <div class="form-row">
                                <input type="text" v-model="question.name" placeholder="Name" required>
                                <input type="email" v-model="question.email" placeholder="Email" required>
                            </div>
                            <input type="tel" v-model="question.phone" placeholder="Phone" required>
                            <textarea v-model="question.message" placeholder="How can we help?" required></textarea>
                            <button type="submit" class="outline-btn">Send message</button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
        <div class="slider " v-if="reviewData.length > 0">
            <transition name="fade" mode="out-in">
                <div :key="currentReviewIndex" class="review-slide">
                    <div class="review-box">
                        <button class="prev-button" @click="prevReview">
                            <font-awesome-icon icon="chevron-left" />
                        </button>
                        <div class="review-content">
                            <div class="star-rating" :key="currentReview.stars">
                                <template v-for="n in currentReview.stars">
                                    <font-awesome-icon icon="star" />
                                </template>
                                <template v-for="n in 5 - currentReview.stars">
                                    <font-awesome-icon icon="star" :class="'empty-star'" />
                                </template>
                            </div>
                            <div>{{ currentReview.description }}</div>
                            <div class="review-author">- {{ currentReview.user.name }}</div>
                        </div>
                        <button class="next-button" @click="nextReview">
                            <font-awesome-icon icon="chevron-right" />
                        </button>
                    </div>
                </div>
            </transition>
        </div>
        <div v-else class="center-message mt-5">
            <p>No reviews available.</p>
        </div>
        <div class="mt-5 mb-2">
            <hr/>
        </div>
        <section class="recently-viewed" v-if="recentlyViewedImages.length">
            <h2>Curated for you</h2>
            <div class="recent-grid">
                <article
                    class="recent-card"
                    v-for="(image, index) in recentlyViewedImages"
                    :key="index"
                    @click="$router.push(`/product-detail/${image.id}`)"
                >
                    <img :src="image.image_url || '/images/no_image.jpg'" :alt="image.name">
                    <div class="recent-info">
                        <p>{{ image.name }}</p>
                    </div>
                </article>
            </div>
        </section>

        <div class="mt-5 mb-2">
            <hr/>
        </div>

    </div>
</template>

<script>
import VueMagnifier from '@websitebeaver/vue-magnifier'
import '@websitebeaver/vue-magnifier/styles.css'
export default {
    name: "ProductDetail",
    components: {
        VueMagnifier,
    },
    data() {
        return {
            recentlyViewedImages: [
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 1' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 2' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 3' },
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 4' },
            ],
            currentSlide: 0,
            quantity: 1,
            stock: 0,
            angleIcons: {
                shippingInformation: "angle-down",
                askAQuestion: "angle-down",
            },
            question: {
                name: "",
                email: "",
                phone: "",
                message: "",
            },
            reviewData: [],
            currentReviewIndex: 0,
            data:{},
            images:[],
            product_variant:[],
            selectedSize: null,
            selectedColor: null,
            defaultDescription: 'Crafted with warm gold accents and soft blush undertones, this piece elevates everyday rituals with artisanal calm.',
        };
    },
    async mounted() {
        scrollToTop();
       await this.fetchData(this.$route.params.id);
       await this.fetchReviewData(this.$route.params.id);
       await this.fetchHotSellingPrducts();
        if (this.product_variant.length > 0) {
            // Select the first size and color from the variants
            this.selectedSize = this.product_variant[0].size.name;
            this.selectedColor = this.product_variant[0].color;
        }
    },
    beforeRouteUpdate(to, from, next) {
        if (to.params.id !== from.params.id) {
            this.fetchData(to.params.id)
                .then(() => next())
                .catch(e => {
                    handleError(e,this.$toast)
                    next(false);
                });
        } else {
            next();
        }
    },
    computed: {
        currentReview() {
            return this.reviewData[this.currentReviewIndex];
        },
        selectedVariant() {
            // Find the selected variant based on the selected color and size
            return this.product_variant.find(
                (variant) =>
                    variant.size?.name === this.selectedSize &&
                    variant.color === this.selectedColor
            );
        },
        availableColors() {
            if (!this.selectedSize) {
                // If no size is selected, return an empty array
                return [];
            }

            // Filter product variants by selected size
            const filteredVariants = this.product_variant.filter(
                (variant) => variant.size?.name === this.selectedSize
            );

            // Extract unique colors from filtered variants
            const uniqueColors = [...new Set(filteredVariants.map((variant) => variant.color))];

            return uniqueColors;
        },
    },
    methods: {
        selectSize(item) {
            this.selectedSize = item.size.name;
            const colors = this.availableColors;
            this.selectedColor = colors.length ? colors[0] : null;
        },
        selectColor(item) {
            this.selectedColor = item;
        },
        async fetchData(id) {
            scrollToTop();
            try {
                await this.$axios
                    .get(`/api/productDetails/${id}`)
                    .then(response => {
                        this.data = response.data.data;
                        const gallery = response.data.data.side_image_urls || [];
                        const mainImage = response.data.data.image_url;
                        const merged = mainImage ? [mainImage, ...gallery] : gallery;
                        this.images = merged.length ? merged : ['/images/no_image.jpg'];
                        this.product_variant = response.data.data.product_variant;
                        if (this.product_variant.length) {
                            this.selectedSize = this.product_variant[0].size?.name || null;
                            const colors = this.availableColors;
                            this.selectedColor = colors.length ? colors[0] : (this.product_variant[0].color || null);
                        } else {
                            this.selectedSize = null;
                            this.selectedColor = null;
                        }
                        this.currentSlide = 0;
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async fetchReviewData(id) {
            try {
                await this.$axios
                    .get(`/api/review?productId=${id}`)
                    .then(response => {
                        this.reviewData = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        prevReview() {
            if (!this.reviewData.length) return;
            this.currentReviewIndex = (this.currentReviewIndex - 1 + this.reviewData.length) % this.reviewData.length;
        },
        nextReview() {
            if (!this.reviewData.length) return;
            this.currentReviewIndex = (this.currentReviewIndex + 1) % this.reviewData.length;
        },
        selectImage(index) {
            if (!this.images.length) return;
            this.currentSlide = index;
        },
        increaseQuantity() {
            if (this.quantity < 100) {
                this.quantity++;
            }
        },
        decreaseQuantity() {
            if (this.quantity > 1) {
                this.quantity--;
            }
        },
        toggleCollapse(section) {
            if (this.angleIcons[section] === "angle-down") {
                this.angleIcons[section] = "angle-up";
            } else {
                this.angleIcons[section] = "angle-down";
            }
        },
        prevSlide() {
            if (!this.images.length) return;
            this.currentSlide = (this.currentSlide - 1 + this.images.length) % this.images.length;
        },
        nextSlide() {
            if (!this.images.length) return;
            this.currentSlide = (this.currentSlide + 1) % this.images.length;
        },
        addToCart() {
            if (!this.selectedVariant) {
                this.$toast.error('Please select a size and colour to continue.', { position: 'bottom-right', duration: 3000 });
                return;
            }

            const productToAdd = {
                id: this.data.id, // Use a unique identifier for the product
                serial_no: this.data.serial_no, // Use a unique identifier for the product
                name: this.data.name,
                sale: this.data.sale_id ? this.data.sale : null,
                size: this.selectedSize,
                color: this.selectedColor,
                price: this.selectedVariant.price,
                quantity: this.quantity,
                image_url: this.data.image_url,
                variant_id: this.selectedVariant.id,
                // Add any other relevant product details here
            };

            // Retrieve the current cart data from local storage or create an empty array if it doesn't exist
            let cart = JSON.parse(localStorage.getItem("cart")) || [];

            // Check if the product with the same id already exists in the cart
            const existingProductIndex = cart.findIndex((item) => item.id === productToAdd.id && item.variant_id === productToAdd.variant_id);

            if (existingProductIndex !== -1) {
                // If the product exists in the cart, update its quantity
                cart[existingProductIndex].quantity += productToAdd.quantity;
            } else {
                // If the product is not in the cart, add it
                cart.push(productToAdd);
            }

            // Store the updated cart data in local storage
            localStorage.setItem("cart", JSON.stringify(cart));

            // Optionally, you can display a confirmation message to the user
            this.$toast.success("Product added to cart!", { position: 'bottom-right', duration: 3000 });

            // You can also redirect the user to the cart page or perform any other desired action
            this.$router.push("/cart");
        },

        submitQuestion() {
            this.$toast.success('Thank you! Our team will be in touch shortly.', {
                position: 'bottom-right',
                duration: 3000,
            });
            this.question = {
                name: "",
                email: "",
                phone: "",
                message: "",
            };
        },

        async fetchHotSellingPrducts() {
            try {
                await this.$axios
                    .get(`/api/hot-selling-products`)
                    .then(response => {
                        this.recentlyViewedImages = response.data.data
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
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.product-page {
    min-height: 100vh;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 55%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    padding: clamp(48px, 6vw, 80px) clamp(24px, 6vw, 96px) clamp(72px, 8vw, 120px);
    display: flex;
    flex-direction: column;
    gap: clamp(48px, 6vw, 72px);
}

.product-hero {
    display: grid;
    grid-template-columns: minmax(280px, 580px) minmax(320px, 1fr);
    gap: clamp(32px, 4vw, 56px);
    align-items: start;
}

.gallery {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 16px;
    background: rgba(253, 251, 247, 0.92);
    border-radius: 32px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: clamp(18px, 3vw, 28px);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
}

.thumb-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.thumb-nav {
    border: 1px solid rgba(212, 175, 55, 0.28);
    border-radius: 12px;
    padding: 8px;
    background: rgba(253, 251, 247, 0.92);
    color: #3A3A3A;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.thumb-nav:hover {
    transform: translateY(-2px);
}

.thumb-track {
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    max-height: clamp(320px, 40vw, 480px);
}

.thumb {
    border: none;
    padding: 0;
    width: 62px;
    height: 62px;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.3s ease, transform 0.3s ease;
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumb.active {
    border-color: rgba(212, 175, 55, 0.7);
    transform: translateY(-2px);
}

.main-view {
    border-radius: 24px;
    overflow: hidden;
    background: rgba(234, 218, 192, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(12px, 2vw, 20px);
}

.product-summary {
    background: rgba(253, 251, 247, 0.92);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 32px;
    padding: clamp(24px, 4vw, 48px);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.product-summary h1 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.2rem, 4vw, 3.1rem);
}

.product-serial {
    margin: 0;
    color: rgba(58, 58, 58, 0.55);
    letter-spacing: 0.08em;
}

.price-block {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.2rem;
    font-weight: 600;
}

.price-old {
    text-decoration: line-through;
    color: rgba(58, 58, 58, 0.45);
}

.price-new {
    color: #3A3A3A;
}

.price-prompt {
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.6);
    font-weight: 500;
}

.badge-sale {
    background: rgba(212, 175, 55, 0.92);
    color: #3A3A3A;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.product-description {
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(58, 58, 58, 0.72);
    margin: 0;
}

.option-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.option-group label {
    font-size: 0.85rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.68);
    font-weight: 600;
}

.option-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.option-chip {
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    padding: 10px 18px;
    background: rgba(253, 251, 247, 0.95);
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

.option-chip.active {
    background: linear-gradient(120deg, rgba(212, 175, 55, 0.85), rgba(234, 218, 192, 0.95));
    border-color: rgba(212, 175, 55, 0.65);
    color: #3A3A3A;
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(212, 175, 55, 0.24);
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 1.1rem;
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

.stock-note {
    margin: 4px 0 0;
    font-size: 0.85rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(212, 106, 79, 0.8);
}

.cta-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.primary-btn,
.ghost-btn,
.outline-btn {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.primary-btn {
    border: none;
    padding: 14px 22px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.ghost-btn {
    border: 1px solid rgba(58, 58, 58, 0.28);
    background: rgba(253, 251, 247, 0.85);
    color: rgba(58, 58, 58, 0.75);
    padding: 14px 22px;
}

.primary-btn:hover:not(:disabled),
.ghost-btn:hover {
    transform: translateY(-2px);
}

.info-accordion {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-accordion details {
    border-radius: 18px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    background: rgba(253, 251, 247, 0.9);
    padding: 12px 16px;
}

.info-accordion summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.7);
}

.question-form {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.question-form input,
.question-form textarea {
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    padding: 12px 14px;
    background: rgba(253, 251, 247, 0.95);
    font-size: 0.95rem;
    color: #3A3A3A;
}

.question-form textarea {
    resize: vertical;
    min-height: 120px;
}

.form-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.outline-btn {
    border: 1px solid rgba(212, 175, 55, 0.5);
    padding: 12px 24px;
    color: #3A3A3A;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.16);
}

.slider {
    display: flex;
    justify-content: center;
}

.review-box {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 18px;
    background: rgba(253, 251, 247, 0.9);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: clamp(18px, 3vw, 28px);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.12);
    max-width: 640px;
    width: 100%;
}

.prev-button,
.next-button {
    border: none;
    background: transparent;
    color: rgba(58, 58, 58, 0.6);
    font-size: 1.2rem;
    cursor: pointer;
}

.review-content {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: rgba(58, 58, 58, 0.72);
}

.star-rating {
    color: #D4AF37;
    display: flex;
    justify-content: center;
    gap: 6px;
}

.star-rating .empty-star {
    color: rgba(58, 58, 58, 0.25);
}

.review-author {
    letter-spacing: 0.14em;
    text-transform: uppercase;
    font-weight: 600;
    color: rgba(58, 58, 58, 0.6);
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
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 1024px) {
    .product-hero {
        grid-template-columns: 1fr;
    }

    .gallery {
        grid-template-columns: 70px 1fr;
    }
}

@media (max-width: 640px) {
    .product-page {
        padding: 40px 18px 64px;
        gap: 40px;
    }

    .gallery {
        grid-template-columns: 1fr;
    }

    .thumb-column {
        flex-direction: row;
    }

    .thumb-track {
        flex-direction: row;
        max-height: none;
    }

    .thumb {
        width: 58px;
        height: 58px;
    }
}
</style>
