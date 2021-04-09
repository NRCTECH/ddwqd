<?php

namespace App\Modules\Api\V1\Services;

use App\Exceptions\CustomApiErrorResponseHandler;
use App\Modules\Api\ApiUtility;
use App\Modules\Api\V1\Models\ActiveStatus;
use App\Modules\Api\V1\Models\Ads;
use App\Modules\Api\V1\Models\AdsSortOption;
use App\Modules\Api\V1\Repositories\AdsRepository;
use App\Modules\Api\V1\Models\File;
use App\Modules\Api\V1\Models\SortOption;
use App\Modules\Api\V1\Models\SubCategorySortOption;
use App\Modules\Api\V1\Resources\AdsResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdsService implements AdsRepository
{
    public function index()
    {
        return AdsResource::collection(Ads::where('active_status', ActiveStatus::ACTIVE)->get());
    }

    public function myAds(array $data)
    {
        $user_id = $data['auth_user']->id;

        return AdsResource::collection(
            Ads::where(
                [
                    'seller_id' => $user_id,
                    'active_status' => ActiveStatus::ACTIVE
                ]
            )->get()
        );
    }

    public function post(array $data)
    {
        $seller_id = $data['auth_user']->id;

        $ads = Ads::where(
            [
                'name' => $data['name'],
                'seller_id' => $seller_id,
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'],
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->first();
        
        if ($ads) {
            throw new CustomApiErrorResponseHandler("You have posted this ads before. Try using a different name");
        }

        $ads = new Ads();
        $ads->category_id = $data['category_id'];
        $ads->sub_category_id = $data['sub_category_id'];
        $ads->seller_id = $seller_id;
        $ads->name = $data['name'];
        $ads->description = $data['description'];
        $ads->price = $data['price'];
        $ads->save();

        return [
            'ads' => $ads,
            'message' => 'Ads successfully added.'
        ];
    }

    public function addSortOptions(int $id, array $data)
    {
        $ads = Ads::where(
            [
                'id' => $id,
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->first();

        $all_sort_options = $data['sort_options'];
        $added_sort_options = [];

        if (count($all_sort_options) > 0) {
            foreach ($all_sort_options as $sort_options) {
                foreach ($sort_options as $sort_option => $value) {
                    $sort_option_exists = SubCategorySortOption::where(
                        [
                            'sub_category_id' => $ads->sub_category_id,
                            'sort_option_id' => $sort_option,
                            'active_status' => ActiveStatus::ACTIVE
                        ]
                    )->first();
                    
                    $ads_sort_option_exists = AdsSortOption::where(
                        [
                            'ads_id' => $ads->id,
                            'sort_option_id' => $sort_option,
                            'active_status' => ActiveStatus::ACTIVE
                        ]
                    )->exists();

                    if ($sort_option_exists && !$ads_sort_option_exists) {
                        $sort_option_name = SortOption::where(
                            [
                                'id' => $sort_option,
                                'active_status' => ActiveStatus::ACTIVE
                            ]
                        )->value('name');
                        
                        $value_exists = DB::table($sort_option_name)->where(
                            [
                                'id' => $value,
                                'active_status' => ActiveStatus::ACTIVE
                            ]
                        )->exists();

                        if ($value_exists) {
                            AdsSortOption::create(
                                [
                                    'ads_id' => $ads->id,
                                    'sort_option_id' => $sort_option,
                                    'value' => $value
                                ]
                            );

                            $added_sort_options[] = str_replace("_", " ", ucwords($sort_option_name));
                        }
                    }
                }
            }

            if (count($added_sort_options) > 0) {
                return [
                    'message' => count($added_sort_options).' sort option(s) successfully added to ads: '.$ads->name,
                    'added_sort_options' => implode(", ", $added_sort_options)
                ];
            }

            throw new CustomApiErrorResponseHandler("No sort option added to ads: ".$ads->name);
        }

        throw new CustomApiErrorResponseHandler("No sort option added to ads: ".$ads->name);
    }
    
    public function updateAds(int $id, array $data)
    {
        $ads = Ads::where(
            [
                'id' => $id,
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->first();
        
        if (!$ads) {
            throw new CustomApiErrorResponseHandler("Ads not found.");
        }

        return $ads;
    }

    public function details(int $id)
    {
        $ads = Ads::where(
            [
                'id' => $id,
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->first();
        
        if (!$ads) {
            throw new CustomApiErrorResponseHandler("Ads not found.");
        }

        return new AdsResource($ads);
    }

    public function deleteSortOption(int $ads_id, int $sort_option_id)
    {
        $ads = Ads::where(
            [
                'id' => $ads_id,
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->first();
        
        if (!$ads) {
            throw new CustomApiErrorResponseHandler("Ads not found.");
        }

        $delete_sort_option = AdsSortOption::where(
            [
                'id' => $sort_option_id,
                'ads_id' => $ads_id,
                'active_status' => ActiveStatus::ACTIVE
            ]
        )->update(['active_status' => ActiveStatus::DELETED]);

        if (!$delete_sort_option) {
            throw new CustomApiErrorResponseHandler("Ads sort option not deleted. Try again later.");
        }

        return [
            'message' => 'Ads sort option successfully deleted'
        ];
    }
}
