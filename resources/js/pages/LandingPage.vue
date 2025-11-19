<template>
    <div class="landing-page" :class="{ 'is-blurred': showQuickViewModal }">
        <div v-if="loading" class="page-loader">
            <Loader />
            </div>
        <div v-else class="landing-body">
            <section class="hero-section">
                <div class="hero-copy">
                    <p class="hero-eyebrow">Artisanal Luxury</p>
                    <h1>Curated essentials for a calm, elevated lifestyle.</h1>
                    <p class="hero-subtitle">
                        Discover modern minimalism warmed by handcrafted finishes, imagined for those who savour slow rituals and mindful design.
                    </p>
                    <div class="hero-actions">
                        <button class="primary-btn" @click="scrollToSection('collection-section')">Explore collection</button>
                        <button class="ghost-btn" @click="scrollToSection('categories-section')">Shop categories</button>
            </div>
        </div>
                <div class="hero-media">
                    <div class="hero-media-frame">
                        <CarouselImage />
            </div>
                </div>
            </section>

            <section class="section categories-section" id="categories-section">
                <div class="section-header">
                    <p class="section-eyebrow">Signature stories</p>
                    <h2>Shop by category</h2>
                    <p class="section-subtitle">Lean into textures and tones crafted to soothe.</p>
                </div>
                <div class="category-grid">
                    <article
                        v-for="category in categories"
                        :key="category.id"
                        class="category-card"
                        @click="$router.push(`/collection/${category.id}`)"
                    >
                        <div class="category-image-wrap">
                            <img :src="category.image_url || placeholderImage" :alt="category.name" loading="lazy">
                            <div class="category-overlay"></div>
                            <span class="category-name">{{ category.name }}</span>
                        </div>
                        <p class="category-description">Hand-selected pieces that echo artisanal calm.</p>
                    </article>
                </div>
            </section>

            <section class="section collection-section" id="collection-section">
                <div class="section-header">
                    <p class="section-eyebrow">Collection {{ currentYear }}</p>
                    <h2>New arrivals</h2>
                    <p class="section-subtitle">Limited editions blending sophistication with softness.</p>
                </div>
                <div class="collection-grid">
                    <article
                        v-for="product in collection"
                        :key="product.id"
                        class="collection-card"
                    >
                        <div class="collection-media" @click="$router.push(`/product-detail/${product.id}`)">
                            <img :src="product.image_url || placeholderImage" :alt="product.name" loading="lazy">
                            <button class="quick-look-btn" @click.stop="openModal(product)">Quick look</button>
                            <div v-if="product.sale_id && product.sale" class="badge-sale">-{{ product.sale.sale_price }}%</div>
            </div>
                        <div class="collection-info">
                            <h3>{{ product.name }}</h3>
                            <p class="collection-price" v-if="getBasePrice(product) !== null">
                                <span v-if="product.sale_id && product.sale">
                                    <span class="price-old">Rs. {{ formatPrice(getBasePrice(product)) }}</span>
                                    <span class="price-new">Rs. {{ formatPrice(applySale(getBasePrice(product), product.sale.sale_price)) }}</span>
                                </span>
                                <span v-else>
                                    Rs. {{ formatPrice(getBasePrice(product)) }}
                                </span>
                            </p>
                            <p class="collection-note" v-else>Artisan pricing revealed inside.</p>
                            <div class="collection-actions">
                                <button class="text-link" @click="$router.push(`/product-detail/${product.id}`)">View details</button>
        </div>
            </div>
                    </article>
                </div>
                <div class="section-actions" v-if="showLoadMoreButton">
                    <button class="outline-btn" @click="loadMoreImages">Show more pieces</button>
                </div>
            </section>

            <section class="section story-section">
                <div class="story-card">
                    <h2>Designed for mindful living</h2>
                    <p>
                        Each piece is thoughtfully composed to balance serene minimal lines with inviting, hand-finished warmth, inviting you to slow down and savour the moment.
                    </p>
                    <div class="story-highlights">
                        <div class="highlight">
                            <span class="highlight-title">Sustainable sources</span>
                            <p>We partner with responsible ateliers that honour ethical craftsmanship.</p>
                        </div>
                        <div class="highlight">
                            <span class="highlight-title">Timeless finishes</span>
                            <p>Understated textures, blush undertones, and matte gold accents reward daily rituals.</p>
                </div>
                        <div class="highlight">
                            <span class="highlight-title">Curated service</span>
                            <p>Our stylists guide you to pieces that complete your personal sanctuary.</p>
            </div>
        </div>
        </div>
            </section>

            <section class="section newsletter-section">
                <div class="newsletter-card">
                    <h2>Join the Atelier Circle</h2>
                    <p>Receive first looks at limited drops, styling notes, and private events.</p>
                    <div class="newsletter-actions">
                        <button class="primary-btn" @click="$router.push({ name: 'register' })">Create account</button>
                        <button class="ghost-btn" @click="$router.push({ name: 'login' })">Sign in</button>
                    </div>
                </div>
            </section>
        </div>

        <transition name="modal-fade">
            <div v-if="showQuickViewModal" class="quick-view-shell" @click.self="closeModal">
                <div class="modal-backdrop"></div>
                <div class="modal-dialog" role="dialog" aria-modal="true">
                    <button class="modal-close" @click="closeModal" aria-label="Close quick view">&times;</button>
                    <div class="modal-layout">
                        <div class="modal-gallery">
                            <div class="gallery-thumbs">
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
                                        <img :src="image || placeholderImage" :alt="`Preview ${index + 1}`">
                                    </button>
                                </div>
                                <button class="thumb-nav" @click="nextSlide" aria-label="Next image">
                                    <font-awesome-icon icon="angle-down" />
                                </button>
                            </div>
                            <div class="gallery-main">
                                <img :src="images[currentSlide] || placeholderImage" :alt="activeProduct.name">
                        </div>
                        </div>
                        <div class="modal-details">
                            <div class="modal-headline">
                                <h3>{{ activeProduct.name }}</h3>
                                <p class="serial" v-if="activeProduct.serial_no">SKU {{ activeProduct.serial_no }}</p>
                    </div>
                            <div class="modal-price" v-if="selectedVariant">
                                <span v-if="activeProduct.sale_id && activeProduct.sale">
                                    <span class="price-old">Rs. {{ formatPrice(selectedVariant.price) }}</span>
                                    <span class="price-new">Rs. {{ formatPrice(applySale(selectedVariant.price, activeProduct.sale.sale_price)) }}</span>
                            </span>
                                <span v-else>Rs. {{ formatPrice(selectedVariant.price) }}</span>
                            </div>
                            <div class="modal-option-group" v-if="uniqueSizes.length">
                                <label>Size</label>
                                <div class="option-grid">
                                    <button
                                        v-for="(size, index) in uniqueSizes"
                                        :key="index"
                                        class="option-chip"
                                        :class="{ active: selectedSize === size }"
                                        @click="selectSizeByName(size)"
                                    >
                                        {{ size }}
                                    </button>
                                    </div>
                                </div>
                            <div class="modal-option-group" v-if="availableColorsForSelectedSize.length">
                                <label>Color</label>
                                <div class="option-grid">
                                    <button
                                        v-for="(color, index) in availableColorsForSelectedSize"
                                        :key="index"
                                        class="option-chip"
                                        :class="{ active: selectedColor === color }"
                                        @click="selectColor(color)"
                                    >
                                        {{ color }}
                                    </button>
                                    </div>
                                </div>
                            <div class="modal-option-group quantity" v-if="selectedVariant">
                                <label>Quantity</label>
                                <div class="quantity-control">
                                    <button @click="decreaseQuantity" :disabled="quantity === 1">-</button>
                                    <span>{{ quantity }}</span>
                                    <button @click="increaseQuantity">+</button>
                            </div>
                                </div>
                            <p class="modal-notice" v-if="activeProduct.quantity !== undefined">
                                <span v-if="activeProduct.quantity < 1">Out of stock</span>
                                <span v-else-if="activeProduct.quantity <= 10">Limited pieces remaining</span>
                                <span v-else>In stock and ready to ship</span>
                            </p>
                            <p class="modal-description">
                                {{ activeProduct.description || defaultDescription }}
                            </p>

                            <div class="modal-accordions">
                                <details>
                                    <summary>Shipping information</summary>
                                    <p>Complimentary delivery nationwide on orders above Rs. 5,000. Standard timelines: 7–8 business days.</p>
                                </details>
                                <details>
                                    <summary>Ask a question</summary>
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

                            <div class="modal-actions">
                                <button class="primary-btn" :disabled="!selectedVariant" @click="addToCart">Add to cart</button>
                                <button class="ghost-btn" @click="$router.push(`/product-detail/${activeProduct.id}`)">Full details</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </transition>
    </div>
