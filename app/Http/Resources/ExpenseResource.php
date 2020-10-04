<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'quantity' => $this->quantity,
            'date' => $this->date->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'activity_id' => $this->activity_id,
            'activity' => new ActivityResource($this->whenLoaded('activity')),
        ];
    }
}
