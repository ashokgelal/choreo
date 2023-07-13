<script setup lang="ts">
import {computed, nextTick, PropType, ref, watch} from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const props = defineProps({
    task: Object as PropType<Task>,
})

const availableTaskStatusActions = computed(() =>  ['todo', 'in progress', 'done'].filter(status => status !== props.task.status));
const taskIsDone = computed(() => props.task.status === 'done');

function changeStatus(status: string) {
    editForm.transform((data) => {
        return {
            ...data,
            status
        }
    })
    editForm.put(route('tasks.update', props.task.id))
}


const editInput = ref<HTMLInputElement | null>(null);
const editing = ref(false);

const editForm = useForm({
    description: props.task.description,
    status: props.task.status
});

function enterEditingMode() {
    editing.value = true;
    nextTick(() => {
        editInput.value?.focus();
    })
}

function cancelEditing() {
    editing.value = false;
    editForm.reset();
    editForm.clearErrors();
}

function updateTask() {
    editForm.put(route('tasks.update', props.task.id), {
        onSuccess: () => editing.value = false
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
                <InputError v-if="editForm.errors.description" :message="editForm.errors.description" class="mt-2"/>
            </form>
            <div  v-else class="mr-4 flex">
                <div class="font-medium flex-1" :class="{'line-through text-gray-400': taskIsDone, 'text-gray-900': !taskIsDone}">{{ task.description }}</div>
                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 uppercase">{{ task.status }}</span>
            </div>
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
                    v-for="action in availableTaskStatusActions"
                    :key="action"
                    class="block w-full px-4 py-2 text-left leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 transition duration-150 ease-in-out text-sm"
                    @click="changeStatus(action)">
                    Mark <span class="uppercase"> {{ action }} </span>
                </button>
                <div class="border-t border-gray-100"></div>
                <button
                    class="block w-full px-4 py-2 text-left leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 transition duration-150 ease-in-out text-sm"
                    @click="enterEditingMode">
                    Edit
                </button>
                <DropdownLink as="button" :href="route('tasks.destroy', task.id)" method="delete" class="text-red-500">
                    Delete
                </DropdownLink>
            </template>
        </Dropdown>
    </div>
</template>