</template>

<script>
import CarouselImage from '../components/CarouselImage.vue';

export default {
    name: 'LandingPage',
    components: {
        CarouselImage,
    },
    data() {
        return {
            placeholderImage: '/images/no_image.jpg',
            categories: [],
            collection: [],
            currentYear: new Date().getFullYear(),
            currentPage: 1,
            imagesPerPage: 6,
            showLoadMoreButton: true,
            loading: true,
            activeProduct: {
                image_url: '/images/no_image.jpg',
                name: 'Artisanal Piece',
                serial_no: null,
                quantity: null,
            },
            images: ['/images/no_image.jpg'],
            product_variant: [],
            currentSlide: 0,
            quantity: 1,
            selectedSize: null,
            selectedColor: null,
            question: {
                name: '',
                email: '',
                phone: '',
                message: '',
            },
            showQuickViewModal: false,
            defaultDescription: 'An elevated essential crafted with soft blush undertones and warm gold accents to complement contemporary living spaces.',
        };
    },
    computed: {
        uniqueSizes() {
            const sizes = this.product_variant
                .map(variant => variant?.size?.name)
                .filter(Boolean);
            return [...new Set(sizes)];
        },
        availableColorsForSelectedSize() {
            if (!this.product_variant.length || !this.selectedSize) {
                return [];
            }
            const colors = this.product_variant
                .filter(variant => variant?.size?.name === this.selectedSize)
                .map(variant => variant?.color)
                .filter(Boolean);
            return [...new Set(colors)];
        },
        selectedVariant() {
            if (!this.product_variant.length) {
                return null;
            }

            const matched = this.product_variant.find(
                variant =>
                    (!this.selectedSize || variant?.size?.name === this.selectedSize) &&
                    (!this.selectedColor || variant?.color === this.selectedColor)
            );

            return matched || this.product_variant[0] || null;
        },
    },
    async mounted() {
        try {
            await Promise.all([this.getCategories(), this.getProducts()]);
        } finally {
            this.loading = false;
        }
        window.addEventListener('keydown', this.handleEscape);
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleEscape);
    },
    methods: {
        async getProducts() {
            try {
                const response = await this.$axios.get(`/api/productslisting?page=${this.currentPage}&view=${this.imagesPerPage}`);
                const payload = response?.data?.data;

                if (payload?.data) {
                    this.collection = [...this.collection, ...payload.data];
                    this.showLoadMoreButton = payload.current_page < payload.last_page;
                } else {
                    this.showLoadMoreButton = false;
                }
            } catch (e) {
                this.showLoadMoreButton = false;
                handleError(e, this.$toast);
            }
        },
        async getCategories() {
            try {
                const response = await this.$axios.get('/api/categorylisting?parent=true');
                this.categories = response?.data?.data || [];
            } catch (e) {
                handleError(e, this.$toast);
            }
        },
        async loadMoreImages() {
            this.currentPage += 1;
            await this.getProducts();
            this.scrollToNewImages();
        },
        scrollToNewImages() {
            this.$nextTick(() => {
                const lastCard = document.querySelector('.collection-card:last-of-type');
                if (lastCard) {
                    lastCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        },
        scrollToSection(id) {
            const section = document.getElementById(id);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        openModal(product) {
            this.activeProduct = product;
            const gallery = Array.isArray(product?.side_image_urls) ? product.side_image_urls.filter(Boolean) : [];
            const baseImage = product?.image_url || this.placeholderImage;

            this.images = [baseImage, ...gallery.filter(image => image !== baseImage)];
            if (!this.images.length) {
                this.images = [this.placeholderImage];
            }

            this.product_variant = Array.isArray(product?.product_variant) ? product.product_variant : [];
            const firstVariant = this.product_variant[0] || null;

            if (firstVariant?.size?.name) {
                this.selectedSize = firstVariant.size.name;
            } else {
                this.selectedSize = this.uniqueSizes[0] || null;
            }

            const colors = this.availableColorsForSelectedSize;
            if (colors.length) {
                this.selectedColor = colors[0];
            } else if (firstVariant?.color) {
                this.selectedColor = firstVariant.color;
            } else {
                this.selectedColor = null;
            }

            this.quantity = 1;
            this.currentSlide = 0;
            this.showQuickViewModal = true;
        },
        closeModal() {
            this.showQuickViewModal = false;
        },
        selectImage(index) {
            this.currentSlide = index;
        },
        prevSlide() {
            if (!this.images.length) return;
            this.currentSlide = (this.currentSlide - 1 + this.images.length) % this.images.length;
        },
        nextSlide() {
            if (!this.images.length) return;
            this.currentSlide = (this.currentSlide + 1) % this.images.length;
        },
        increaseQuantity() {
            if (this.quantity < 100) {
                this.quantity += 1;
            }
        },
        decreaseQuantity() {
            if (this.quantity > 1) {
                this.quantity -= 1;
            }
        },
        selectSizeByName(size) {
            this.selectedSize = size;
            const colors = this.availableColorsForSelectedSize;
            this.selectedColor = colors[0] || null;
        },
        selectColor(color) {
            this.selectedColor = color;
        },
        addToCart() {
            if (!this.selectedVariant) {
                this.$toast.error('Please select a size and color to continue.', { position: 'bottom-right', duration: 3000 });
                return;
            }

            const productToAdd = {
                id: this.activeProduct.id,
                serial_no: this.activeProduct.serial_no,
                name: this.activeProduct.name,
                sale: this.activeProduct.sale_id ? this.activeProduct.sale : null,
                size: this.selectedSize,
                color: this.selectedColor,
                price: this.selectedVariant.price,
                quantity: this.quantity,
                image_url: this.activeProduct.image_url,
                variant_id: this.selectedVariant.id,
            };

            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingIndex = cart.findIndex(
                item => item.id === productToAdd.id && item.variant_id === productToAdd.variant_id
            );

            if (existingIndex !== -1) {
                cart[existingIndex].quantity += productToAdd.quantity;
            } else {
                cart.push(productToAdd);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            this.$toast.success('Product added to cart!', { position: 'bottom-right', duration: 3000 });
            this.$router.push('/cart');
        },
        submitQuestion() {
            this.$toast.success('Thank you! Our team will reach out shortly.', {
                position: 'bottom-right',
                duration: 3000,
            });
            this.question = {
                name: '',
                email: '',
                phone: '',
                message: '',
            };
        },
        handleEscape(event) {
            if (event.key === 'Escape' && this.showQuickViewModal) {
                this.closeModal();
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
        },
        getBasePrice(product) {
            if (!product) return null;
            if (product.price !== undefined && product.price !== null) {
                return Number(product.price);
            }
            if (Array.isArray(product.product_variant) && product.product_variant.length) {
                return Number(product.product_variant[0].price);
            }
            return null;
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.landing-page {
    min-height: 100vh;
    width: 100%;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.22), transparent 55%), linear-gradient(145deg, rgba(234, 218, 192, 0.65), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
}

.page-loader {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.landing-body {
    display: flex;
    flex-direction: column;
    gap: 72px;
    padding: 96px clamp(16px, 6vw, 96px) 120px;
    transition: filter 0.3s ease, transform 0.3s ease;
}

.landing-page.is-blurred .landing-body {
    filter: blur(6px);
    transform: scale(0.99);
    transition: filter 0.3s ease, transform 0.3s ease;
    pointer-events: none;
}

.hero-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: clamp(32px, 5vw, 64px);
    align-items: center;
    position: relative;
}

.hero-copy {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 520px;
}

.hero-eyebrow {
    font-size: 0.75rem;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.hero-section h1 {
    font-family: 'Playfair Display', 'Times New Roman', serif;
    font-size: clamp(2.5rem, 4vw, 3.5rem);
    line-height: 1.2;
    margin: 0;
}

.hero-subtitle {
    font-size: 1.05rem;
    line-height: 1.8;
    color: rgba(58, 58, 58, 0.78);
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 16px;
}

.hero-media {
    position: relative;
}

.hero-media-frame {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.28);
    box-shadow: 0 26px 70px rgba(58, 58, 58, 0.18);
    padding: clamp(12px, 2vw, 20px);
    overflow: hidden;
}

.hero-media-frame :deep(.carousel-container) {
    border-radius: 20px;
    overflow: hidden;
}

.hero-media-frame :deep(.carousel__slide) {
    border-radius: 18px;
    overflow: hidden;
}

.hero-media-frame :deep(img) {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

.section {
    display: flex;
    flex-direction: column;
    gap: 36px;
}

.section-header {
    text-align: center;
    max-width: 680px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-eyebrow {
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
}

.section-header h2 {
    font-family: 'Playfair Display', 'Times New Roman', serif;
    font-size: clamp(2rem, 3vw, 2.75rem);
    margin: 0;
}

.section-subtitle {
    font-size: 1rem;
    color: rgba(58, 58, 58, 0.7);
    line-height: 1.6;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 280px));
    gap: 24px;
    justify-content: center;
}

.category-card {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    cursor: pointer;
    background: rgba(253, 251, 247, 0.9);
    border: 1px solid rgba(212, 175, 55, 0.28);
    box-shadow: 0 18px 38px rgba(58, 58, 58, 0.12);
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    display: flex;
    flex-direction: column;
    max-width: 320px;
    margin: 0 auto;
    height: 100%;
}

.category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 26px 60px rgba(58, 58, 58, 0.18);
}

.category-image-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 3 / 4;
    min-height: clamp(220px, 32vw, 280px);
}

.category-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(58, 58, 58, 0) 40%, rgba(58, 58, 58, 0.65) 100%);
}

.category-name {
    position: absolute;
    bottom: 18px;
    left: 20px;
    color: #FDFBF7;
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    letter-spacing: 0.04em;
}

.category-description {
    padding: 20px 24px 24px;
    margin: 0;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.68);
    min-height: 72px;
}

.collection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 300px));
    gap: 24px;
    justify-content: center;
}

