<template>
    <div v-if="loading === false" class="category-page">
        <div v-if="categoryAdd === true">
            <section class="hero">
                <p class="hero-kicker">Collection Architecture</p>
                <h1>Add Category</h1>
                <p class="hero-subtitle">Craft a new category that complements your curated, artisanal collection.</p>
            </section>

            <div class="form-card">
                <form @submit.prevent="addCategory" @keydown="form.onKeydown($event)" class="category-form">
                    <div class="form-field">
                        <label class="field-label" for="category-name">{{ ('Name') }}</label>
                        <input id="category-name" v-model="form.name" class="field-input" type="text" name="name" required>
                        <has-error class="error-text" :form="form" field="name" />
                    </div>

                    <div class="form-field">
                        <span class="field-label">{{ ('Type') }}</span>
                        <div class="type-toggle">
                            <label class="type-option" :class="{ active: form.type === 'parent' }">
                                <input type="radio" v-model="form.type" value="parent">
                                <span>Parent Category</span>
                            </label>
                            <label class="type-option" :class="{ active: form.type === 'child' }">
                                <input type="radio" v-model="form.type" value="child">
                                <span>Child Category</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="field-label" for="category-image">{{ ('Image') }}</label>
                        <input id="category-image" @change="handleImageChange" class="field-input file-input" type="file" name="image" accept="image/*" required>
                        <p class="field-helper">Upload a refined image that embodies the mood of this category.</p>
                        <has-error class="error-text" :form="form" field="image" />
                    </div>

                    <div class="form-field" v-if="form.type === 'child'">
                        <label class="field-label" for="parent-category">{{ ('Select Parent Category') }}</label>
                        <select id="parent-category" v-model="form.parent_category_id" class="field-input" name="category" required>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <v-button :loading="form.busy" class="primary-btn">
                            {{ ('Save') }}
                        </v-button>
                        <router-link :to="{ name: 'category' }" class="secondary-btn">
                            {{ ('Cancel') }}
                        </router-link>
                    </div>
                </form>
            </div>
        </div>
        <div v-else class="unauthorized-wrapper">
            <Unauthorized />
        </div>
    </div>
    <div v-else class="loading-wrapper">
        <Loader />
    </div>
</template>

<script>

export default {
    inject: ['authStore'],
    data() {
        return {
            form: new this.$form({
                name: '',
                type: 'parent',
                image: null,
                parent_category_id: null,
            }),
            categories: [],
            addedSuccessful: false,
            loading: true,
            categoryAdd: false,
            filter: 'parent'
        }
    },
    async mounted() {
        this.$useHead({
            title: 'Category',
            description: 'Category create page'
        });

        if (this.authStore.user === null) {
            this.loading = true
        } else {
            const permissionsToCheck = ['categoryAdd'];
            const permissionResults = await checkPermissions(permissionsToCheck);
            permissionsToCheck.forEach(permission => {
                if (permissionResults[permission]) {
                    this[permission] = true;
                    if (permission === 'categoryAdd') {
                        this.loading = false;
                    }
                }
            });
        }

        // Fetch units from API here
        await this.fetchCreateData();
    },

    methods: {
        handleImageChange(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                this.form.image = file; // Store the selected file
            } else {
                event.target.value = ''; // Clear the input
                this.form.image = null; // Reset the stored file
                this.$toast.error('Please select a valid image file.', { position: 'bottom-right', duration: 3000 });
            }
        },

        async addCategory() {
            try {
                await this.form.post('/api/category')
                    .then(response => {
                        if (response.data.status === 200) {
                            this.$toast.success(response.data.message, { position: 'bottom-right', duration: 3000 });
                            // Redirect to the landing page
                            this.$router.push('/category');
                        } else {
                            handleError(e,this.$toast);
                        }
                    });

            } catch (e) {
                handleError(e,this.$toast);
            }
        },

        async fetchCreateData() {
            try {
                const response = await this.$axios.get(`/api/category/create`);
                this.categories = response.data.data.categories;
            } catch (e) {
                handleError(e,this.$toast);
            }
        },


    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.category-page {
    min-height: 100vh;
    padding: 72px 24px 96px;
    background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 55%), linear-gradient(135deg, rgba(234, 218, 192, 0.55), rgba(253, 251, 247, 0.95));
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    color: #3A3A3A;
    display: flex;
    flex-direction: column;
    gap: 48px;
}

