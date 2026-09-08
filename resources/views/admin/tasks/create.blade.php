<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Task</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.tasks.store') }}">
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
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                    @foreach(\App\Enums\TaskStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ old('status', 'todo') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="priority" value="Priority" />
                                <select id="priority" name="priority" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                    @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                        <option value="{{ $priority->value }}" {{ old('priority', 'medium') === $priority->value ? 'selected' : '' }}>{{ $priority->label() }}</option>
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
                            <x-input-label for="assign_department" value="Assign to entire department (optional)" />
                            <select id="assign_department" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                <option value="">— Select a department —</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }} ({{ $department->users->count() }} members)</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecting a department checks all its members as assignees. You can still adjust individual assignees below.</p>
                        </div>

                        <div>
                            <x-input-label for="assigned_to" value="Assignees" />
                            <select id="assigned_to" name="assigned_to[]" multiple class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" size="6">
                                @foreach($departments as $department)
                                    <optgroup label="{{ $department->name }}" data-department="{{ $department->id }}">
                                        @foreach($department->users as $user)
                                            <option value="{{ $user->id }}" data-department="{{ $department->id }}" {{ in_array($user->id, old('assigned_to', [])) ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple assignees.</p>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const deptSelect = document.getElementById('assign_department');
                                const assigneeSelect = document.getElementById('assigned_to');

                                if (deptSelect && assigneeSelect) {
                                    deptSelect.addEventListener('change', function () {
                                        if (!this.value) return;
                                        const deptId = this.value;
                                        const options = Array.from(assigneeSelect.options);
                                        const anySelected = options.some(o => o.selected);
                                        const targetSelected = options.filter(o => o.dataset.department === deptId).some(o => o.selected);

                                        if (anySelected && targetSelected && !confirm('Replace current assignees with all members of this department?')) {
                                            return;
                                        }

                                        options.forEach(o => {
                                            if (o.dataset.department === deptId) {
                                                o.selected = true;
                                            } else {
                                                o.selected = false;
                                            }
                                        });
                                        this.selectedIndex = 0;
                                    });
                                }
                            });
                        </script>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="parent_id" value="Parent Task" />
                                <select id="parent_id" name="parent_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">None</option>
                                    @foreach($parentTasks as $pt)
                                        <option value="{{ $pt->id }}" {{ old('parent_id') == $pt->id ? 'selected' : '' }}>{{ $pt->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="estimated_hours" value="Estimated Hours" />
                                <x-text-input id="estimated_hours" name="estimated_hours" type="number" step="0.5" min="0" class="mt-1 block w-full" :value="old('estimated_hours')" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 gap-3">
                        <a href="{{ route('admin.tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>Create Task</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
