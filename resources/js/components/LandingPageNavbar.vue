<template>
<div>
    <header class="atelier-header">
        <div class="header-shell">
            <div class="header-top">
                <button class="mobile-menu-trigger" type="button">
                    <span></span>
                </button>
                <div class="brand">
                    <span class="brand-mark" @click="$router.push('/')">S</span>
                    <div class="brand-copy">
                        <button class="brand-name" @click="$router.push('/')">{{ Title }}</button>
                        <p class="brand-tagline">Curated calm & crafted luxury</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button @click="openSearchPopup" class="action-btn" aria-label="Search">
                        <font-awesome-icon icon="search" fixed-width />
                    </button>
                    <button @click="$router.push({ name: 'cart' })" class="action-btn" aria-label="Cart">
                        <font-awesome-icon icon="cart-shopping" fixed-width />
                    </button>
                    <button
                        v-if="authStore.user && $route.name === 'userDashboard'"
                        class="action-btn"
                        aria-label="Logout"
                        @click="logout"
                    >
                        <font-awesome-icon icon="sign-out" fixed-width />
                    </button>
                    <button
                        v-else-if="authStore.user"
                        class="action-btn"
                        aria-label="Dashboard"
                        @click="$router.push({ name: 'userDashboard' })"
                    >
                        <font-awesome-icon icon="user" fixed-width />
                    </button>
                    <button
                        v-else
                        class="action-btn"
                        aria-label="Login"
                        @click="$router.push({ name: 'login' })"
                    >
                        <font-awesome-icon icon="sign-in" fixed-width />
                    </button>
                </div>
            </div>
            <div class="header-bottom">
                <div class="menu-overlay"></div>
                <nav class="menu">
                    <div class="mobile-menu-head">
                        <button class="go-back action-btn" aria-label="Go back">
                            <font-awesome-icon icon="arrow-left" fixed-width />
                        </button>
                        <div class="current-menu-title"></div>
                        <button class="mobile-menu-close action-btn" aria-label="Close menu">&times;</button>
                    </div>
                    <ul class="menu-main">
                        <li class="menu-item-has-children" v-for="(item,index) in categories" :key="item.id">
                            <button class="menu-link" type="button" @click="handleCategoryClick(item)">
                                {{ item.name }}
                                <font-awesome-icon icon="angle-down" fixed-width />
                            </button>
                            <div class="sub-menu" v-if="item.subCategories.length > 0">
                                <button
                                    v-for="(subItem,subIndex) in item.subCategories"
                                    :key="subItem.id"
                                    class="sub-link"
                                    type="button"
                                    @click="$router.push(`/collection/${subItem.id}`)"
                                >
                                    {{ subItem.name }}
                                </button>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <!-- Search popup -->
    <div v-if="isSearchPopupOpen" class="search-popup">
        <div class="search-popup-content">
            <div class="input-container ">
                <font-awesome-icon icon="search" fixed-width />
                <input
                    class="search-input"
                    v-model="searchInput"
                    @input="performSearch"
                    placeholder="Enter your search query..."
                />
                <i class="close-icon" @click="closeSearchPopup">X</i>
            </div>
            <div class="search-results">
                <div class="search-result-grid">
                    <div class="search-result-item" v-for="(result, index) in searchResults" :key="index">
                        <img :src="result.image_url" alt="Search Result" @click="changeRoute(result.id)" class="cur-point" />
                        <p class="image-name">{{ result.name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
</template>

<script>
export default {
    inject: ['authStore'],
    name: "LandingPageNavbar",
    data(){
        return {
            categories: [],
            isMobileMenuOpen: false,
            isSearchPopupOpen: false,
            searchInput: "",
            searchResults: [],
            currentPage:1,
            imagesPerPage:6,
            Title: ''
        }
    },
    created() {
        this.Title = Title;

        if (this.authStore.user === null) {
            this.$router.push('/')
        }
    },
    async mounted() {

        try {
            await this.$axios
                .get('/api/categorylisting')
                .then(response => {
                    this.categories = response.data.data
                })
        } catch (e) {
            handleError(e,this.$toast);
        }

        const menu = document.querySelector(".menu");
        const menuMain = menu.querySelector(".menu-main");
        const goBack = menu.querySelector(".go-back");
        const menuTrigger = document.querySelector(".mobile-menu-trigger");
        const closeMenu = menu.querySelector(".mobile-menu-close");
        let subMenu;

        menuMain.addEventListener("click", (e) =>{
            if(!menu.classList.contains("active")){
                return;
            }
            if(e.target.closest(".menu-item-has-children")){
                const hasChildren = e.target.closest(".menu-item-has-children");
                showSubMenu(hasChildren);
            }
        });
        goBack.addEventListener("click",() =>{
            hideSubMenu();
        })
        menuTrigger.addEventListener("click",() =>{
            toggleMenu();
        })
        closeMenu.addEventListener("click",() =>{
            toggleMenu();
        })
        document.querySelector(".menu-overlay").addEventListener("click",() =>{
            toggleMenu();
        })
        function toggleMenu(){
            menu.classList.toggle("active");
            document.querySelector(".menu-overlay").classList.toggle("active");
        }
        function showSubMenu(hasChildren){
             subMenu = hasChildren.querySelector(".sub-menu");
            if (subMenu) {
                subMenu.classList.add("active");
                subMenu.style.animation = "slideLeft 0.5s ease forwards";
                const menuTitle = hasChildren.querySelector("i").parentNode.childNodes[0].textContent;
                menu.querySelector(".current-menu-title").innerHTML = menuTitle;
                menu.querySelector(".mobile-menu-head").classList.add("active");
            }
        }
        function  hideSubMenu(){
            subMenu.style.animation = "slideRight 0.5s ease forwards";
            setTimeout(() =>{
                subMenu.classList.remove("active");
            },300);
            menu.querySelector(".current-menu-title").innerHTML="";
            menu.querySelector(".mobile-menu-head").classList.remove("active");
        }
        window.onresize = function(){
            if(this.innerWidth >991){
                if(menu.classList.contains("active")){
                    toggleMenu();
                }

            }
        }
        this.$router.beforeEach((to, from, next) => {
            // Check if the new route is a collection page
            if (to.name === "collection") {
                // Close the mobile menu
                document.querySelector(".menu").classList.remove("active");
                document.querySelector(".menu-overlay").classList.remove("active");

            }
            this.closeSearchPopup();
            // Continue with the route change
            next();
        });
    },
    methods: {
        changeRoute(id) {
            this.closeSearchPopup();
            this.$router.push(`/product-detail/${id}`)
        },
        // Function to handle search
        async performSearch() {

            try {
                await this.$axios
                    .get(`/api/productslisting?page=${this.currentPage}&view=${this.imagesPerPage}&search=${this.searchInput}`)
                    .then(response => {
                        this.searchResults = response.data.data.data
                    })
            } catch (e) {
                handleError(e,this.$toast);
            }
        },
        openSearchPopup() {
            this.isSearchPopupOpen = true;
        },
        closeSearchPopup() {
            this.isSearchPopupOpen = false;
            this.searchInput = ""; // Clear search input when closing the popup
            this.searchResults = []; // Clear search results when closing the popup
        },
        handleError(e,toast) {
            if (e.response && e.response.status === 429) {
                toast.error('Too many login attempts. Please try again later.', { position: 'bottom-right', duration: 3000 });
            } else if (e.response && e.response.status === 422) {
                toast.error(e.response.data.message, { position: 'bottom-right', duration: 3000 });
            } else if (e.response && e.response.status === 403) {
                toast.error(e.response.data.message, { position: 'bottom-right', duration: 3000 });
            } else {
                toast.error('An error occurred. Please try again later.', { position: 'bottom-right', duration: 3000 });
            }
        },
        async logout() {
            localStorage.removeItem('cart');
            localStorage.removeItem('productDetails');
            await this.LogoutApiCall();
            this.authStore.logout()
            await this.$router.push('/')

        },
        async LogoutApiCall() {
            try {
                const response = await this.$axios.post('/api/logout');
                return response.data;
            } catch (e) {
                throw e;
            }
        },
        handleCategoryClick(item) {
            const hasChildren = Array.isArray(item.subCategories) && item.subCategories.length > 0;
            if (window.innerWidth <= 991 && hasChildren) {
                return;
            }
            this.$router.push(`/collection/${item.id}`);
        },
    },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.atelier-header {
    background: linear-gradient(135deg, rgba(234, 218, 192, 0.65), rgba(253, 251, 247, 0.98));
    border-bottom: 1px solid rgba(212, 175, 55, 0.25);
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(12px);
}

.header-shell {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: clamp(18px, 3vw, 26px) clamp(20px, 5vw, 64px);
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    color: #3A3A3A;
}

.header-top {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 18px;
    align-items: center;
}

.mobile-menu-trigger {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.mobile-menu-trigger span,
.mobile-menu-trigger span::before,
.mobile-menu-trigger span::after {
    display: block;
    width: 18px;
    height: 2px;
    background: #3A3A3A;
    position: relative;
    content: '';
}

.mobile-menu-trigger span::before,
.mobile-menu-trigger span::after {
    position: absolute;
    left: 0;
}

.mobile-menu-trigger span::before {
    top: -6px;
}

.mobile-menu-trigger span::after {
    top: 6px;
}

.mobile-menu-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(212, 175, 55, 0.25);
}

.brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.brand-mark {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
}

.brand-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.brand-name {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.4rem, 3vw, 1.9rem);
    letter-spacing: 0.05em;
    border: none;
    background: none;
    color: #3A3A3A;
    text-align: left;
    padding: 0;
    cursor: pointer;
}

.brand-name:hover {
    color: rgba(58, 58, 58, 0.8);
}

.brand-tagline {
    margin: 0;
    font-size: 0.85rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.6);
}

.header-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    justify-self: end;
}

