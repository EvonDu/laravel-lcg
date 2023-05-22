<script setup>
import { useForm, Link } from '@inertiajs/inertia-vue3';

defineProps({
    lte: Object,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post('register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="hold-transition register-page">
        <div class="register-box">
            <!-- title -->
            <div class="register-logo">
                <a><b>{{lte?.app?.name}}</b></a>
            </div>
            <!-- form -->
            <div class="card">
                <div class="card-body register-card-body">
                    <p class="register-box-msg">Register a new membership</p>
                    <form method="post" @submit.prevent="submit">
                        <div class="input-group mb-3">
                            <input type="name" class="form-control" placeholder="Name" v-model="form.name" :class="{'is-invalid' : $attrs.errors.name}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                            <div class="invalid-feedback" v-if="$attrs.errors.name">
                                {{ $attrs.errors.name }}
                            </div>
                        </div>
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
                            <div class="col-8"></div>
                            <div class="col-4">
                                <button class="btn btn-primary btn-block" type="submit">Register</button>
                            </div>
                        </div>
                    </form>

                    <p class="mt-3 mb-1">
                        <Link :href="route('login')">Already registered?</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
