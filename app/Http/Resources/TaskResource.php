<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'status'       => $this->status->value,
            'status_label' => $this->status->label(),
            'priority'     => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'due_date'     => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'is_completed' => $this->isCompleted(),
            'is_overdue'   => $this->isOverdue(),
            'category' => $this->whenLoaded('category', function () {
                return $this->category ? [
                    'id'    => $this->category->id,
                    'name'  => $this->category->name,
                    'color' => $this->category->color,
                    'icon'  => $this->category->icon,
                ] : null;
            }),
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}