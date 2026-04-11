<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Models;

use AdultDate\FilamentWirechat\Database\Factories\MessageFactory;
use AdultDate\FilamentWirechat\Enums\Actions;
use AdultDate\FilamentWirechat\Enums\MessageType;
use AdultDate\FilamentWirechat\Models\Scopes\WithoutRemovedMessages;
use AdultDate\FilamentWirechat\Traits\Actionable;
use Adultdate\Wirechat\Facades\Wirechat;
use Adultdate\Wirechat\Helpers\Helper;
use Eloquent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int|null $conversation_id
 * @property int $sendable_id
 * @property string $sendable_type
 * @property int|null $reply_id
 * @property string|null $body
 * @property MessageType $type
 * @property Carbon|null $kept_at filled when a message is kept from disappearing
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Action> $actions
 * @property-read int|null $actions_count
 * @property-read Attachment|null $attachment
 * @property-read Conversation|null $conversation
 * @property-read Message|null $parent
 * @property-read Message|null $reply
 * @property-read Model|Eloquent $sendable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereIsNotOwnedBy(\Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereKeptAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereReplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereSendableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereSendableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Message withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Message withoutTrashed()
 *
 * @mixin Eloquent
 */
class Message extends Model
{
    use Actionable;
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'body',
        'sendable_type',
        'sendable_id',
        'conversation_id',
        'reply_id',
        'type',
        'kept_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    protected $casts = [
        'type' => MessageType::class,
        'kept_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Wirechat::formatTableName('messages');

        parent::__construct($attributes);
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        if (! $this->table) {
            $this->table = Wirechat::formatTableName('messages');
        }

        return $this->table;
    }

    /**
     * Get a new query builder instance for the model.
     *
     * @return Builder
     */
    public function newQuery()
    {
        // Ensure table is set before creating query builder
        if (! $this->table) {
            $this->table = Wirechat::formatTableName('messages');
        }

        return parent::newQuery();
    }

    /* relationship */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /* Polymorphic relationship for the sender */
    public function sendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convenience relationship for User (assuming sendable is User).
     */
    public function user(): MorphTo
    {
        return $this->sendable();
    }

    /**
     * Relationship to the Participant model.
     *
     * For FilamentWirechat, messages use sendable (polymorphic to User),
     * but we need to find the participant that corresponds to the sendable
     * in this conversation.
     *
     * Note: This relationship uses a simple belongsTo on conversation_id.
     * The actual participant matching is handled by the accessor below
     * to support both single access and eager loading.
     */
    public function participant(): BelongsTo
    {
        // Return a simple relationship - the accessor will handle the actual matching
        return $this->belongsTo(Participant::class, 'conversation_id', 'conversation_id');
    }

    /**
     * Get the participant for this message.
     * This accessor ensures the participant is loaded correctly by matching
     * the message's sendable with the participant's participantable.
     */
    public function getParticipantAttribute(): ?Participant
    {
        // If already loaded via relationship, check if it matches
        $loaded = $this->getRelationValue('participant');
        if ($loaded && $loaded->participantable_id === $this->sendable_id
            && $loaded->participantable_type === $this->sendable_type) {
            return $loaded;
        }

        // Otherwise, load it manually based on sendable
        if ($this->conversation_id && $this->sendable_id && $this->sendable_type) {
            $participant = Participant::where('conversation_id', $this->conversation_id)
                ->where('participantable_id', $this->sendable_id)
                ->where('participantable_type', $this->sendable_type)
                ->first();

            $this->setRelation('participant', $participant);

            return $participant;
        }

        return null;
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function hasAttachment(): bool
    {
        return $this->attachment()->exists();
    }

    public function isAttachment(): bool
    {
        return $this->type === MessageType::ATTACHMENT;
    }

    /**
     * Check if the message has been read by a specific user.
     */
    public function readBy(Model|Participant $user): bool
    {
        if ($user instanceof Participant) {
            $user = $user->participantable;
        }

        return $this->conversation->getUnreadCountFor($user) <= 0;
    }

    /**
     * Check if the message is owned by user
     */
    public function ownedBy($user): bool
    {
        if (! $user || ! ($user instanceof Model)) {
            return false;
        }

        $userMorphClass = is_object($user) && method_exists($user, 'getMorphClass') ? $user->getMorphClass() : (is_object($user) ? get_class($user) : null);

        return $this->sendable_type === $userMorphClass && $this->sendable_id === (is_object($user) && method_exists($user, 'getKey') ? $user->getKey() : null);
    }

    public function belongsToAuth(): bool
    {
        $user = Auth::user();

        if (! is_object($user)) {
            return false;
        }

        // Prefer Eloquent Model methods when available to satisfy static analysis
        if ($user instanceof Model) {
            $userMorphClass = $user->getMorphClass();
            $userKey = $user->getKey();
        } else {
            $userMorphClass = get_class($user);
            $userKey = method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null;
        }

        return $this->sendable_type === $userMorphClass && $this->sendable_id === $userKey;
    }

    // Relationship for the parent message
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_id')->withoutGlobalScope(WithoutRemovedMessages::class)->withTrashed();
    }

    // Relationship for the reply
    public function reply(): HasOne
    {
        return $this->hasOne(self::class, 'reply_id');
    }

    // Method to check if the message has a reply
    public function hasReply(): bool
    {
        return $this->reply()->exists();
    }

    // Method to check if the message has a parent
    public function hasParent(): bool
    {
        return $this->parent()->exists();
    }

    public function scopeWhereIsNotOwnedBy($query, Model|Authenticatable $user)
    {

        $query->where(function ($query) use ($user) {
            $query->where('sendable_id', '<>', $user->getKey())
                ->orWhere('sendable_type', '<>', $user->getMorphClass());
        });

        // $query->where(function ($query) use ($user) {
        //     $query->whereNot('sendable_id', $user->id)
        //           ->orWhereNot('sendable_type', $user->getMorphClass());
        // });

    }

    /**
     * Delete for
     * This will delete the message only for the auth user meaning other participants will be able to see it
     *
     * @return bool|null
     */
    public function deleteFor(Model|Authenticatable $user)
    {

        $conversation = $this->conversation;

        // Make sure auth belongs to conversation for this message
        abort_unless($user->belongsToConversation($conversation), 403);

        // If conversation is self, then delete permanently directly
        if ($conversation->isSelf()) {
            return $this->forceDelete();

        }

        // Try to create an action
        $this->actions()->create([
            'actor_id' => $user->getKey(),
            'actor_type' => $user->getMorphClass(),
            'type' => Actions::DELETE,
        ]);

        // If it's a private conversation (only 2 users), then check if both users have deleted the message
        if ($conversation->isPrivate()) {

            // Eager load particiapnts
            $conversation->loadMissing('participants.participantable');
            $deletedByBothParticipants = true;

            foreach ($conversation->participants as $participant) {
                $deletedByBothParticipants = $deletedByBothParticipants &&
                    $this->actions()
                        ->whereActor($participant->participantable)
                        ->where('type', Actions::DELETE)
                        ->exists();
            }

            if ($deletedByBothParticipants) {
                return $this->forceDelete();
            }
        }

        return null;
    }

    /**
     * Deleting message for everyone   */
    public function deleteForEveryone(Model $user): void
    {

        $conversation = $this->conversation;
        $participant = $conversation->participant($user);
        $message = $this;

        // Make sure auth belongs to conversation for this message
        abort_unless($user->belongsToConversation($conversation), 403, 'You do not belong to this conversation');

        // make sure user owns message OR allow if is admin in group
        abort_unless($message->ownedBy($user) || ($participant->isAdmin() && $message->conversation->isGroup()), 403, 'You do not have permission to delete this message');

        // if message has reply then only-soft delete it
        if ($message->hasReply()) {
            $message->delete();
        } else {

            $message->forceDelete();
        }

    }

    /**
     * Check if the message body contains only emojis.
     */
    public function isEmoji(): bool
    {
        if ($this->body === null) {
            return false;
        }

        // Use the isEmoji helper method to check if the message body contains only emojis
        return Helper::isEmoji($this->body);
    }

    /**
     * since you have a non-standard namespace;
     * the resolver cannot guess the correct namespace for your Factory class.
     * so we exlicilty tell it the correct namespace
     */
    protected static function newFactory()
    {
        return MessageFactory::new();
    }

    protected static function booted()
    {
        // Add scope if authenticated
        self::addGlobalScope(WithoutRemovedMessages::class);

        // listen to deleted
        self::deleted(function ($message) {

            if ($message->attachment?->exists()) {

                // delete attachment
                $message->attachment->delete();

            }

            // Use a DB transaction to ensure atomicity
            DB::transaction(function () use ($message) {
                // Delete associated actions (polymorphic actionable relation)
                $message->actions()->delete();
            });
        });
    }
}