.action-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid rgba(212, 175, 55, 0.3);
    background: rgba(253, 251, 247, 0.88);
    color: #3A3A3A;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(212, 175, 55, 0.3);
    background: rgba(212, 175, 55, 0.18);
}

.header-bottom {
    display: flex;
    align-items: center;
    position: relative;
}

.menu {
    width: 100%;
}

.menu-main {
    list-style: none;
    display: flex;
    gap: clamp(18px, 3vw, 32px);
    margin: 0;
    padding: 0;
    justify-content: flex-end;
}

.menu-link {
    background: transparent;
    border: none;
    padding: 10px 0;
    font-size: 0.95rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.7);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
    font-weight: 600;
    transition: color 0.3s ease;
}

.menu-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -6px;
    width: 100%;
    height: 2px;
    background: rgba(212, 175, 55, 0.6);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.menu-link:hover {
    color: #3A3A3A;
}

.menu-link:hover::after {
    transform: scaleX(1);
}

.sub-menu {
    display: none;
    position: absolute;
    top: calc(100% + 2px);
    left: -90px;
    background: rgba(253, 251, 247, 0.98);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 16px;
    box-shadow: 0 18px 48px rgba(58, 58, 58, 0.18);
    padding: 18px;
    min-width: 220px;
    flex-direction: column;
    gap: 12px;
    z-index: 10;
}

