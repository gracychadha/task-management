<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Task</h1>
                    <p class="text-sm text-gray-500">Assign a new task to your team members</p>
                </div>
            </div>
            <a href="{{ route('manager.team-tasks') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Tasks
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                <div class="p-8">
                    <form method="POST" action="{{ route('manager.team-tasks.store') }}">
                        @csrf

                        {{-- Task Details --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </span>
                                Task Details
                            </h3>
                            <div class="space-y-4 pl-9">
                                <div>
                                    <x-input-label for="title" value="Title" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full rounded-xl" :value="old('title')" required autofocus placeholder="e.g. Design the onboarding flow" />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" value="Description" />
                                    <textarea id="description" name="description" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm mt-1 block w-full" placeholder="Describe the task in detail...">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="status" value="Status" />
                                        <select id="status" name="status" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm block w-full">
                                            @foreach(\App\Enums\TaskStatus::cases() as $s)
                                                <option value="{{ $s->value }}" {{ old('status', 'new') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="priority" value="Priority" />
                                        <select id="priority" name="priority" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm block w-full">
                                            @foreach(\App\Enums\TaskPriority::cases() as $p)
                                                <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Schedule --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                Schedule & Estimation
                            </h3>
                            <div class="space-y-4 pl-9">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="start_date" value="Start Date" />
                                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full rounded-xl" :value="old('start_date')" />
                                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="due_date" value="Due Date" />
                                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full rounded-xl" :value="old('due_date')" />
                                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="estimated_hours" value="Estimated Hours" />
                                        <x-text-input id="estimated_hours" name="estimated_hours" type="number" step="0.5" min="0" class="mt-1 block w-full rounded-xl" :value="old('estimated_hours')" placeholder="e.g. 8" />
                                        <x-input-error :messages="$errors->get('estimated_hours')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Assignment --}}
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-violet-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                </span>
                                Assignment
                            </h3>
                            <div class="space-y-4 pl-9">
                                @include('partials.assignee-reviewer-picker', ['showAssignAll' => true])
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                            <a href="{{ route('manager.team-tasks') }}" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl border border-gray-300 hover:border-gray-400 transition">Cancel</a>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>