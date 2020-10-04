<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'name' => $this->name,
            'start' => $this->when($this->start, (function (): string {
                return $this->start->format('Y-m-d H:i:s');
            })->bindTo($this)),
            'finish' => $this->when($this->finish, (function (): string {
                return $this->finish->format('Y-m-d H:i:s');
            })->bindTo($this)),
            'owner_id' => $this->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
        ];
    }
}