.sub-menu.active {
    display: flex;
}

.menu-item-has-children {
    position: relative;
    padding-bottom: 6px;
}

.menu-item-has-children:hover .sub-menu {
    display: flex;
}

.sub-link {
    border: none;
    background: rgba(234, 218, 192, 0.35);
    border-radius: 12px;
    padding: 10px 14px;
    text-align: left;
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.75);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
}

.sub-link:hover {
    transform: translateX(4px);
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.8), rgba(234, 218, 192, 0.95));
    color: #3A3A3A;
}

.menu-overlay {
    display: none;
}

.mobile-menu-head {
    display: none;
}

.mobile-menu-head.active {
    display: flex;
}

.search-popup {
    position: fixed;
    inset: 0;
    background: rgba(58, 58, 58, 0.55);
    backdrop-filter: blur(6px);
    z-index: 1100;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 120px;
}

.search-popup-content {
    width: min(680px, 92vw);
    background: rgba(253, 251, 247, 0.98);
    border-radius: 26px;
    padding: clamp(24px, 4vw, 36px);
    box-shadow: 0 28px 70px rgba(58, 58, 58, 0.25);
}

.input-container {
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 18px;
    padding: 14px 18px;
    font-size: 1rem;
    color: rgba(58, 58, 58, 0.7);
}

.search-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 1rem;
    color: #3A3A3A;
}

.close-icon {
    font-size: 1.2rem;
    cursor: pointer;
    color: rgba(58, 58, 58, 0.6);
}

.search-results {
    margin-top: 18px;
}

.search-result-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
}

.search-result-item {
    background: rgba(234, 218, 192, 0.4);
    border-radius: 16px;
    padding: 12px;
    text-align: center;
}

.search-result-item img {
    width: 100%;
    border-radius: 14px;
    object-fit: cover;
    margin-bottom: 10px;
}

.image-name {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.72);
}

@media (max-width: 992px) {
    .header-shell {
        gap: 18px;
    }

    .mobile-menu-trigger {
        display: flex;
    }

    .menu {
        position: fixed;
        top: 0;
        left: -100%;
        width: 80%;
        max-width: 320px;
        height: 100%;
        background: rgba(253, 251, 247, 0.97);
        box-shadow: 16px 0 40px rgba(58, 58, 58, 0.2);
        padding: 32px 24px;
        transition: left 0.4s ease;
        display: flex;
        flex-direction: column;
    }

    .menu.active {
        left: 0;
    }

    .menu-main {
        flex-direction: column;
        gap: 18px;
        margin-top: 24px;
    }

    .menu-item-has-children:hover .sub-menu {
        display: none;
    }

    .menu-item-has-children.active .sub-menu {
        display: flex;
    }

    .sub-menu {
        position: static;
        top: auto;
        background: rgba(234, 218, 192, 0.4);
        box-shadow: none;
        border: none;
        margin-top: 12px;
    }

    .menu-overlay {
        position: fixed;
        inset: 0;
        background: rgba(58, 58, 58, 0.45);
        display: none;
        z-index: 900;
    }

    .menu-overlay.active {
        display: block;
    }

    .mobile-menu-head {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .menu.active .mobile-menu-head {
        display: flex;
    }

    .current-menu-title {
        flex: 1;
        text-align: center;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(58, 58, 58, 0.68);
    }
}

@media (max-width: 640px) {
    .header-top {
        grid-template-columns: auto 1fr;
        grid-template-rows: auto auto;
    }

    .brand {
        grid-row: 1 / 2;
        grid-column: 2 / 3;
    }

    .header-actions {
        grid-column: 1 / 3;
        justify-content: flex-end;
    }

    .brand-mark {
        width: 44px;
        height: 44px;
    }
}

@keyframes slideLeft {
    from {
        transform: translateX(30px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(30px);
        opacity: 0;
    }
}
</style>