.collection-card {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 24px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    box-shadow: 0 20px 48px rgba(58, 58, 58, 0.14);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    max-width: 340px;
    margin: 0 auto;
    height: 100%;
}

.collection-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 28px 64px rgba(58, 58, 58, 0.2);
}

.collection-media {
    position: relative;
    overflow: hidden;
    aspect-ratio: 3 / 4;
    cursor: pointer;
}

.collection-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.collection-media:hover img {
    transform: scale(1.05);
}

.quick-look-btn {
    position: absolute;
    bottom: 18px;
    right: 18px;
    background: rgba(212, 175, 55, 0.92);
    color: #3A3A3A;
    border: none;
    border-radius: 999px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 0.85rem;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity 0.35s ease, transform 0.35s ease;
}

.collection-media:hover .quick-look-btn {
    opacity: 1;
    transform: translateY(0);
}

.badge-sale {
    position: absolute;
    top: 16px;
    left: 16px;
    background: rgba(212, 175, 55, 0.92);
    color: #3A3A3A;
    border-radius: 999px;
    padding: 6px 14px;
    font-weight: 600;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
}

.collection-info {
    padding: 22px 24px 26px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1 1 auto;
}

.collection-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    margin: 0;
}

.collection-price {
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    letter-spacing: 0.04em;
}

