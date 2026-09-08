<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Logs</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Search" />
                        <x-text-input name="search" value="{{ request('search') }}" class="mt-1 block w-full" placeholder="Search activities..." />
                    </div>
                    <div class="min-w-[150px]">
                        <x-input-label value="User" />
                        <select name="user" class="border-gray-300 rounded-md shadow-sm mt-1 block w-full">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Date" />
                        <x-text-input name="date" type="date" value="{{ request('date') }}" class="mt-1" />
                    </div>
                    <x-primary-button type="submit">Filter</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($activities as $activity)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $activity->causer?->name ?? 'System' }}</div>
                                        <div class="text-xs text-gray-500">{{ $activity->causer?->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $activity->description }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $parts = explode(' ', $activity->description ?? '');
                                            $verb = $parts[0] ?? '';
                                            $color = match(true) {
                                                str_starts_with($verb, 'created') => 'bg-green-100 text-green-800',
                                                str_starts_with($verb, 'updated') => 'bg-blue-100 text-blue-800',
                                                str_starts_with($verb, 'deleted') => 'bg-red-100 text-red-800',
                                                str_starts_with($verb, 'moved') => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">{{ ucfirst($verb) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-sm text-gray-500 text-center">No activity logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $activities->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
