<template>
    <div class="category-page" :class="{ 'modal-active': showQuickView }">
        <section class="hero-block" v-if="banners.length">
            <img :src="banners[0].image_url" alt="Featured banner">
            <div class="hero-overlay">
                <p class="hero-eyebrow">Curated selections</p>
                <h1>{{ currentCollectionName }}</h1>
                <p>Discover pieces shaped by warm craftsmanship and modern calm.</p>
            </div>
        </section>

        <section class="category-overview" v-if="categories.length">
            <header class="section-header">
                <p class="section-eyebrow">Explore</p>
                <h2>Shop by style family</h2>
            </header>
            <div class="category-grid">
                <article
                    v-for="(image, index) in categories"
                    :key="index"
                    class="category-card"
                    @click="$router.push(`/collection/${image.id}`)"
                >
                    <div class="category-media">
                        <img :src="image.image_url || '/images/no_image.jpg'" :alt="image.name">
                        <div class="category-overlay"></div>
                        <span class="category-name">{{ image.name }}</span>
                    </div>
                </article>
            </div>
        </section>

        <section class="collections-shell">
            <aside class="filters-panel" :class="{ open: filtersVisible }">
                <div class="filters-head">
                    <h3>Filter</h3>
                    <button class="icon-btn" @click="toggleFilters" v-if="!filtersVisibleButton">
                        <font-awesome-icon icon="times-circle" />
                    </button>
                </div>

                <div class="filters-body">
                    <button class="apply-btn" @click="applyFilter">Apply filters</button>

                    <details open>
                        <summary>
                            <span>Price</span>
                            <span class="summary-value">{{ filters.priceRange || 'Any' }}</span>
                        </summary>
                        <div class="filter-content">
                            <div class="range-labels">
                                <span>{{ min_price }}</span>
                                <span>{{ max_price }}</span>
                            </div>
                            <input type="range" class="range-input" :min="min_price" :max="max_price" v-model="filters.priceRange">
                        </div>
                    </details>

                    <details open>
                        <summary>
                            <span>Colours</span>
                            <span class="summary-value">{{ filters.colors.length ? filters.colors.length + ' selected' : 'Any' }}</span>
                        </summary>
                        <div class="filter-list">
                            <label v-for="(color, index) in colors" :key="index">
                                <input type="checkbox" v-model="filters.colors" :value="color">
                                <span>{{ color }}</span>
                            </label>
                        </div>
                    </details>

                    <details open>
                        <summary>
                            <span>Sizes</span>
                            <span class="summary-value">{{ filters.sizes.length ? filters.sizes.length + ' selected' : 'Any' }}</span>
                        </summary>
                        <div class="filter-list">
                            <label v-for="(size, index) in sizes" :key="index">
                                <input type="checkbox" v-model="filters.sizes" :value="size">
                                <span>{{ size }}</span>
                            </label>
                        </div>
                    </details>
                </div>
            </aside>

            <div class="collections-panel">
                <div class="collections-toolbar">
                    <div>
                        <p class="toolbar-eyebrow">New arrivals</p>
                        <h2>Artisanal pieces</h2>
                    </div>
                    <button class="ghost-btn" v-if="!filtersVisibleButton" @click="toggleFilters">
                        <font-awesome-icon icon="bars" /> Filters
                    </button>
                </div>

                <div class="collections-grid">
                    <article v-for="(image, index) in images" :key="index" class="collection-card">
                        <div class="collection-media">
                            <img :src="image.image_url || '/images/no_image.jpg'" :alt="image.name">
                            <div v-if="image.sale_id && image.sale" class="badge-sale">-{{ image.sale.sale_price }}%</div>
                            <div class="media-overlay">
                                <button class="overlay-btn ghost" @click.stop="$router.push(`/product-detail/${image.id}`)">View detail</button>
                                <button class="overlay-btn primary" @click.stop="openModal(image)">Quick look</button>
                            </div>
                        </div>
                        <div class="collection-info">
                            <p class="collection-eyebrow">{{ image.sale_id ? 'Limited release' : 'Artisan crafted' }}</p>
                            <h3>{{ image.name }}</h3>
                            <p class="collection-copy">
                                {{ image.short_description || image.description || defaultDescription }}
                            </p>
                            <div class="price-line" v-if="image.sale_id && image.sale">
                                <span class="price-old">Rs. {{ formatPrice(getBasePrice(image)) }}</span>
                                <span class="price-new">Rs. {{ formatPrice(applySale(getBasePrice(image), image.sale.sale_price)) }}</span>
                            </div>
                            <div class="price-line" v-else-if="getBasePrice(image)">Rs. {{ formatPrice(getBasePrice(image)) }}</div>
                            <div class="price-line" v-else>Price on request</div>
                            <div class="collection-foot">
                                <button class="outline-pill" @click="$router.push(`/product-detail/${image.id}`)">View details</button>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="load-more" v-if="showLoadMoreButton">
                    <button class="outline-btn" @click="loadMoreImages">Show more pieces</button>
                </div>
            </div>
        </section>

        <transition name="modal-fade">
            <div v-if="showQuickView" class="quick-view-shell" @click.self="closeModal">
                <div class="modal-backdrop"></div>
                <div class="modal-dialog" role="dialog" aria-modal="true">
                    <button class="modal-close" @click="closeModal" aria-label="Close quick view">&times;</button>
                    <div class="modal-layout">
                        <div class="modal-gallery">
                            <div class="gallery-thumbs">
                                <button class="thumb-nav" @click="prevSlide" aria-label="Previous">
                                    <font-awesome-icon icon="angle-up" />
                                </button>
                                <div class="thumb-track">
                                    <button
                                        v-for="(image, index) in sideImages"
                                        :key="index"
                                        class="thumb"
                                        :class="{ active: index === currentSlide }"
                                        @click="selectImage(index)"
                                    >
                                        <img :src="image || '/images/no_image.jpg'" :alt="`Preview ${index + 1}`">
                                    </button>
                                </div>
                                <button class="thumb-nav" @click="nextSlide" aria-label="Next">
                                    <font-awesome-icon icon="angle-down" />
                                </button>
                            </div>
                            <div class="gallery-main">
                                <img :src="sideImages[currentSlide] || data.image_url || '/images/no_image.jpg'" :alt="data.name">
                            </div>
                        </div>
                        <div class="modal-details">
                            <div class="modal-headline">
                                <h3>{{ data.name }}</h3>
                                <p class="serial" v-if="data.serial_no">SKU {{ data.serial_no }}</p>
                            </div>
                            <div class="modal-price" v-if="selectedVariant">
                                <span v-if="data.sale_id && data.sale">
                                    <span class="price-old">Rs. {{ formatPrice(selectedVariant.price) }}</span>
                                    <span class="price-new">Rs. {{ formatPrice(applySale(selectedVariant.price, data.sale.sale_price)) }}</span>
                                </span>
                                <span v-else>Rs. {{ formatPrice(selectedVariant.price) }}</span>
                            </div>
                            <div class="modal-option-group" v-if="product_variant.length">
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
                            <div class="modal-option-group" v-if="availableColors.length">
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
                            <div class="modal-option-group quantity">
                                <label>Quantity</label>
                                <div class="quantity-control">
                                    <button @click="decreaseQuantity" :disabled="quantity === 1">-</button>
                                    <span>{{ quantity }}</span>
                                    <button @click="increaseQuantity" :disabled="quantity === 100">+</button>
                                </div>
                            </div>
                            <p class="modal-notice" v-if="data.quantity !== undefined">
                                <span v-if="data.quantity < 1">Out of stock</span>
                                <span v-else-if="data.quantity <= 10">Limited stock available</span>
                                <span v-else>Ready to ship</span>
                            </p>
                            <p class="modal-description">
                                {{ data.description || defaultDescription }}
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
                                <button class="ghost-btn" @click="$router.push(`/product-detail/${data.id}`)">Full details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>


