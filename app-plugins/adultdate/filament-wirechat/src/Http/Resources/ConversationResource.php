<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Http\Resources;

use AdultDate\FilamentWirechat\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'group' => $this->whenLoaded('group', fn () => new GroupResource($this->group)),
            'is_group' => $this->isGroup(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
