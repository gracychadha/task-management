<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Task</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('manager.team-tasks.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="title" value="Title" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" name="description" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    @foreach(\App\Enums\TaskStatus::cases() as $s)
                                        <option value="{{ $s->value }}" {{ old('status', 'todo') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="priority" value="Priority" />
                                <select id="priority" name="priority" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    @foreach(\App\Enums\TaskPriority::cases() as $p)
                                        <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" value="Start Date" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" />
                            </div>
                            <div>
                                <x-input-label for="due_date" value="Due Date" />
                                <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="assigned_to" value="Assignees" />
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-gray-500">Hold Ctrl/Cmd to select multiple assignees.</p>
                                <button type="button" id="select_all_members" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Assign to all department members</button>
                            </div>
                            <select id="assigned_to" name="assigned_to[]" multiple class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" size="6">
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ in_array($member->id, old('assigned_to', [])) ? 'selected' : '' }}>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const btn = document.getElementById('select_all_members');
                                const sel = document.getElementById('assigned_to');
                                if (btn && sel) {
                                    btn.addEventListener('click', function () {
                                        Array.from(sel.options).forEach(o => o.selected = true);
                                        const deptName = @json(auth()->user()->department?->name ?? 'your department');
                                        alert('All department members selected. They will be notified when the task is created.');
                                    });
                                }
                            });
                        </script>

                        <div>
                            <x-input-label for="estimated_hours" value="Estimated Hours" />
                            <x-text-input id="estimated_hours" name="estimated_hours" type="number" step="0.5" min="0" class="mt-1 block w-full" :value="old('estimated_hours')" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 gap-3">
                        <a href="{{ route('manager.team-tasks') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>Create Task</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
