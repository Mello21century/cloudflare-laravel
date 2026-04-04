<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Domain Information
        </x-slot>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Domain</p>
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->zoneName }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ ucfirst($this->zoneStatus) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nameservers</p>
                @foreach($this->nameservers as $ns)
                    <p class="text-sm text-gray-950 dark:text-white">{{ $ns }}</p>
                @endforeach
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            DNS Records
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
