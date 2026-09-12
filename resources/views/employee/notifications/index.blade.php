<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-500">Updates about your tasks and reviews</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-gray-900" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleDateString('en-US', { weekday:'long', month:'short', day:'numeric', year:'numeric' }) + ' · ' + new Date().toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'}), 1000)" x-text="time"></p>
                </div>
                @if($unread > 0)
                    <form method="POST" action="{{ route('employee.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg hover:from-indigo-600 hover:to-violet-700 transition-all duration-200">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Mark All Read
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
                            <p class="text-sm text-gray-500">Total Notifications</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $unread }}</p>
                            <p class="text-sm text-gray-500">Unread</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notification List --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">All Notifications</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($notifications as $notification)
                        <div class="p-5 flex items-start gap-4 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'opacity-70' : 'bg-indigo-50/30' }}">
                            <div class="mt-1 shrink-0">
                                @if($notification->read_at)
                                    <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
                                @else
                                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-sm shadow-indigo-300"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900">{{ $notification->title }}</h4>
                                        <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{!! nl2br(e($notification->message)) !!}</p>
                                    </div>
                                    @if(!$notification->read_at)
                                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">New</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 mt-3">
                                    <span class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                    @if($notification->data['task_id'] ?? null)
                                        <a href="{{ route('employee.my-tasks.show', $notification->data['task_id']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            View Task
                                        </a>
                                    @endif
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('employee.notifications.read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-900 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                Mark Read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-16 text-center">
                            <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                            </div>
                            <p class="text-gray-500 font-medium">No notifications yet</p>
                            <p class="text-sm text-gray-400 mt-1">You'll see updates about your tasks here</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
