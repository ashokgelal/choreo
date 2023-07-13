<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import TaskList from "@/Pages/Tasks/TaskList.vue";
import {PropType} from "vue";
import AddTaskForm from "@/Pages/Tasks/Partials/AddTaskForm.vue";

const props = defineProps({
    tasks: {type: Object as PropType<{ data: Task[] }>, required: true,},
})

</script>

<template>
    <Head title="Tasks"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasks</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <AddTaskForm/>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <TaskList :tasks="tasks.data"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
