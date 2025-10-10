<script setup>
// Converted from https://github.com/inertiajs/pingcrm/blob/master/resources/js/Shared/Pagination.vue

import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex justify-end mr-1 mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="mb-1 mr-1 px-4 py-3 text-gray-400 text-sm leading-4 border rounded"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="mb-1 mr-1 px-4 py-3 focus:text-gray-500 text-sm leading-4 hover:bg-white border focus:border-gray-500 rounded"
                    :class="{ 'bg-white': link.active }"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
