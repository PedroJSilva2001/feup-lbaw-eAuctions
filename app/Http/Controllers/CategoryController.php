<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use \Carbon\Carbon;

use App\Models\Category;
use App\Models\Auction;

class CategoryController extends Controller{
    /**
     * Shows the category page of a given category.
     *
     * @param int $category
     * @return Response
     */
    public function show($category) : View {

        $category_db = ucwords(str_replace('-', ' & ', $category));

        $auctions_to_display = Auction::whereIn('id', Category::where("category", $category_db)->get()->map->only(['auction_id']))
                                        ->where('start_date', '<=', Carbon::now()->toDateTimeString())
                                        ->where('end_date', '>=', Carbon::now()->toDateTimeString())
                                        ->where('type', 'Public')
                                        ->get();

        return view('pages.category', [
            'auctions_to_display' => $auctions_to_display,
            'category' => $category_db
        ]);
    } 
}
