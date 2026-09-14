<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    @php
        $role = Auth::user()->role;
        $notificationsRoute = match ($role) {
            'manager' => route('manager.notifications'),
            'employee' => route('employee.notifications'),
            default => null,
        };
        $unreadNotifications = $notificationsRoute
            ? \App\Models\UserNotification::where('user_id', Auth::id())->unread()->count()
            : 0;
        $hasReviewTasks = $role === 'employee'
            ? \App\Models\Task::pendingReviewFor(Auth::id())->exists()
            : false;
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('image/final-logo.png') }}" alt="Task Management" class="block h-9 w-auto" />
                        <span class="hidden sm:block text-lg font-bold tracking-tight text-gray-900">Task<span class="text-indigo-600">Management</span></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if($role === 'admin')
                        <x-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees*')">
                            {{ __('Employees') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments*')">
                            {{ __('Departments') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.tasks.index')" :active="request()->routeIs('admin.tasks*')">
                            {{ __('Tasks') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports*')">
                            {{ __('Reports') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs*')">
                            {{ __('Activity Logs') }}
                        </x-nav-link>
                    @elseif($role === 'manager')
                        <x-nav-link :href="route('manager.team-tasks')" :active="request()->routeIs('manager.team-tasks*')">
                            {{ __('Tasks') }}
                        </x-nav-link>
                        <x-nav-link :href="route('manager.reports.index')" :active="request()->routeIs('manager.reports*')">
                            {{ __('Reports') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('employee.my-tasks')" :active="request()->routeIs('employee.my-tasks*')">
                            {{ __('My Tasks') }}
                        </x-nav-link>
                        @if($hasReviewTasks)
                            <x-nav-link :href="route('employee.review-tasks')" :active="request()->routeIs('employee.review-tasks')">
                                {{ __('Review Tasks') }}
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Right side: notifications + profile -->
            <div class="hidden sm:flex sm:items-center sm:gap-2">
                @if($notificationsRoute)
                    <a href="{{ $notificationsRoute }}"
                       class="relative p-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                       title="Notifications">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                        @if($unreadNotifications > 0)
                            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[1.25rem] h-[1.25rem] px-1 rounded-full text-[10px] font-bold text-white bg-red-500 border-2 border-white">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                        @endif
                    </a>
                @endif

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-2 py-1.5 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                                {{ Auth::user()->initials() }}
                            </div>
                            <span class="hidden md:block text-left">
                                <span class="block text-sm font-semibold text-gray-900 leading-tight">{{ Auth::user()->name }}</span>
                                <span class="block text-xs text-gray-500 capitalize leading-tight">{{ Auth::user()->role }}</span>
                            </span>
                            <svg class="hidden md:block h-4 w-4 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100 mb-1">
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if($notificationsRoute)
                            <x-dropdown-link :href="$notificationsRoute">
                                {{ __('Notifications') }}
                                @if($unreadNotifications > 0)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $unreadNotifications }}</span>
                                @endif
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if($role === 'admin')
                <x-responsive-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees*')">
                    {{ __('Employees') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments*')">
                    {{ __('Departments') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.tasks.index')" :active="request()->routeIs('admin.tasks*')">
                    {{ __('Tasks') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports*')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs*')">
                    {{ __('Activity Logs') }}
                </x-responsive-nav-link>
            @elseif($role === 'manager')
                <x-responsive-nav-link :href="route('manager.team-tasks')" :active="request()->routeIs('manager.team-tasks*')">
                    {{ __('Tasks') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.notifications')" :active="request()->routeIs('manager.notifications')">
                    {{ __('Notifications') }}
                    @if($unreadNotifications > 0)
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $unreadNotifications }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manager.reports.index')" :active="request()->routeIs('manager.reports*')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('employee.my-tasks')" :active="request()->routeIs('employee.my-tasks*')">
                    {{ __('My Tasks') }}
                </x-responsive-nav-link>
                @if($hasReviewTasks)
                    <x-responsive-nav-link :href="route('employee.review-tasks')" :active="request()->routeIs('employee.review-tasks')">
                        {{ __('Review Tasks') }}
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('employee.notifications')" :active="request()->routeIs('employee.notifications')">
                    {{ __('Notifications') }}
                    @if($unreadNotifications > 0)
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $unreadNotifications }}</span>
                    @endif
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>