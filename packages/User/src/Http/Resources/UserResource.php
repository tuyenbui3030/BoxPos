<?php

namespace Packages\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 * 
 * Transforms user model data for API responses.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->when($this->email_verified_at, $this->email_verified_at?->toISOString()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Include relationships when loaded
            'devices' => $this->whenLoaded('devices'),
            'profile' => $this->whenLoaded('profile'),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
