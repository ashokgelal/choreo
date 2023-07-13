<script setup lang="ts">
import {PropType} from "vue";

defineProps({
    task: Object as PropType<Task>,
})
</script>

<template>
    <div :key="task.id" class="relative flex items-start py-4">
        <div class="min-w-0 flex-1 text-sm leading-6">
            <label :for="`person-${task.id}`" class="select-none font-medium text-gray-900">{{ task.description }}</label>
        </div>
    </div>
</template>
