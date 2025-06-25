<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps<{
    links: {
        url: string;
        label: string;
        active: boolean;
    }[];
}>();
</script>

<template>
    <nav class="relative flex justify-center">
        <template v-for="(link, index) in links" :key="index">
            <Link
                :href="link.url ?? ''"
                class="flex items-center justify-center rounded-lg px-3 py-2 text-sm text-gray-400"
                :class="{ 'bg-gray-100': link.active }"
                preserve-scroll
                prefetch
            >
                <span v-html="link.label"></span>
            </Link>
        </template>
    </nav>
</template>
