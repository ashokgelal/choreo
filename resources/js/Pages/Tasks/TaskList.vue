<script setup lang="ts">

import {PropType} from "vue";
import TaskItem from "@/Pages/Tasks/TaskItem.vue";

const props = defineProps({
    tasks: { type: Array as PropType<Task[]>, required: true, },
})
</script>

<template>
    <fieldset class="p-4">
        <legend class="text-xl font-bold leading-6 text-gray-900 pt-4 uppercase">Tasks</legend>
        <div class="divide-y divide-gray-200 border-b border-t border-gray-200">
            <div v-for="task in tasks" :key="task.id" >
                <TaskItem :task="task"/>
            </div>
        </div>
    </fieldset>
</template>

<style scoped>

</style>
