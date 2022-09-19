<script setup>
import { useForm, Link } from '@inertiajs/inertia-vue3';

const props = defineProps({
    lte: Object,
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
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
                    <p class="login-box-msg">You are only one step a way from your new password, recover your password now.</p>
                    <p class="login-box-msg text-danger" v-if="$attrs.errors.email">{{ $attrs.errors.email }}</p>
                    <form method="post" @submit.prevent="submit">
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
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" placeholder="Confirm Password" v-model="form.password_confirmation" :class="{'is-invalid' : $attrs.errors.password_confirmation}" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            <div class="invalid-feedback" v-if="$attrs.errors.password_confirmation">
                                {{ $attrs.errors.password_confirmation }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-primary btn-block" type="submit">Change password</button>
                            </div>
                        </div>
                    </form>

                    <p class="mt-3 mb-1">
                        <Link :href="route('login')">Login</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
