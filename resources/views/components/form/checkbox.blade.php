@props(['id', 'name', 'label', 'isChecked' => false, 'disabled' => false, 'description' => null, 'hidden' => false,])

<div x-data="{ isChecked: {{ $isChecked ? 'true' : 'false' }} }" class="flex items-center gap-2">
    <label for="{{ $id }}" class="toggle-switch {{$hidden ? 'hidden' : '' }}">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" x-model="isChecked"
            class="toggle-switch-checkbox" {{ $disabled ? 'disabled' : ''}}>
        @if(!$hidden)
            <span class="toggle-switch-slider"></span>
        @endif
    </label>

    @if($description && !$hidden)
        <div class="flex gap-1">
            {{-- <x-input-label value="{{ $label }}" /> --}}
            <span class="block font-medium text-sm text-background-700 dark:text-background-300">{{ $label }}</span>
            <div class="has-tooltip">
                <x-lucide-info class="h-4 text-background-300" />
                <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                    {{ $description }}
                </div>
            </div>
        </div>
    @elseif(!$hidden)
        {{-- <x-input-label value="{{ $label }}" /> --}}
        <span class="block font-medium text-sm text-background-700 dark:text-background-300">{{ $label }}</span>
    @endif
</div>
<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .toggle-switch.hidden {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 17px;

    }

    .toggle-switch-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 17px;
    }

    input:checked+.toggle-switch-slider {
        background-color: var(--primary-500);
        border-radius: 17px;

    }

    input:focus+.toggle-switch-slider {
        box-shadow: 0 0 1px var(--primary-500);

    }

    input:checked+.toggle-switch-slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    .toggle-switch-label {
        margin-left: 10px;
    }
</style>
