<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Password" />

        <!-- Status message -->
        <div v-if="status" class="ds-alert ds-alert--success">
            {{ status }}
        </div>

        <!-- Title -->
        <div class="ds-form-header">
            <h1 class="ds-form-title">Lupa Password?</h1>
            <p class="ds-form-subtitle">
                Tidak masalah! Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.
            </p>
        </div>

        <form @submit.prevent="submit" class="ds-form">
            <!-- Email field -->
            <div class="ds-form-group">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="ds-input"
                    v-model="form.email"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="ds-error" :message="form.errors.email" />
            </div>

            <!-- Submit button -->
            <div class="ds-form-actions">
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="{ 'ds-btn--loading': form.processing }"
                    class="ds-btn ds-btn--primary"
                >
                    {{ form.processing ? 'Sedang mengirim...' : 'Kirim Tautan Reset' }}
                </button>
            </div>

            <!-- Back to login link -->
            <div class="ds-form-footer">
                <p class="ds-form-footer-text">
                    Ingat password Anda?
                    <Link :href="route('login')" class="ds-link">
                        Kembali ke login
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.ds-alert {
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.9rem;
    margin-bottom: 24px;
    animation: slideDown 0.3s ease;
}

.ds-alert--success {
    background: linear-gradient(135deg, rgba(58, 171, 46, 0.1), rgba(92, 201, 79, 0.08));
    border: 1px solid rgba(58, 171, 46, 0.2);
    color: #268c1a;
}

.ds-form-header {
    margin-bottom: 32px;
    text-align: center;
}

.ds-form-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: #14213d;
    margin: 0 0 8px;
    letter-spacing: -0.5px;
}

.ds-form-subtitle {
    font-size: 0.9rem;
    color: #7a8aad;
    margin: 0;
    line-height: 1.6;
}

.ds-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ds-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.ds-input {
    padding: 12px 14px;
    border: 2px solid #dde4f0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: white;
    color: #14213d;
}

.ds-input::placeholder {
    color: #b3d4f5;
}

.ds-input:focus {
    outline: none;
    border-color: #1a6fbd;
    box-shadow: 0 0 0 3px rgba(26, 111, 189, 0.1);
}

.ds-error {
    font-size: 0.85rem;
    color: #e74c3c;
}

.ds-form-actions {
    margin-top: 12px;
}

.ds-btn {
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

.ds-btn--primary {
    background: linear-gradient(135deg, #1a6fbd, #0f4f8e);
    color: white;
    box-shadow: 0 4px 14px rgba(26, 111, 189, 0.3);
}

.ds-btn--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 111, 189, 0.4);
}

.ds-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.ds-btn--loading {
    opacity: 0.7;
}

.ds-form-footer {
    text-align: center;
    padding-top: 12px;
    border-top: 1px solid #eef2f9;
}

.ds-form-footer-text {
    margin: 0;
    font-size: 0.9rem;
    color: #7a8aad;
}

.ds-link {
    color: #1a6fbd;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    margin-left: 4px;
}

.ds-link:hover {
    color: #0f4f8e;
    text-decoration: underline;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
