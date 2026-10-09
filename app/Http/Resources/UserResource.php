<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_platform_admin' => (bool) $this->is_platform_admin,
            'workshop' => $this->whenLoaded('workshop', fn () => $this->workshop
                ? ['id' => $this->workshop->id, 'name' => $this->workshop->name]
                : null),
        ];
    }
}
