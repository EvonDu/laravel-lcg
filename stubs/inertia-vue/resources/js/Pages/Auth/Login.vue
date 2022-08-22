<script setup>
import { useForm, Link } from '@inertiajs/inertia-vue3';

defineProps({
    lte: Object,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const submit = () => {
    form.post('login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="hold-transition login-page">
        <div class="login-box">
            <!-- title -->
            <div class="login-logo">
                <a><b>{{lte.project}}</b></a>
            </div>
            <!-- form -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>
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
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" placeholder="Password" v-model="form.password" :class="{'is-invalid' : $attrs.errors.password}" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            <div class="invalid-feedback" v-if="$attrs.errors.password">
                                {{ $attrs.errors.password }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <el-checkbox label="Remember Me" size="large" v-model="form.remember"/>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-primary btn-block" type="submit">Sign In</button>
                            </div>
                        </div>
                    </form>

                    <p class="mt-3 mb-1">
                        <Link :href="route('password.request')">I forgot my password</Link>
                    </p>
                    <p class="mb-0">
                        <Link :href="route('register')">Register a new membership</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
