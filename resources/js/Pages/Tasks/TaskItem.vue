<script setup lang="ts">
import {nextTick, PropType, ref, watch} from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    task: Object as PropType<Task>,
})
const editInput = ref<HTMLInputElement | null>(null);
const editing = ref(false);

const editForm = useForm({
    description: props.task.description,
});

function updateTask() {
    editForm.put(route('tasks.update', props.task.id), {
        onSuccess: () => editing.value = false
    })
}

function cancelEditing() {
    editing.value = false;
    editForm.reset();
    editForm.clearErrors();
}

function enterEditingMode() {
    editing.value = true;
    nextTick(() => {
        editInput.value?.focus();
    })
}
</script>

<template>
    <div :key="task.id" class="relative flex items-start py-4">
        <div class="min-w-0 flex-1 text-lg leading-6">
            <form v-if="editing" @submit.prevent="updateTask">
                <div class="flex gap-4">
                    <TextInput v-model="editForm.description"
                               ref="editInput"
                               type="text"
                              class="mt-4 w-full text-gray-900 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"/>

                    <PrimaryButton class="mt-4">Update</PrimaryButton>
                    <button class="mt-4" @click="cancelEditing()">Cancel</button>
                </div>
                <InputError :message="editForm.errors.description" class="mt-2"/>
            </form>
            <div v-else class="select-none font-medium text-gray-900">{{ task.description }}</div>
        </div>
        <Dropdown>
            <template #trigger>
                <button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20"
                         fill="currentColor">
                        <path
                            d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </button>
            </template>
            <template #content>
                <button
                    class="block w-full px-4 py-2 text-left leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 transition duration-150 ease-in-out"
                    @click="enterEditingMode">
                    Edit
                </button>
            </template>
        </Dropdown>
    </div>
</template>
