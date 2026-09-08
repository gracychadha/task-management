<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Department Performance</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Member Performance Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Member Performance</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completed</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">In Progress</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Overdue</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($memberStats as $member)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $member['department'] ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $member['total_tasks'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-green-600">{{ $member['completed_tasks'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-blue-600">{{ $member['in_progress_tasks'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-red-600">{{ $member['overdue_tasks'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member['completion_rate'] >= 75 ? 'bg-green-100 text-green-800' : ($member['completion_rate'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $member['completion_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-sm text-gray-500 text-center">No department members found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Trend -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Trend</h3>
                <div class="space-y-3">
                    @foreach($tasksByMonth as $month => $data)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $month }}</span>
                                <span class="font-medium">{{ $data['completed'] }}/{{ $data['total'] }} completed</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