export default {
    name: "CategoryDetail",
    data() {
        return {
            categories: [
                { image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 1' },
                // Add more image objects as needed
            ],
            images: [    ],
            angleIcons: {
                PRICE: "angle-up",
                PRODUCTTYPE: "angle-down",
                COLORS: "angle-up",
                SIZE: "angle-up",
                Featured: "angle-down",
                shippingInformation: "angle-down",
                askAQuestion: "angle-down",
            },// Example initial price range values

            filtersVisible: false,
            filtersVisibleButton: false,
            selectedFeatured: '',
            selectedFruit: '',
            featureList: ['a','b'],
            filters: {
                priceRange: null,
                colors: [],
                sizes: [],
            },
            currentPage: 1,
            imagesPerPage: 3,
            showLoadMoreButton: true,
            quantity: 1,
            data:{
                image_url: '/images/no_image.jpg', alt: 'Image 1', name: 'Image 1',price: 5,quantity:5,serial_no: 5
            },
            sideImages: [
                "/images/no_image.jpg",
                "/images/no_image.jpg",
                "/images/no_image.jpg",
                "/images/no_image.jpg",
            ],
            currentSlide: 0,
            question: {
                name: "",
                email: "",
                phone: "",
                message: "",
            },
            product_variant:[],
            selectedSize: null,
            selectedColor: null,
            max_price:0,
            min_price:0,
            colors: [],
            sizes: [],
            banners: [],
            showQuickView: false,
            defaultDescription: 'An elevated essential crafted with soft blush undertones and warm gold accents to complement contemporary living spaces.',
        }
    },
    computed: {
        currentCollectionName() {
            const id = this.$route.params.id;
            const match = this.categories.find(category => String(category.id) === String(id));
            return match?.name || 'Collection';
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
        availableColors() {
            if (!this.product_variant.length || !this.selectedSize) {
                return [];
            }
            const colors = this.product_variant
                .filter(variant => variant?.size?.name === this.selectedSize)
                .map(variant => variant?.color)
                .filter(Boolean);
            return [...new Set(colors)];
        }
    },
    created() {
        window.addEventListener('resize', this.handleResize);
        this.handleResize(); // Call it once to set the initial state
    },

    beforeUnmount() {
        window.removeEventListener('resize', this.handleResize);
    },
    async mounted() {
        await this.getBanners();
        await this.fetchData(this.$route.params.id);
    },
    methods: {
        async fetchData(id) {
            try {
                await this.getFilterAttributes(id);
                const response = await this.$axios.get(`/api/subCategorylisting/${id}`);
                await this.getProducts(id);

                this.categories = response.data.data;

                this.currentPage = 1;
                this.quantity = 1;

            } catch (e) {
                handleError(e,this.$toast)
            }
        },
        async getProducts(id,fromFilter) {
            try {
                const queryParams = {
                    id,
                    view: this.imagesPerPage,
                    priceRange: this.filters.priceRange,
                    colors: this.filters.colors,
                    sizes: this.filters.sizes,
                    page: this.currentPage,
                };

                const response = await this.$axios.get(`/api/categoryProductListing`, {
                    params: queryParams,
                });
                if (fromFilter === 'fromFilter'){
                    this.images = response.data.data.data;
                }else{
                    this.images = [...this.images, ...response.data.data.data];
                    if (response.data.data.current_page === response.data.data.last_page) {
                        this.showLoadMoreButton = false; // No more images to load, hide the button
                    }
                }


            } catch (e){
                handleError(e,this.$toast)
            }

        },
        async getBanners() {
            try {
                await this.$axios
                    .get('/api/bannerlisting?pageName=Category')
                    .then(response => {
                        this.banners = response.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        async getFilterAttributes(id) {
            try {
                const response = await this.$axios.get(`/api/getFilterAttributes/${id}`);
                this.max_price = response.data.data.priceRange.max_price;
                this.min_price = response.data.data.priceRange.min_price;
                this.colors = response.data.data.colors;
                this.sizes = response.data.data.sizes;
            } catch (e) {
                handleError(e,this.$toast)
            }
        },
        async loadMoreImages() {
            // Increment the current page
            this.currentPage++;

            // Fetch more images from the backend
            await this.getProducts(this.$route.params.id);

            // Scroll to the newly loaded images (optional)
            this.scrollToNewImages();
        },
        scrollToNewImages() {
            // You can use JavaScript to scroll to the newly loaded images for a better user experience
            // For example, you can use the `scrollIntoView` method.
            const lastImageElement = document.querySelector(".collection-card:last-child");
            if (lastImageElement) {
                lastImageElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        },
        toggleCollapse(section) {
            if (this.angleIcons[section] === "angle-down") {
                this.angleIcons[section] = "angle-up";
            } else {
                this.angleIcons[section] = "angle-down";
            }
        },
        toggleFilters() {
            this.filtersVisible = !this.filtersVisible;
        },
        handleResize() {
            // Check the screen width and update the filtersVisible property
            this.filtersVisible = window.innerWidth >= 768;
            this.filtersVisibleButton = window.innerWidth >= 768;
        },
        selectFruit(fruit,name) {
            this.toggleCollapse(name);
            // this.featureList = fruit;
        },
        selectImage(index) {
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
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.sideImages.length) % this.sideImages.length;
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.sideImages.length;
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
        selectSize(item) {
            this.selectedSize = item.size?.name || null;
            const colors = this.availableColors;
            this.selectedColor = colors.length ? colors[0] : null;
        },
        selectColor(item) {
            this.selectedColor = item;
        },
        openModal(product) {
            // Set the current product data for the modal
            this.data = product;
            this.sideImages = Array.isArray(product.side_image_urls) && product.side_image_urls.length
                ? product.side_image_urls
                : [product.image_url || '/images/no_image.jpg'];
            this.product_variant = product.product_variant || [];
            if (this.product_variant.length) {
                this.selectedSize = this.product_variant[0].size?.name || null;
                const colors = this.availableColors;
                this.selectedColor = colors.length ? colors[0] : (this.product_variant[0].color || null);
            } else {
                this.selectedSize = null;
                this.selectedColor = null;
            }
            this.currentSlide = 0;
            this.quantity = 1;
            this.showQuickView = true;
        },
        closeModal() {
            this.showQuickView = false;
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
        applyFilter(){
            this.currentPage = 1;
            this.showLoadMoreButton = true;
            this.getProducts(this.$route.params.id,'fromFilter');
            if (!this.filtersVisibleButton) {
                this.filtersVisible = false;
            }
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
        }
    },

    beforeRouteUpdate(to, from, next) {
        if (to.params.id !== from.params.id) {
            this.showQuickView = false;
            this.images=[];
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

}

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.category-page {
    min-height: 100vh;
    padding: clamp(56px, 8vw, 96px) clamp(24px, 6vw, 96px);
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.2), transparent 55%), linear-gradient(135deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    display: flex;
    flex-direction: column;
    gap: clamp(48px, 6vw, 72px);
}

.category-page.modal-active .hero-block,
.category-page.modal-active .category-overview,
.category-page.modal-active .collections-shell,
.category-page.modal-active .load-more {
    filter: blur(6px);
    pointer-events: none;
    transform: scale(0.99);
    transition: filter 0.3s ease, transform 0.3s ease;
}

.hero-block {
    position: relative;
    border-radius: 36px;
    overflow: hidden;
    min-height: clamp(240px, 40vw, 420px);
    box-shadow: 0 40px 80px rgba(58, 58, 58, 0.2);
}

.hero-block img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.75);
}

.hero-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 14px;
    padding: clamp(32px, 6vw, 80px);
    color: #FDFBF7;
}

.hero-eyebrow {
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
    font-weight: 600;
}

.hero-overlay h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 5vw, 3.4rem);
    margin: 0;
}

.hero-overlay p {
    font-size: 1.05rem;
    max-width: 540px;
    margin: 0;
    line-height: 1.8;
}

.category-overview {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.section-header {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.section-eyebrow {
    font-size: 0.75rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #D4AF37;
    margin: 0;
    font-weight: 600;
}

.section-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3.2vw, 2.6rem);
    margin: 0;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: clamp(20px, 3vw, 28px);
}

.category-card {
    position: relative;
    max-width: 500px;
    border-radius: 26px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    background: rgba(253, 251, 247, 0.9);
    border: 1px solid rgba(212, 175, 55, 0.25);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.14);
}

.category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 36px 80px rgba(58, 58, 58, 0.2);
}

.category-media {
    position: relative;
    aspect-ratio: 3 / 4;
}

.category-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(58, 58, 58, 0.05), rgba(58, 58, 58, 0.65));
}

