<script setup lang="ts">
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {useForm} from "@inertiajs/vue3";
import {nextTick, ref} from "vue";

const form = useForm({
    description: '',
});

const taskInput = ref<HTMLInputElement | null>(null);
const showingAddModal = ref(false);

function showModal() {
    showingAddModal.value = true;
    nextTick(() => {
        taskInput.value?.focus();
    })
}

function closeModal() {
    showingAddModal.value = false;
    form.reset();
}

function createTask() {
    form.post(route('tasks.store'), {
        onSuccess: () => closeModal(),
    });
}
</script>

<template>
    <PrimaryButton @click="showModal">Add New Task</PrimaryButton>
    <Modal :show="showingAddModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                New Task
            </h2>

            <div class="mt-6">
                <InputLabel for="task" value="Task" class="sr-only"/>

                <TextInput
                    id="task"
                    ref="taskInput"
                    v-model="form.description"
                    type="text"
                    class="mt-1 block w-3/4"
                    placeholder="What needs to be done?"
                    @keyup.enter="createTask"
                />

                <InputError :message="form.errors.description" class="mt-2"/>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="createTask"
                >
                    Add
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
