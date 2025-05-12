<template>
    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Upload Excel File</h1>
        <form @submit.prevent="submit">
            <input type="file" ref="file" accept=".xlsx" class="mb-4" />
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
        </form>
        <p v-if="status" class="mt-4 text-green-600">{{ status }}</p>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const status = ref('')
const file = ref(null)

function submit() {
    const selectedFile = file.value.files[0]
    if (!selectedFile) return

    const formData = new FormData()
    formData.append('excel', selectedFile)

    router.post('/upload', formData, {
        forceFormData: true,
        onSuccess: () => status.value = 'Upload successful, processing started!',
        onError: () => status.value = 'Upload failed. Please try again.',
    })
}
</script>
