<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Http\Resources;

use Adultdate\Wirechat\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class GroupResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'name' => $this->name,
            'cover_url' => $this->cover_url,
        ];
    }
}
