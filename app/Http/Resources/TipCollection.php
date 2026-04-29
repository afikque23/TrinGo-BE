<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TipCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tips' => TipResource::collection($this->collection),
            'pagination' => [
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'total_items' => $this->total(),
                'items_per_page' => $this->perPage(),
                'has_next' => $this->hasMorePages(),
                'has_prev' => $this->currentPage() > 1,
            ],
        ];
    }
}
