<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SafeBrowsing;
use App\Models\Shorturl;
use App\Http\Requests\UrlStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


/**
 * UrlShortnerController
 */
class UrlShortnerController extends Controller
{
    
    /**
     * handleShortUrl
     *
     * @param  mixed $request
     * @param  mixed $safeBrowsing
     * @return void
     */
    public function handleShortUrl(Request $request, SafeBrowsing $safeBrowsing)
    {
        $mainUrl = $request->input('mainUrl');
       //validate request data
        $validator = Validator::make($request->all(), [
            'mainUrl' => 'required|url',
        ]);
        if ($validator->fails()) {    
            return response()->json($validator->messages(), 422);
        }
        //Check for safe browsing
        $safeUrl = $safeBrowsing->isSafeUrl($mainUrl);
        if(!$safeUrl){
            $error = [
                'mainUrl' => ['Please provide safe browsing url']
            ];
           return response()->json($error, 422);
        }
        //Check for dublicate url
        $existUrl = Shorturl::where('main_url', '=', $mainUrl)->first();
        if($existUrl){
            $error = [
                'mainUrl' => ['This url is already exist.'],
                'shortUrl' => [$existUrl->new_url],
            ];
           return response()->json($error, 422);
        }

        //create short url
        $shortUrl = url('/').'/u/'.Str::random(6);
        //Store main and short url
        $shorturl = new Shorturl();
        $shorturl->main_url = $mainUrl;
        $shorturl->new_url = $shortUrl;
        if($shorturl->save()){

           return $shortUrl;
        }
    }
    
    /**
     * handleRedirect
     *
     * @param  mixed $request
     * @param  mixed $url
     * @return void
     */
    public function handleRedirect(Request $request, $url)
    {
        $uri = $_SERVER['REQUEST_URI'];
        if($uri == ''){
            return abort(404);
        }
        $url = Shorturl::where('new_url', 'like', '%'.$uri.'%')->get('main_url');
        if($url == '' || count($url) == 0){
            return abort(404);
        } else {
            return redirect($url[0]['main_url']);
        }

    }
}
