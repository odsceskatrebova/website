<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useToastStore } from '../stores/ToastStore';

const toastStore = useToastStore();
const { toasts } = storeToRefs(toastStore);
const { validateToasts, removeToast, displayAllToasts } = toastStore;

const toastClearerInterval = ref<ReturnType<typeof setInterval> | null>(null);

onMounted(() => {
    toastClearerInterval.value = setInterval(() => {
        validateToasts();
    }, 3000);

    setTimeout(() => {
        displayAllToasts();
    }, 500);
});

onUnmounted(() => {
    if (toastClearerInterval.value !== null) {
        clearInterval(toastClearerInterval.value);
    }
});

const displayableToasts = computed(() => {
    return toasts.value.filter((toast) => !toast.displayAfterRefresh);
});
</script>

<template>
    <div class="toast-bar">
        <div>
            <div
                v-for="toast in displayableToasts"
                :key="toast.id"
                class="toast"
                :class="toast.type"
            >
                <div class="message">
                    {{ toast.message }}
                </div>
                <div
                    class="close"
                    @click="removeToast(toast)"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"
                        />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
@use '../../styles/base/variables' as vars;

.toast-bar {
    position: fixed;
    left: 50%;
    bottom: 0;
    transform: translateX(-50%);
    z-index: 76000;
    padding: .5rem;

    .toast {
        background: #000000;
        border-radius: .5rem;
        box-shadow: 0 0 1rem vars.$textHighlightColor;
        padding: 1rem 1rem 1rem 2rem;
        display: grid;
        margin-bottom: 1rem;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 1rem;
        max-width: 30rem;
        color: #ffffff;

        .message {
            margin-top: .1rem;
            font-size: 1rem;
            line-height: 1.5rem;
        }

        .close {
            cursor: pointer;
            width: 2rem;
            height: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            border: 1px solid transparent;

            svg {
                width: 1.5rem;
                fill: #ffffff;
            }

            &:hover {
                border-color: vars.$darkGrey;

                svg {
                    fill: vars.$darkGrey;
                }
            }
        }

        &.success {
            background: green;
            box-shadow: 0 0 1rem vars.$darkGrey;
            color: #000000;

            .close {
                svg { fill: #000000; }

                &:hover {
                    border-color: #ffffff;
                    svg { fill: #ffffff; }
                }
            }
        }

        &.error {
            background: red;
            box-shadow: 0 0 1rem red;
            color: #ffffff;

            .close {
                svg { fill: #ffffff; }

                &:hover {
                    border-color: #ffffff;
                    svg { fill: #ffffff; }
                }
            }
        }
    }
}
</style>