.category-name {
    position: absolute;
    bottom: 20px;
    left: 20px;
    color: #FDFBF7;
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    letter-spacing: 0.04em;
}

.collections-shell {
    display: grid;
    grid-template-columns: minmax(260px, 300px) 1fr;
    gap: clamp(24px, 4vw, 40px);
}

.filters-panel {
    background: rgba(253, 251, 247, 0.92);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.12);
    padding: 24px;
    position: sticky;
    top: 120px;
    height: max-content;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.filters-panel .filters-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.filters-panel h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
}

.icon-btn {
    border: none;
    background: transparent;
    color: rgba(58, 58, 58, 0.55);
    font-size: 1.4rem;
    cursor: pointer;
}

.filters-body {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.apply-btn {
    border: none;
    border-radius: 999px;
    padding: 12px 22px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.apply-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.filters-panel details {
    border-radius: 18px;
    background: rgba(234, 218, 192, 0.35);
    padding: 14px 18px;
    border: 1px solid rgba(212, 175, 55, 0.22);
}

.filters-panel summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    list-style: none;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.75);
}

.summary-value {
    font-size: 0.75rem;
    color: rgba(58, 58, 58, 0.6);
}

.filter-content {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: rgba(58, 58, 58, 0.6);
}

.range-input {
    width: 100%;
}

