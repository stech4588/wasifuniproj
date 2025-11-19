<template>
    <div v-if="loading === false" class="login-page">
        <div class="login-shell">
            <section class="login-copy">
                <p class="eyebrow">S-Tech Atelier</p>
                <h1>Welcome back to your curated sanctuary.</h1>
                <p class="copy-subtitle">
                    Sign in to access bespoke pieces, saved edits, and personalised recommendations crafted around your rituals.
                </p>
                <router-link to="/" class="return-link">Return to collections</router-link>
            </section>

            <section class="login-card">
                <div class="login-card-head">
                    <span class="login-badge">Sign in</span>
                    <h2>Enter your details</h2>
                    <p>We’ll restore your cart, favourites, and Atelier journal.</p>
                </div>

                <form @submit.prevent="handleLogin" class="login-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            v-model="form.email"
                            :class="{ 'is-invalid': form.errors.has('email') }"
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                        />
                        <has-error :form="form" field="email" class="error-text" />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            v-model="form.password"
                            :class="{ 'is-invalid': form.errors.has('password') }"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                        />
                        <has-error :form="form" field="password" class="error-text" />
                    </div>

                    <button type="submit" class="primary-btn" :disabled="form.busy">
                        <span v-if="form.busy">Signing in...</span>
                        <span v-else>Login</span>
                    </button>
                </form>

                <div class="login-footer">
                    <span>New to S-Tech Atelier?</span>
                    <router-link to="/register">Create an account</router-link>
                </div>
            </section>
        </div>
    </div>
    <div v-else class="login-loader">
        <Loader />
    </div>
</template>

<script>

export default {
    name: 'Login',
    inject: ['authStore'],

    data() {
        return {
            form: new this.$form({
                email: '',
                password: '',
            }),
            loading: false
        };
    },

    async mounted() {
        this.$useHead({
            title: 'login',
            description: 'login page'
        });
    },

    methods: {
        async handleLogin() {
            this.loading = true;
            try {
                const response = await this.LoginApiCall();
                this.authStore.login(response.data);

                    const permissionsToCheck = ['normalUser','adminUser'];
                    const permissionResults = await checkPermissions(permissionsToCheck);
                    permissionsToCheck.forEach(permission => {
                        if (permissionResults[permission]) {
                            if (permission === 'normalUser') {
                                this.loading = false;
                                // this.$router.push('/');
                                const returnUrl = this.$route.query.returnUrl || '/'; // Default to home if no returnUrl
                                this.$router.push(returnUrl);
                            } else if (permission === 'adminUser') {
                                this.loading = false;
                                this.$router.push('/dashboard');
                            }
                        }
                    });

                this.$toast.success('User Login successfully', { position: 'bottom-right', duration: 3000 });
                // Redirect to the landing page
            } catch (e) {
                this.loading = false;
                handleError(e,this.$toast);
            }
        },

        async LoginApiCall() {
            try {
                const response = await this.form.post('/api/login');
                return response.data;
            } catch (e) {
                throw e;
            }
        }
    }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(48px, 8vw, 96px) clamp(24px, 6vw, 120px);
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 55%), linear-gradient(140deg, rgba(234, 218, 192, 0.65), rgba(253, 251, 247, 0.95));
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    color: #3A3A3A;
}

.login-shell {
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

.login-copy {
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

.login-copy h1 {
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

.login-card {
    background: rgba(253, 251, 247, 0.95);
    border-radius: 28px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    padding: clamp(28px, 4vw, 48px);
    display: flex;
    flex-direction: column;
    gap: 28px;
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.12);
}

.login-card-head {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.login-badge {
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

.login-card-head h2 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
}

.login-card-head p {
    margin: 0;
    color: rgba(58, 58, 58, 0.7);
}

.login-form {
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

.login-footer {
    display: flex;
    gap: 8px;
    font-size: 0.95rem;
    color: rgba(58, 58, 58, 0.7);
    justify-content: center;
}

.login-footer a {
    color: #3A3A3A;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid rgba(58, 58, 58, 0.4);
    padding-bottom: 2px;
}

.login-footer a:hover {
    border-color: rgba(58, 58, 58, 0.6);
}

.login-loader {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(140deg, rgba(234, 218, 192, 0.65), rgba(253, 251, 247, 0.95));
}

@media (max-width: 768px) {
    .login-shell {
        padding: 32px;
        border-radius: 28px;
    }

    .login-card {
        border-radius: 24px;
    }
}
</style>
