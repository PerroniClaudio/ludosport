@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
            @else
                <img src={{url('warriors')}} class="logo" alt="Logo" style="width: 100px; height: 100px; max-height: 100px; max-width: 100px;">
            @endif
        </a>
    </td>
</tr>