.filter-list {
    margin-top: 12px;
    display: grid;
    gap: 10px;
}

.filter-list label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.75);
}

.filter-list input {
    accent-color: #D4AF37;
}

.collections-panel {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.collections-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.toolbar-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #D4AF37;
}

.collections-toolbar h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 3.2vw, 2.6rem);
}

.ghost-btn,
.outline-btn,
.primary-pill {
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.ghost-btn {
    border: 1px solid rgba(58, 58, 58, 0.25);
    background: rgba(253, 251, 247, 0.85);
    padding: 10px 18px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.ghost-btn:hover {
    transform: translateY(-2px);
    border-color: rgba(58, 58, 58, 0.4);
}

.collections-grid {
    display: flex;
    flex-wrap: wrap;
    gap: clamp(20px, 3vw, 28px);
    justify-content: center;
}

.collection-card {
    background: rgba(253, 251, 247, 0.9);
    border-radius: 26px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.14);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
    max-width: 500px;
    margin: 0 auto;
}

.collection-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 36px 84px rgba(58, 58, 58, 0.2);
}

.collection-media {
    position: relative;
    aspect-ratio: 3 / 4;
    cursor: pointer;
    overflow: hidden;
}

.collection-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.collection-card:hover .collection-media img {
    transform: scale(1.05);
}

