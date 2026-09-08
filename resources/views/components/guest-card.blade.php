@props([])

<div class="min-h-screen flex flex-col sm:justify-center items-center px-6 py-12 bg-gray-100">
    <div class="mb-6">
        <div class="flex items-center justify-center mb-3">
            <img
                src="{{ asset('image/tmc.png') }}"
                alt="Task Management System"
                class="w-20 h-auto rounded-2xl bg-white p-2 shadow-lg shadow-slate-200"
            />
        </div>
        <h1 class="text-xl font-bold text-gray-900 text-center">Task Management System</h1>
    </div>

    <div class="w-full max-w-md bg-white shadow-xl rounded-2xl px-8 py-8">
        {{ $slot }}
    </div>
</div>
