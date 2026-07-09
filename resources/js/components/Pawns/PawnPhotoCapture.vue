<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue'])

const videoRef = ref(null)
const canvasRef = ref(null)
const fileInputRef = ref(null)

const isCameraOpen = ref(false)
const isCameraLoading = ref(false)
const cameraError = ref('')
const stream = ref(null)

const photos = computed({
    get: () => props.modelValue || [],
    set: (value) => emit('update:modelValue', value),
})

const photoCount = computed(() => photos.value.length)

const openCamera = async () => {
    if (props.disabled) return

    cameraError.value = ''
    isCameraLoading.value = true
    isCameraOpen.value = true

    await nextTick()

    try {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Tu navegador no permite usar la cámara desde esta página.')
        }

        stream.value = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1280 },
                height: { ideal: 960 },
            },
            audio: false,
        })

        if (videoRef.value) {
            videoRef.value.srcObject = stream.value
            await videoRef.value.play()
        }
    } catch (error) {
        cameraError.value = error?.message || 'No se pudo abrir la cámara.'
    } finally {
        isCameraLoading.value = false
    }
}

const closeCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach((track) => track.stop())
        stream.value = null
    }

    if (videoRef.value) {
        videoRef.value.srcObject = null
    }

    isCameraOpen.value = false
    isCameraLoading.value = false
}

const capturePhoto = () => {
    if (!videoRef.value || !canvasRef.value) return

    const video = videoRef.value
    const canvas = canvasRef.value

    const maxWidth = 1280
    const ratio = video.videoWidth > maxWidth ? maxWidth / video.videoWidth : 1

    canvas.width = Math.round(video.videoWidth * ratio)
    canvas.height = Math.round(video.videoHeight * ratio)

    const context = canvas.getContext('2d')
    context.drawImage(video, 0, 0, canvas.width, canvas.height)

    const dataUrl = canvas.toDataURL('image/jpeg', 0.82)

    addPhoto(dataUrl, 'camera')

    closeCamera()
}

const triggerFileInput = () => {
    if (props.disabled) return
    fileInputRef.value?.click()
}

const onFileChange = async (event) => {
    const files = Array.from(event.target.files || [])

    for (const file of files) {
        if (!file.type.startsWith('image/')) continue

        const dataUrl = await compressFile(file)
        addPhoto(dataUrl, 'file')
    }

    event.target.value = ''
}

const compressFile = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader()

        reader.onload = () => {
            const image = new Image()

            image.onload = () => {
                const canvas = document.createElement('canvas')
                const maxWidth = 1280
                const ratio = image.width > maxWidth ? maxWidth / image.width : 1

                canvas.width = Math.round(image.width * ratio)
                canvas.height = Math.round(image.height * ratio)

                const context = canvas.getContext('2d')
                context.drawImage(image, 0, 0, canvas.width, canvas.height)

                resolve(canvas.toDataURL('image/jpeg', 0.82))
            }

            image.onerror = reject
            image.src = reader.result
        }

        reader.onerror = reject
        reader.readAsDataURL(file)
    })
}

const addPhoto = (dataUrl, source) => {
    photos.value = [
        ...photos.value,
        {
            uid: globalThis.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`,
            data_url: dataUrl,
            source,
            captured_at: new Date().toISOString(),
        },
    ]
}

const removePhoto = (index) => {
    photos.value = photos.value.filter((_, photoIndex) => photoIndex !== index)
}

const clearPhotos = () => {
    photos.value = []
}

const iconPath = (icon) => {
    const icons = {
        camera: 'M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2v11ZM12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
        upload: 'M12 3v12M7 8l5-5 5 5M5 21h14',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        x: 'M18 6 6 18M6 6l12 12',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        image: 'M4 5h16v14H4V5Zm3 10 3-3 2 2 3-4 2 5M8 9h.01',
    }

    return icons[icon] || icons.camera
}

onBeforeUnmount(() => {
    closeCamera()
})
</script>

<template>
    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-950">
                    Fotografías de la prenda
                </h2>

                <p class="text-sm text-slate-500">
                    Toma fotos con la cámara o súbelas desde archivo.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="sicem-btn-blue inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-xs font-black shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="disabled"
                    @click="openCamera"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('camera')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Tomar foto
                </button>

                <button
                    type="button"
                    class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-xs font-black shadow-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="disabled"
                    @click="triggerFileInput"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('upload')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Subir imagen
                </button>

                <button
                    v-if="photoCount"
                    type="button"
                    class="rounded-2xl border border-red-200 bg-white px-4 py-2 text-xs font-black text-red-600 transition hover:bg-red-50"
                    @click="clearPhotos"
                >
                    Limpiar
                </button>
            </div>

            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                capture="environment"
                multiple
                class="hidden"
                @change="onFileChange"
            />
        </div>

        <div class="p-6">
            <div
                v-if="photoCount"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <article
                    v-for="(photo, index) in photos"
                    :key="photo.uid || index"
                    class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 shadow-sm"
                >
                    <img
                        :src="photo.data_url"
                        :alt="`Foto ${index + 1}`"
                        class="h-52 w-full object-cover"
                    />

                    <div class="flex items-center justify-between gap-3 p-4">
                        <div>
                            <p class="text-sm font-black text-slate-900">
                                Foto {{ index + 1 }}
                            </p>
                            <p class="text-xs font-semibold text-slate-400">
                                {{ photo.source === 'camera' ? 'Cámara' : 'Archivo' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-red-200 bg-white text-red-600 transition hover:bg-red-500 hover:text-white"
                            @click="removePhoto(index)"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('image')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-950">
                    Sin fotografías
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Agrega al menos una foto para que la boleta quede respaldada.
                </p>
            </div>
        </div>

        <div
            v-if="isCameraOpen"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">
                            Tomar foto de la prenda
                        </h2>

                        <p class="text-sm text-slate-500">
                            Coloca la prenda dentro del cuadro y captura la imagen.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeCamera"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <div
                        v-if="cameraError"
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800"
                    >
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('alert')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            <div>
                                <p class="font-black">
                                    No se pudo abrir la cámara
                                </p>
                                <p class="mt-1 text-sm">
                                    {{ cameraError }}
                                </p>
                                <p class="mt-1 text-xs">
                                    Puedes usar “Subir imagen” como alternativa.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="overflow-hidden rounded-[1.5rem] bg-slate-950"
                    >
                        <video
                            ref="videoRef"
                            autoplay
                            playsinline
                            muted
                            class="max-h-[65vh] w-full bg-black object-contain"
                        />
                    </div>

                    <canvas ref="canvasRef" class="hidden" />
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeCamera"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="sicem-btn-rose rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-rose-100 transition disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isCameraLoading || !!cameraError"
                        @click="capturePhoto"
                    >
                        {{ isCameraLoading ? 'Abriendo cámara...' : 'Capturar foto' }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #4f4896 !important;
    color: #ffffff !important;
}

.sicem-btn-blue {
    background-color: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
}

.sicem-btn-blue:hover {
    background-color: #1d4ed8 !important;
    color: #ffffff !important;
}

.sicem-btn-rose {
    background-color: #e11d48 !important;
    color: #ffffff !important;
    border-color: #e11d48 !important;
}

.sicem-btn-rose:hover {
    background-color: #be123c !important;
    color: #ffffff !important;
}
</style>