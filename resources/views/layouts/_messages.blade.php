<?php foreach(session("messages") ?? [] as $message): ?>

<div class="my-3 alert alert-{{ $message['state'] ?? \App\Constants\MessageState::STATE_INFO }}">
    @switch($message['state'] ?? 'primary')
        @case(\App\Constants\MessageState::STATE_INFO)
        <i class="fas fa-info-circle"></i>
        @break
        @case(\App\Constants\MessageState::STATE_SUCCESS)
        <i class="fas fa-check-circle"></i>
        @break
        @case(\App\Constants\MessageState::STATE_WARNING)
        <i class="fas fa-exclamation-circle"></i>
        @break
        @case(\App\Constants\MessageState::STATE_DANGER)
        <i class="fas fa-times-circle"></i>
        @break
    @endswitch
    {{ $message['content'] ?? 'Default message content.' }}
</div>

<?php endforeach; ?>
