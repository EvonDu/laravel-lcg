<script setup>
import { useForm, Link } from '@inertiajs/inertia-vue3';

defineProps({
    lte: Object,
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <div class="hold-transition login-page">
        <div class="login-box">
            <!-- title -->
            <div class="login-logo">
                <a><b>{{lte?.app?.name}}</b></a>
            </div>
            <!-- form -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>
                    <p class="login-box-msg text-success" v-if="status">{{ status }}</p>
                    <form method="post" @submit.prevent="submit">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Email" v-model="form.email" :class="{'is-invalid' : $attrs.errors.email}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            <div class="invalid-feedback" v-if="$attrs.errors.email">
                                {{ $attrs.errors.email }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-primary btn-block" type="submit">Request new password</button>
                            </div>
                        </div>
                    </form>

                    <p class="mt-3 mb-1">
                        <Link :href="route('login')">Login</Link>
                    </p>
                    <p class="mb-0">
                        <Link :href="route('register')">Register a new membership</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
