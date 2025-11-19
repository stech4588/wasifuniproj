<template>
    <div class="register-page">
        <div class="register-shell">
            <section class="register-copy">
                <p class="eyebrow">S-Tech Atelier</p>
                <h1>Join the circle of mindful collectors.</h1>
                <p class="copy-subtitle">
                    Create your Atelier profile to unlock bespoke drops, private previews, and a sanctuary tailored to your rituals.
                </p>
                <router-link to="/login" class="return-link">Already a member? Login</router-link>
            </section>

            <section class="register-card">
                <div class="register-card-head">
                    <span class="register-badge">Create account</span>
                    <h2>Share your details</h2>
                    <p>We keep your information secure and only use it to enhance your experience.</p>
                </div>

                <form @submit.prevent="register" class="register-form">
                    <div class="form-group">
                        <label for="name">Full name</label>
                        <input v-model="form.name" type="text" id="name" placeholder="Your name" />
                        <has-error :form="form" field="name" class="error-text" />
                    </div>

                    <div class="form-group">
                        <label for="phone_no">Phone</label>
                        <input v-model="form.phone_no" type="tel" id="phone_no" placeholder="+92 300 1234567" />
                        <has-error :form="form" field="phone_no" class="error-text" />
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input v-model="form.email" type="email" id="email" placeholder="you@example.com" />
                        <has-error :form="form" field="email" class="error-text" />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input v-model="form.password" type="password" id="password" placeholder="Create a secure password" />
                        <has-error :form="form" field="password" class="error-text" />
                    </div>

                    <button type="submit" class="primary-btn" :disabled="form.busy">
                        <span v-if="form.busy">Creating...</span>
                        <span v-else>Register</span>
                    </button>
                </form>

                <div class="register-footer">
                    <span>Prefer to browse first?</span>
                    <router-link to="/">Return home</router-link>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import {useHead} from "@vueuse/head";
import axios from "axios";
export default {
    name: 'Register',
    setup() {
        useHead({
            title: 'Register',
            description: 'Registration page'
        });
    },
    data() {
        return {
            form: new this.$form({
                name:'',
                phone_no:0,
                email: '',
                password: '',
            }),
        };
    },
    methods: {
        async register() {
            try {
                const response = await this.form.post('/api/register');

                this.$toast.success('User Registered successfully', { position: 'bottom-right', duration: 3000 });
                // Redirect to the landing page
                this.$router.push('/login');
                // After successful registration, you can navigate to another route
                // this.$router.push('/dashboard');
            } catch (e) {
                handleError(e,this.$toast)
            }
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.register-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(48px, 8vw, 96px) clamp(24px, 6vw, 120px);
    background: radial-gradient(circle at bottom left, rgba(212, 175, 55, 0.18), transparent 55%), linear-gradient(140deg, rgba(234, 218, 192, 0.6), rgba(253, 251, 247, 0.95));
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    color: #3A3A3A;
}

.register-shell {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: clamp(32px, 5vw, 64px);
    width: min(1040px, 100%);
    background: rgba(253, 251, 247, 0.92);
    border-radius: 32px;
    border: 1px solid rgba(212, 175, 55, 0.28);
    padding: clamp(32px, 6vw, 64px);
    box-shadow: 0 38px 90px rgba(58, 58, 58, 0.18);
    backdrop-filter: blur(8px);
}

.register-copy {
    display: flex;
    flex-direction: column;
    gap: 18px;
    justify-content: center;
}

.eyebrow {
    font-size: 0.75rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #D4AF37;
    font-weight: 600;
    margin: 0;
}

.register-copy h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.1rem, 4vw, 2.8rem);
    line-height: 1.2;
    margin: 0;
}

.copy-subtitle {
    font-size: 1.05rem;
    line-height: 1.8;
    color: rgba(58, 58, 58, 0.75);
    margin: 0;
}

.return-link {
    align-self: flex-start;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.75);
    text-decoration: none;
    border-bottom: 1px solid rgba(58, 58, 58, 0.3);
    padding-bottom: 4px;
    transition: color 0.3s ease, border-color 0.3s ease;
}

.return-link:hover {
    color: #3A3A3A;
    border-color: rgba(58, 58, 58, 0.5);
}

.register-card {
    background: rgba(253, 251, 247, 0.95);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    padding: clamp(28px, 4vw, 48px);
    display: flex;
    flex-direction: column;
    gap: 28px;
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.12);
}

.register-card-head {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.register-badge {
    align-self: flex-start;
    border-radius: 999px;
    padding: 8px 16px;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    font-size: 0.75rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 600;
}

.register-card-head h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.register-card-head p {
    margin: 0;
    color: rgba(58, 58, 58, 0.7);
}

.register-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 0.85rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(58, 58, 58, 0.65);
    font-weight: 600;
}

.form-group input {
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    background: rgba(253, 251, 247, 0.95);
    padding: 14px 18px;
    font-size: 1rem;
    color: #3A3A3A;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: rgba(212, 175, 55, 0.7);
    box-shadow: 0 14px 38px rgba(212, 175, 55, 0.22);
    background: rgba(253, 251, 247, 1);
}

.form-group input.is-invalid {
    border-color: rgba(212, 106, 79, 0.8);
}

.error-text,
:deep(.has-error) {
    color: #b35454;
    font-size: 0.82rem;
}

.primary-btn {
    border-radius: 999px;
    border: none;
    padding: 14px 20px;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease;
}

.primary-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(212, 175, 55, 0.3);
}

.primary-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.register-footer {
    display: flex;
    gap: 8px;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.7);
    justify-content: center;
}

.register-footer a {
    color: #3A3A3A;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid rgba(58, 58, 58, 0.4);
    padding-bottom: 2px;
}

.register-footer a:hover {
    border-color: rgba(58, 58, 58, 0.6);
}

@media (max-width: 768px) {
    .register-shell {
        padding: 32px;
        border-radius: 28px;
    }

    .register-card {
        border-radius: 24px;
    }
}
</style>
