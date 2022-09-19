<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    lte: Object,
    status: String,
});

const form = useForm();

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <div class="hold-transition login-page">
        <div class="verify-box">
            <!-- title -->
            <div class="login-logo">
                <a><b>{{lte?.app?.name}}</b></a>
            </div>
            <!-- form -->
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. </p>
                    <p class="login-box-msg text-success" v-if="verificationLinkSent">A new verification link has been sent to the email address you provided during registration.</p>
                    <form method="post" @submit.prevent="submit">
                        <div class="row">
                            <div class="col-8">
                                <button class="btn btn-primary btn-block" type="submit">Resend Verification Email</button>
                            </div>
                            <div class="col-4 logout-body">
                                <button class="btn btn-link">Logout</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.verify-box {
    width: 560px;
}
.logout-body{
    text-align: right;
}
.logout-body .btn{
    color: #4B5563;
}
</style>