.media-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px;
    background: linear-gradient(180deg, rgba(58, 58, 58, 0) 30%, rgba(58, 58, 58, 0.65) 100%);
    opacity: 0;
    transition: opacity 0.35s ease;
}

.collection-card:hover .media-overlay {
    opacity: 1;
}

.overlay-btn {
    border-radius: 999px;
    border: none;
    padding: 10px 18px;
    font-size: 0.85rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.overlay-btn.ghost {
    background: rgba(253, 251, 247, 0.85);
    color: #3A3A3A;
}

.overlay-btn.primary {
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    box-shadow: 0 14px 30px rgba(212, 175, 55, 0.25);
}

.badge-sale {
    position: absolute;
    top: 16px;
    left: 16px;
    border-radius: 999px;
    padding: 6px 14px;
    background: rgba(212, 175, 55, 0.92);
    color: #3A3A3A;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
}

.collection-info {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.collection-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.6);
}

.collection-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    margin: 0;
}

.collection-copy {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: rgba(58, 58, 58, 0.7);
}

.price-line {
    display: flex;
    gap: 12px;
    align-items: center;
    margin: 0;
    font-size: 1rem;
}

.price-old {
    text-decoration: line-through;
    color: rgba(58, 58, 58, 0.45);
}

.price-new {
    font-weight: 700;
}

