<script setup>
import { useForm } from '@inertiajs/inertia-vue3';

defineProps({
    lte: Object,
});

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    })
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
                    <p class="login-box-msg">This is a secure area of the application. Please confirm your password before continuing. </p>
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
                        <div class="row">
                            <div class="col-8"></div>
                            <div class="col-4">
                                <button class="btn btn-primary btn-block" type="submit">Confirm</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
