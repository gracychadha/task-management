<div class="bg-gray-50 rounded-lg p-4 text-center">
    <div class="text-2xl font-bold {{ $color === 'green' ? 'text-green-600' : ($color === 'red' ? 'text-red-600' : ($color === 'yellow' ? 'text-yellow-600' : ($color === 'blue' ? 'text-blue-600' : ($color === 'purple' ? 'text-purple-600' : ($color === 'indigo' ? 'text-indigo-600' : 'text-gray-900'))))) }}">{{ $value }}</div>
    <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
</div>