.collection-foot {
    display: flex;
    justify-content: flex-start;
    margin-top: 12px;
}

.outline-pill {
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.5);
    background: transparent;
    color: #3A3A3A;
    padding: 10px 22px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.outline-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
    background: rgba(212, 175, 55, 0.12);
}

.outline-btn {
    background: transparent;
    border: 1px solid rgba(212, 175, 55, 0.5);
    color: #3A3A3A;
    padding: 12px 26px;
}

.outline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(212, 175, 55, 0.25);
    background: rgba(212, 175, 55, 0.14);
}

.load-more {
    display: flex;
    justify-content: center;
}

.quick-view-shell {
    position: fixed;
    inset: 0;
    z-index: 1100;
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
    max-width: min(1080px, 92vw);
    width: 100%;
    padding: clamp(24px, 4vw, 36px);
    z-index: 1;
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
    border: 1px solid rgba(212, 175, 55, 0.28);
    border-radius: 12px;
    background: rgba(253, 251, 247, 0.9);
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
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.6rem, 3vw, 2.2rem);
}

.serial {
    margin: 0;
    color: rgba(58, 58, 58, 0.5);
}

.modal-price {
    display: flex;
    gap: 12px;
    align-items: baseline;
    font-size: 1.1rem;
    font-weight: 600;
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
    padding: 10px 18px;
    background: rgba(253, 251, 247, 0.95);
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
}

.quantity-control button:disabled {
    background: rgba(234, 218, 192, 0.6);
    cursor: not-allowed;
}

.modal-notice {
    font-size: 0.95rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(212, 106, 79, 0.85);
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
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.25);
    padding: 12px 16px;
    background: rgba(253, 251, 247, 0.9);
}

.modal-accordions summary {
    cursor: pointer;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.75);
}

.question-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 12px;
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
    gap: 16px;
    flex-wrap: wrap;
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
    .collections-shell {
        grid-template-columns: 1fr;
    }

    .filters-panel {
        position: fixed;
        inset: 0;
        max-width: 320px;
        transform: translateX(-120%);
        transition: transform 0.35s ease;
        z-index: 1200;
        top: 0;
        height: 100vh;
        border-radius: 0 28px 28px 0;
    }

    .filters-panel.open {
        transform: translateX(0);
    }

    .collections-toolbar {
        justify-content: space-between;
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
    }
}

@media (max-width: 640px) {
    .category-page {
        padding: 48px 18px 72px;
        gap: 40px;
    }

    .hero-overlay h1 {
        font-size: 2.1rem;
    }

    .collections-toolbar h2 {
        font-size: 1.9rem;
    }

    .collection-media {
        aspect-ratio: 1 / 1.2;
    }

    .filters-panel {
        width: 100%;
        max-width: none;
        border-radius: 0;
    }
}
</style>
