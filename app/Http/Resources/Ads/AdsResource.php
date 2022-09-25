<?php

namespace App\Http\Resources\Ads;

use App\Models\ActiveStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class AdsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $countReviews = $this->reviews?->count() * 5;
        $sumRating = $this->reviews?->sum('rating');

        $totalRating = ($sumRating > 0) ? round(($sumRating / $countReviews) * 5, 1) : 5.0;

        return [
            'id'           => $this->id,
            'category'     => $this->whenLoaded('category'),
            'sub_category' => $this->whenLoaded('subCategory'),
            'seller'       => $this->whenLoaded('seller'),
            'sort_options' => $this->whenLoaded('sortOptions'),
            'name'         => ucfirst($this->name),
            'slug'         => $this->slug,
            'description'  => ucfirst($this->description),
            'price'        => $this->price,
            'reviews'      => $this->whenLoaded('reviews'),
            'pictures'     => $this->whenLoaded('pictures'),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'total_rating' => $totalRating,
        ];
    }
}