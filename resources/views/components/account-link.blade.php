<a href="{{ route($component->getRoute()) }}"
   class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
    <i class="fa-regular fa-id-card text-gray-400"></i>
    {{ $component->getLabel() }}
</a>
