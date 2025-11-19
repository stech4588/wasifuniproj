<template>
    <footer class="atelier-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <div class="brand-mark">S</div>
                <div class="brand-copy">
                    <p class="brand-eyebrow">S-Tech Atelier</p>
                    <p class="brand-text">
                        Thoughtfully curated pieces that blend modern minimalism with artisanal warmth for mindful living.
                    </p>
                </div>
            </div>

            <div class="footer-columns">
                <div class="footer-column">
                    <p class="column-title">Discover</p>
                    <ul>
                        <li><router-link to="/">Collections</router-link></li>
                        <li><router-link to="/collection/featured">Featured curation</router-link></li>
                        <li><router-link to="/cart">Gifting concierge</router-link></li>
                        <li><router-link to="/review">Stories & journal</router-link></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <p class="column-title">Service</p>
                    <ul>
                        <li><router-link to="/userDashboard">Account</router-link></li>
                        <li><router-link to="/order">Order tracking</router-link></li>
                        <li><router-link to="/setting">Care & support</router-link></li>
                        <li><router-link to="/review">Leave a review</router-link></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <p class="column-title">Visit</p>
                    <ul class="contact-list">
                        <li><font-awesome-icon icon="location-dot" /> Lahore, Pakistan</li>
                        <li><font-awesome-icon icon="envelope" /> hello@stechatelier.com</li>
                        <li><font-awesome-icon icon="phone" /> +92 (0)42 1234 5678</li>
                    </ul>
                    <div class="footer-social">
                        <a href="#" aria-label="Instagram"><font-awesome-icon :icon="['fab', 'instagram']" /></a>
                        <a href="#" aria-label="Pinterest"><font-awesome-icon :icon="['fab', 'pinterest']" /></a>
                        <a href="#" aria-label="Facebook"><font-awesome-icon :icon="['fab', 'facebook']" /></a>
                        <a href="#" aria-label="LinkedIn"><font-awesome-icon :icon="['fab', 'linkedin']" /></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-bottom">
            <span>© {{ currentYear }} S-Tech Atelier. All rights reserved.</span>
            <div class="footer-links">
                <router-link to="/setting">Privacy</router-link>
                <router-link to="/setting">Terms</router-link>
                <router-link to="/review">Contact</router-link>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'Footer',
    data() {
        return {
            currentYear: new Date().getFullYear(),
        };
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.atelier-footer {
    background: linear-gradient(160deg, rgba(234, 218, 192, 0.55), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    padding: 72px clamp(24px, 6vw, 96px) 32px;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    border-top: 1px solid rgba(212, 175, 55, 0.28);
}

.footer-inner {
    display: grid;
    gap: clamp(32px, 5vw, 64px);
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    align-items: start;
}

.footer-brand {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.brand-mark {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(160deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 600;
}

.brand-eyebrow {
    font-size: 0.8rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.7);
    margin: 0 0 10px 0;
}

.brand-text {
    margin: 0;
    font-size: 1rem;
    line-height: 1.7;
    color: rgba(58, 58, 58, 0.75);
}

.footer-columns {
    display: grid;
    gap: 28px;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.footer-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.column-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    margin: 0;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #3A3A3A;
}

.footer-column ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-column a {
    text-decoration: none;
    color: rgba(58, 58, 58, 0.7);
    transition: color 0.3s ease, transform 0.3s ease;
}

.footer-column a:hover {
    color: #3A3A3A;
    transform: translateX(4px);
}

.contact-list li {
    display: flex;
    align-items: center;
    gap: 10px;
}

.footer-social {
    display: flex;
    gap: 14px;
    margin-top: 10px;
}

.footer-social a {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(212, 175, 55, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3A3A3A;
    transition: transform 0.3s ease, background 0.3s ease;
}

.footer-social a:hover {
    background: rgba(212, 175, 55, 0.3);
    transform: translateY(-2px);
}

.footer-divider {
    height: 1px;
    background: rgba(212, 175, 55, 0.28);
    margin: clamp(36px, 5vw, 56px) 0 32px;
}

.footer-bottom {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: rgba(58, 58, 58, 0.65);
}

.footer-links {
    display: flex;
    gap: 16px;
}

.footer-links a {
    text-decoration: none;
    color: rgba(58, 58, 58, 0.65);
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #3A3A3A;
}

@media (max-width: 720px) {
    .atelier-footer {
        padding: 56px 24px 28px;
    }

    .footer-brand {
        flex-direction: column;
    }

    .brand-mark {
        width: 52px;
        height: 52px;
    }

    .footer-columns {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 20px;
    }
}
</style>
