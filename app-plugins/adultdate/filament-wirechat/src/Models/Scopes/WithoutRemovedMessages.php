<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Models\Scopes;

use AdultDate\FilamentWirechat\Enums\Actions;
use AdultDate\FilamentWirechat\Models\Message;
use AdultDate\FilamentWirechat\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WithoutRemovedMessages implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * This scope filters out messages that are considered "removed" for the authenticated user.
     * "Removed" messages can be those that are deleted, cleared, or otherwise excluded based on
     * user-specific actions or participant conditions.
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $messagesTableName = (new Message)->getTable();
        $participantTableName = (new Participant)->getTable();

        if (auth()->check()) {
            $user = auth()->user();

            $builder->whereDoesntHave('actions', function ($q) use ($user) {
                $q->where('actor_id', $user->id)
                    ->where('actor_type', $user->getMorphClass())
                    ->where('type', Actions::DELETE);
            })
                ->where(function ($query) use ($user, $messagesTableName, $participantTableName) {
                    $query->whereHas('conversation.participants', function ($q) use ($user, $messagesTableName, $participantTableName) {
                        $q->where('participantable_id', $user->id)
                            ->where('participantable_type', $user->getMorphClass())
                            ->where(function ($q) use ($messagesTableName, $participantTableName) {
                                $q->orWhere(function ($q) {
                                    $q->whereNull('conversation_cleared_at')
                                        ->whereNull('conversation_deleted_at');
                                })
                                    ->orWhere(function ($query) use ($messagesTableName, $participantTableName) {
                                        $query->whereColumn("$messagesTableName.created_at", '>', "$participantTableName.conversation_cleared_at")
                                            ->orWhereColumn("$messagesTableName.created_at", '>', "$participantTableName.conversation_deleted_at");
                                    });
                            });
                    });
                });
        }
    }
}
