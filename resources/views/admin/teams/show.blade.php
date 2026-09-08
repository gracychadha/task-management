<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $team->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.teams.edit', $team) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Edit</a>
                <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Delete this team?')">
                    @csrf @method('DELETE')
                    <x-danger-button type="submit">Delete</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Team Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center text-white text-xl font-bold" style="background-color: {{ $team->color ?? '#3b82f6' }}">
                        {{ strtoupper(substr($team->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $team->name }}</h3>
                        <p class="text-gray-500">{{ $team->description ?? 'No description' }}</p>
                        <p class="text-sm text-gray-500 mt-1">Owner: {{ $team->owner?->name }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Members -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Members ({{ $team->members->count() }})</h3>
                    </div>

                    <!-- Add Member Form -->
                    <form method="POST" action="{{ route('admin.teams.members.store', $team) }}" class="mb-4 flex gap-2">
                        @csrf
                        <select name="user_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm flex-1" required>
                            <option value="">Select member...</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->email }})</option>
                            @endforeach
                        </select>
                        <select name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                        <x-primary-button type="submit">Add</x-primary-button>
                    </form>

                    <div class="space-y-2">
                        @foreach($team->members as $member)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-{{ $member->getRoleBadgeColor() }}-100 flex items-center justify-center text-{{ $member->getRoleBadgeColor() }}-600 text-sm font-medium">
                                        {{ $member->initials() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->pivot->role }}</div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.teams.members.update', [$team, $member]) }}">
                                        @csrf @method('PATCH')
                                        <select name="role" onchange="this.form.submit()" class="text-xs border-gray-300 rounded">
                                            <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>Member</option>
                                            <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="owner" {{ $member->pivot->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                        </select>
                                    </form>
                                    @if($member->id !== $team->owner_id)
                                        <form method="POST" action="{{ route('admin.teams.members.destroy', [$team, $member]) }}" onsubmit="return confirm('Remove this member?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-500">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Team Stats -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Active Tasks</span>
                                <span class="font-medium text-gray-900">{{ $team->tasks_count ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Members</span>
                                <span class="font-medium text-gray-900">{{ $team->members->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
