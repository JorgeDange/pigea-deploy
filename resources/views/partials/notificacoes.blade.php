@props(['itens' => []])

<div class="notification-list">
@if (count($itens) === 0)
    <div class="notification-item">
        <div class="notification-content"><p>Sem atividade recente.</p></div>
    </div>
@else
    @foreach ($itens as $item)
        <a class="notification-item {{ $item['lida'] ? '' : 'unread' }}" href="{{ $item['href'] ?? '#' }}" aria-label="{{ trim($item['titulo'].' — '.$item['texto']) }}">
            <div class="notification-icon"><i class="fa {{ $item['icone'] }}" aria-hidden="true"></i></div>
            <div class="notification-content">
                <p>
                    @unless ($item['lida'])<span class="notif-badge-nova">Nova</span>@endunless
                    <strong>{{ $item['titulo'] }}</strong> — {{ $item['texto'] }}
                </p>
            </div>
            <div class="notification-time">{{ $item['tempo'] }}</div>
        </a>
    @endforeach
@endif
</div>
