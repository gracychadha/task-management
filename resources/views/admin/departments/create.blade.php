<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Department</h1>
                    <p class="text-sm text-gray-500">Add a new department to the organization</p>
                </div>
            </div>
            <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-sm font-medium text-white shadow-md shadow-sky-500/25 hover:shadow-lg hover:shadow-sky-500/30 hover:-translate-y-0.5 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Departments
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-sky-500 to-indigo-600"></div>
                <div class="p-8">
                    <form method="POST" action="{{ route('admin.departments.store') }}">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <x-input-label for="name" value="Department Name" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl" :value="old('name')" required autofocus placeholder="e.g. Engineering" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" value="Description" />
                                <textarea id="description" name="description" rows="3" class="mt-1 border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-xl shadow-sm block w-full" placeholder="Brief description of this department's purpose...">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100">
                                <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4" />
                                <x-input-label for="is_active" value="Active Department" class="!mb-0 text-emerald-700" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                            <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl border border-gray-300 hover:border-gray-400 transition">Cancel</a>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-sm font-medium text-white shadow-md shadow-sky-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Create Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>