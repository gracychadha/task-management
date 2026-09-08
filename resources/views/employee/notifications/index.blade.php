<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            <form method="POST" action="{{ route('employee.notifications.read-all') }}">
                @csrf
                <x-secondary-button type="submit">Mark All as Read</x-secondary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200">
                @forelse($notifications as $notification)
                    <div class="p-4 flex items-start justify-between {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">
                        <div class="flex gap-3">
                            <div class="mt-0.5">
                                <div class="w-2 h-2 rounded-full {{ $notification->read_at ? 'bg-gray-300' : 'bg-indigo-600' }}"></div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $notification->title }}</div>
                                <div class="text-sm text-gray-700 mt-0.5">{{ $notification->message }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @if($notification->data['task_id'] ?? null)
                                <a href="{{ route('employee.my-tasks.show', $notification->data['task_id']) }}" class="text-xs text-indigo-600 hover:text-indigo-500">View Task</a>
                            @endif
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('employee.notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-900">Mark Read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">No notifications.</div>
                @endforelse
            </div>
            <div class="mt-6">{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