.price-old {
    text-decoration: line-through;
    color: rgba(58, 58, 58, 0.45);
}

.price-new {
    color: #3A3A3A;
    font-weight: 700;
}

.collection-note {
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.6);
}

.collection-actions {
    margin-top: auto;
}

.text-link {
    background: none;
    border: none;
    padding: 0;
    color: #3A3A3A;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
}

.text-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -4px;
    width: 100%;
    height: 1px;
    background: rgba(58, 58, 58, 0.35);
    transition: transform 0.3s ease;
    transform-origin: left;
    transform: scaleX(0);
}

.text-link:hover::after {
    transform: scaleX(1);
}

.section-actions {
    display: flex;
    justify-content: center;
}

.story-card {
    background: rgba(253, 251, 247, 0.92);
    border-radius: 32px;
    padding: clamp(32px, 5vw, 64px);
    border: 1px solid rgba(212, 175, 55, 0.22);
    box-shadow: 0 28px 68px rgba(58, 58, 58, 0.16);
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.story-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3vw, 2.6rem);
    margin: 0;
}

.story-card p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: rgba(58, 58, 58, 0.72);
    margin: 0;
}

.story-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}

.highlight {
    background: rgba(234, 218, 192, 0.35);
    border-radius: 18px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.highlight-title {
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.85rem;
    color: rgba(58, 58, 58, 0.85);
}

.newsletter-card {
    max-width: 720px;
    margin: 0 auto;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.22), rgba(253, 251, 247, 0.95));
    border-radius: 32px;
    padding: clamp(32px, 5vw, 56px);
    text-align: center;
    border: 1px solid rgba(212, 175, 55, 0.35);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.18);
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.newsletter-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3vw, 2.6rem);
    margin: 0;
}

