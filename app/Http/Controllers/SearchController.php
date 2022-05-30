<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Auction;
use App\Models\Category;

class SearchController extends Controller {
    /**
     * Display all auctions in search.
     *
     * @return Response
     */
    public function showAll()
    {
        $auctions_to_display = Auction::where('start_date', '<=', Carbon::now()->toDateTimeString())
                                        ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                        ->where('type', 'Public');
        
        return view('pages.search', [
            'auctions_to_display' => $auctions_to_display->paginate(12),
            'textSearch' => "",
            'conditions' => $auctions_to_display->get()->pluck('condition')->unique()->filter(function($value){
                return $value != null;
            }),
            'brands' => $auctions_to_display->get()->pluck('brand')->unique()->filter(function($value){
                return $value != null;
            }),
            'colours' => $auctions_to_display->get()->pluck('colour')->unique()->filter(function($value){
                return $value != null;
            }),
            'years' => $auctions_to_display->get()->pluck('year')->unique()->filter(function($value){
                return $value != null;
            }),
            'categories' => Category::whereIn('auction_id',$auctions_to_display->get()->pluck('id')->all())->pluck('category')->unique(),
        ]);
    }

    /**
     * Display result auctions by filtered search.
     *
     * @param  Request $request
     * @return Response
     */
    public function showFiltered(Request $request)
    {

        if (!is_null($request->navIdentifier)) {
            return SearchController::showTextSearch($request);
        }

        $validator = Validator::make($request->all(), [
            'textSearch' => 'nullable|max:255',
            'conditions' => 'nullable',
            'brands'     => 'nullable',
            'colours'    => 'nullable',
            'years'      => 'nullable',
            'categories' => 'nullable',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $auctions_active  =  Auction::where('start_date', '<=', Carbon::now()->toDateTimeString())
                                    ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                    ->where('type', 'Public');

        $auctions_search  =  Auction::where('start_date', '<=', Carbon::now()->toDateTimeString())
                                    ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                    ->whereRaw('tsvectors @@ plainto_tsquery(\'english\', ?)', [$request->textSearch])
                                    ->where('type', 'Public');

        $auctions_query = ($request->textSearch == "") ? $auctions_active :  $auctions_search;

        if (!is_null($request->conditions)) {
            $auctions_query = $auctions_query->whereIn('condition', $request->conditions);
        }

        if (!is_null($request->brands)) {
            $auctions_query = $auctions_query->whereIn('brand', $request->brands);
        }

        if (!is_null($request->colours)) {
            $auctions_query = $auctions_query->whereIn('colour', $request->colours);
        }

        if (!is_null($request->years)) {
            $auctions_query = $auctions_query->whereIn('year', $request->years);
        }

        if (!is_null($request->categories)) {
            $auctions_query = $auctions_query->whereIn('id', Category::whereIn('auction_id', $auctions_query->get()->map->only(['id']))
                                                                       ->whereIn('category', $request->categories)->get()->map->only(['auction_id']));
        }


        $auctions_to_display = $auctions_query->get();


        return view('pages.search', [
            'auctions_to_display' => $auctions_query->paginate(12),
            'textSearch' => $request->textSearch,
            'conditions' => $auctions_to_display->pluck('condition')->unique()->filter(function($value) {
                return $value != null;
            }),
            'brands' => $auctions_to_display->pluck('brand')->unique()->filter(function($value) {
                return $value != null;
            }),
            'colours' => $auctions_to_display->pluck('colour')->unique()->filter(function($value) {
                return $value != null;
            }),
            'years' => $auctions_to_display->pluck('year')->unique()->filter(function($value) {
                return $value != null;
            }),
            'categories' => Category::whereIn('auction_id',$auctions_to_display->pluck('id')->all())->pluck('category')->unique(),
        ]);
    }

    /**
     * Display result auctions by text search.
     *
     * @param  Request $request
     * @return Response
     */
    public function showTextSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'textSearch' => 'nullable|max:255',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $auctions_active  =  Auction::where('start_date', '<=', Carbon::now()->toDateTimeString())
                                    ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                    ->where('type', 'Public');

        $auctions_search  =  Auction::where('start_date', '<=', Carbon::now()->toDateTimeString())
                                    ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                    ->whereRaw('tsvectors @@ plainto_tsquery(\'english\', ?)', [$request->textSearch])
                                    ->where('type', 'Public');

        $auctions_to_display = ($request->textSearch == "") ? $auctions_active :  $auctions_search;

        return view('pages.search', [
            'auctions_to_display' => $auctions_to_display->paginate(12),
            'textSearch' => $request->textSearch,
            'conditions' => $auctions_to_display->get()->pluck('condition')->unique()->filter(function($value){
                return $value != null;
            }),
            'brands' => $auctions_to_display->get()->pluck('brand')->unique()->filter(function($value){
                return $value != null;
            }),
            'colours' => $auctions_to_display->get()->pluck('colour')->unique()->filter(function($value){
                return $value != null;
            }),
            'years' => $auctions_to_display->get()->pluck('year')->unique()->filter(function($value){
                return $value != null;
            }),
            'categories' => Category::whereIn('auction_id',$auctions_to_display->get()->pluck('id')->all())->pluck('category')->unique(),
        ]);
    }
}