.hero {
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
}

.hero-kicker {
    font-size: 0.75rem;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: #D4AF37;
    margin-bottom: 16px;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
}

.hero h1 {
    font-family: 'Playfair Display', 'Times New Roman', serif;
    font-size: clamp(2.25rem, 4vw, 3rem);
    margin-bottom: 16px;
}

.hero-subtitle {
    font-size: 1rem;
    line-height: 1.6;
    color: rgba(58, 58, 58, 0.75);
    max-width: 540px;
    margin: 0 auto;
}

.form-card {
    max-width: 860px;
    margin: 0 auto;
    background: rgba(253, 251, 247, 0.95);
    border: 1px solid rgba(212, 175, 55, 0.28);
    border-radius: 24px;
    padding: clamp(32px, 4vw, 48px);
    box-shadow: 0 24px 60px rgba(58, 58, 58, 0.12);
    backdrop-filter: blur(6px);
}

.category-form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.field-label {
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.8rem;
    color: rgba(58, 58, 58, 0.8);
}

.field-input {
    border: 1px solid rgba(234, 218, 192, 0.9);
    border-radius: 12px;
    padding: 14px 18px;
    font-size: 0.95rem;
    background: linear-gradient(135deg, rgba(250, 245, 236, 0.85), rgba(253, 251, 247, 0.95));
    color: #3A3A3A;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
}

.field-input:focus {
    outline: none;
    border-color: rgba(212, 175, 55, 0.7);
    box-shadow: 0 12px 30px rgba(212, 175, 55, 0.18);
    transform: translateY(-1px);
}

.field-input::placeholder {
    color: rgba(58, 58, 58, 0.4);
}

.field-helper {
    font-size: 0.8rem;
    color: rgba(58, 58, 58, 0.55);
}

.file-input {
    padding: 10px 16px;
    cursor: pointer;
}

.file-input::-webkit-file-upload-button {
    background: #D4AF37;
    color: #3A3A3A;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.3s ease;
}

.file-input::-webkit-file-upload-button:hover {
    background: #c29b2f;
    transform: translateY(-1px);
}

.type-toggle {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
}

.type-option {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 14px;
    border: 1px solid rgba(234, 218, 192, 0.9);
    background: rgba(253, 251, 247, 0.8);
    color: rgba(58, 58, 58, 0.75);
    cursor: pointer;
    transition: border-color 0.3s ease, background 0.3s ease, transform 0.3s ease, color 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
}

.type-option:hover {
    border-color: rgba(212, 175, 55, 0.5);
    transform: translateY(-1px);
}

.type-option.active {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.85), rgba(234, 218, 192, 0.95));
    border-color: rgba(212, 175, 55, 0.7);
    color: #3A3A3A;
    box-shadow: 0 14px 30px rgba(212, 175, 55, 0.25);
}

.type-option input {
    display: none;
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 12px;
}

.primary-btn,
.secondary-btn {
    border-radius: 999px;
    padding: 12px 28px;
    font-size: 0.95rem;
    font-weight: 600;
    font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.primary-btn {
    background: linear-gradient(120deg, #D4AF37, #EADAC0);
    color: #3A3A3A;
    border: none;
    box-shadow: 0 14px 30px rgba(212, 175, 55, 0.3);
}

.primary-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(212, 175, 55, 0.35);
}

.secondary-btn {
    background: transparent;
    border: 1px solid rgba(58, 58, 58, 0.2);
    color: rgba(58, 58, 58, 0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.secondary-btn:hover {
    border-color: rgba(58, 58, 58, 0.35);
    transform: translateY(-2px);
}

.error-text,
:deep(.has-error) {
    color: #b35454;
    font-size: 0.8rem;
    font-weight: 500;
}

.unauthorized-wrapper,
.loading-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(234, 218, 192, 0.4), rgba(253, 251, 247, 0.95));
}

@media (max-width: 720px) {
    .category-page {
        padding: 48px 16px 72px;
    }

    .form-card {
        padding: 28px 22px;
        border-radius: 20px;
    }

    .form-actions {
        justify-content: center;
    }
}
</style>
