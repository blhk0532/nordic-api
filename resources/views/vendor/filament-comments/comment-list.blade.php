<div>
    @foreach ($this->comments as $comment)
        <livewire:filament-comments::comment
            :key="'comment-' . $comment->id"
            :comment="$comment"
            :mentionables="$mentionables"
        />
    @endforeach
</div>
