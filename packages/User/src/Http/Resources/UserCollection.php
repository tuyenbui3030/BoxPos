<?php

namespace Packages\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * User Collection Resource
 * 
 * Transforms user collection data for API responses.
 */
class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'active_count' => $this->collection->where('is_active', true)->count(),
                'inactive_count' => $this->collection->where('is_active', false)->count(),
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
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
            'links' => [
                'self' => $request->url(),
            ],
        ];
    }
}
