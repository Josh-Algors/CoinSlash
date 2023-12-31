<?php

namespace Modules\Location\Http\Controllers;

use App\Http\Services\LocationService;
use App\Models\Country;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function getAllCountries()
    {
        try{
            $result = LocationService::getAllCountries();
        }catch(\Exception $e){
            Log::error($e);
            return response(['status' => false, 'message' => 'Unable to fetch record', 'data'=>false], 422);
        }
        return response(['status' => true, 'message' => 'Success', 'data'=>$result], Response::HTTP_OK);
    }

    public function getCountry($country_id)
    {
        try{
            $result = LocationService::getSpecificCountry($country_id);
            $result['states'] = $result->states;
        }catch(\Exception $e){
            Log::error($e);
            return response(['status' => false, 'message' => 'Unable to fetch record', 'data'=>false], 422);
        }
        return response(['status' => true, 'message' => 'Success', 'data'=>$result], Response::HTTP_OK);
    }

    public function getState($state_id)
    {
        try{
            $result = LocationService::getSpecificState($state_id);
            $result['country'] = $result->country;
            $result['cities'] = $result->cities;
        }catch(\Exception $e){
            Log::error($e);
            return response(['status' => false, 'message' => 'Unable to fetch record', 'data'=>false], 422);
        }
        return response(['status' => true, 'message' => 'Success', 'data'=>$result], Response::HTTP_OK);
    }
    
}