.newsletter-card p {
    margin: 0;
    font-size: 1rem;
    color: rgba(58, 58, 58, 0.7);
}

.newsletter-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.primary-btn,
.ghost-btn,
.outline-btn {
    border-radius: 999px;
    padding: 12px 28px;
    font-size: 0.95rem;
    font-weight: 600;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease;
    cursor: pointer;
}

.primary-btn {
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    border: none;
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 48px rgba(212, 175, 55, 0.35);
}

.ghost-btn {
    background: transparent;
    border: 1px solid rgba(58, 58, 58, 0.28);
    color: rgba(58, 58, 58, 0.8);
}

.ghost-btn:hover {
    border-color: rgba(58, 58, 58, 0.4);
    transform: translateY(-2px);
}

.outline-btn {
    background: transparent;
    border: 1px solid rgba(212, 175, 55, 0.5);
    color: #3A3A3A;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.12);
}

.quick-view-shell {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(58, 58, 58, 0.45);
    backdrop-filter: blur(6px);
    pointer-events: none;
}

.modal-dialog {
    position: relative;
    background: rgba(253, 251, 247, 0.95);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    box-shadow: 0 38px 80px rgba(58, 58, 58, 0.22);
    max-width: min(1080px, 90%);
    width: 100%;
    padding: clamp(24px, 4vw, 36px);
    z-index: 1;
    pointer-events: auto;
}

.modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    border: none;
    background: rgba(234, 218, 192, 0.6);
    color: #3A3A3A;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-layout {
    display: grid;
    grid-template-columns: minmax(280px, 1fr) minmax(320px, 1fr);
    gap: clamp(24px, 4vw, 40px);
}

.modal-gallery {
    display: grid;
    grid-template-columns: 80px auto;
    gap: 16px;
}

.gallery-thumbs {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

.thumb-nav {
    background: rgba(253, 251, 247, 0.9);
    border: 1px solid rgba(212, 175, 55, 0.28);
    border-radius: 12px;
    padding: 8px;
    cursor: pointer;
    color: #3A3A3A;
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
    max-height: 360px;
    padding-right: 4px;
}

.thumb {
    border: none;
    padding: 0;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.3s ease;
    width: 64px;
    height: 64px;
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumb.active {
    border-color: rgba(212, 175, 55, 0.65);
}

.gallery-main {
    border-radius: 22px;
    overflow: hidden;
    background: rgba(234, 218, 192, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-details {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.modal-headline h3 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.6rem, 2.8vw, 2.2rem);
    margin: 0;
}

.serial {
    color: rgba(58, 58, 58, 0.5);
    font-size: 0.9rem;
    margin: 6px 0 0;
}

.modal-price {
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    gap: 12px;
    align-items: baseline;
}

.modal-option-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal-option-group label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: rgba(58, 58, 58, 0.65);
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
    background: rgba(253, 251, 247, 0.95);
    padding: 10px 18px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

.option-chip.active {
    background: linear-gradient(120deg, rgba(212, 175, 55, 0.85), rgba(234, 218, 192, 0.95));
    border-color: rgba(212, 175, 55, 0.7);
    color: #3A3A3A;
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(212, 175, 55, 0.25);
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 1.1rem;
}

.quantity-control button {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    background: rgba(212, 175, 55, 0.85);
    color: #3A3A3A;
    cursor: pointer;
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.quantity-control button:disabled {
    background: rgba(234, 218, 192, 0.6);
    cursor: not-allowed;
}

.quantity-control button:not(:disabled):hover {
    transform: translateY(-2px);
}

.modal-notice {
    font-size: 0.95rem;
    color: rgba(212, 106, 79, 0.85);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.modal-description {
    font-size: 0.98rem;
    color: rgba(58, 58, 58, 0.72);
    line-height: 1.7;
}

.modal-accordions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal-accordions details {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 12px 16px;
}

.modal-accordions summary {
    font-weight: 600;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(58, 58, 58, 0.75);
}

.modal-accordions p {
    margin: 12px 0 0;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.65);
}

.question-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.question-form input,
.question-form textarea {
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    padding: 12px 14px;
    background: rgba(253, 251, 247, 0.95);
    font-size: 0.95rem;
    color: #3A3A3A;
}

.question-form textarea {
    resize: vertical;
    min-height: 100px;
}

.form-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.modal-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 18px;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

@media (max-width: 1024px) {
    .landing-body {
        gap: 60px;
        padding: 80px 32px 96px;
    }

    .modal-layout {
        grid-template-columns: 1fr;
    }

    .modal-gallery {
        grid-template-columns: 1fr;
    }

    .gallery-thumbs {
        flex-direction: row;
    }

    .thumb-track {
        flex-direction: row;
        max-height: none;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .thumb {
        width: 60px;
        height: 60px;
    }
}

@media (max-width: 768px) {
    .landing-body {
        padding: 72px 20px 88px;
        gap: 48px;
    }

    .hero-section {
        gap: 40px;
    }

    .category-grid,
    .collection-grid {
        gap: 20px;
    }

    .newsletter-actions {
        flex-direction: column;
    }

    .hero-actions {
        justify-content: center;
    }

    .story-highlights {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .hero-section h1 {
        font-size: 2.1rem;
    }

    .hero-subtitle {
        font-size: 0.98rem;
    }

    .collection-media {
        aspect-ratio: 1 / 1.2;
    }

    .modal-dialog {
        padding: 24px 18px;
    }
}
</style>
