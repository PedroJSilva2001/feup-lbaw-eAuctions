<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use \Carbon\Carbon;

use App\Models\Auction;
use App\Models\Category;

class HomepageController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show() : View
    {
        $nItems = 3;
        $trendingCategories = $this->getTrendingCategories($nItems);
        $trendingAuctionsForHomepage = $this->getTrendingInProgressAuctions($nItems);
        $pastAuctionsForHomepage = $this->getRandomPastAuctions($nItems);

        return view('pages.homepage', [
            'trending_auctions' => $trendingAuctionsForHomepage,
            'trending_categories' => $trendingCategories,
            'past_auctions' => $pastAuctionsForHomepage
        ]);
    }

    /**
     * Gets a given number of the most trending categories, based on number of auctions of each category.
     * 
     * @param  int $nCategories
     * @return array
     */
    private function getTrendingCategories($nCategories) {
        $categoriesAll = ['Art', 'Technology', 'Books', 'Automobilia', 'Coins & Stamps', 'Music', 'Toys', 'Fashion'];
        $categoriesNr = [];

        foreach ($categoriesAll as $name) {
            $c = Category::where("category", "=", $name)->first();
            array_push($categoriesNr, [$name, $c->getNumberAuctions()]);
        }
        usort($categoriesNr, fn ($a, $b) => $b[1] - $a[1]);

        $trendingCategories = [];

        foreach ($categoriesNr as $result) {
            if (count($trendingCategories) == $nCategories) break;
            array_push($trendingCategories, $this->getCategoryInfo($result[0]));
        }

        return $trendingCategories;
    }

    /**
     * Gets information of a given category (Name, image and color).
     *
     * @return array
     */
    private function getCategoryInfo($category) {
        switch ($category) {
            case 'Art':
                return ['Art', 'category_pictures/art.jpg', '#A98B98'];
            case 'Technology':
                return ['Technology', 'category_pictures/technology.jpg', '#89CFF0'];
            case 'Books':
                return ['Books', 'category_pictures/books.jpg', '#48bf53'];
            case 'Automobilia':
                return ['Automobilia', 'category_pictures/automobilia.jpg', '#ffdbd0'];
            case 'Coins & Stamps':
                return ['Coins & Stamps', 'category_pictures/coins.jpg', '#F65C78'];
            case 'Music':
                return ['Music', 'category_pictures/music.jpg', '#E9C891'];
            case 'Toys':
                return ['Toys', 'category_pictures/toys.jpg', '#FF7F3F'];
            case 'Fashion':
                return ['Fashion', 'category_pictures/fashion.jpg', '#F6D860'];
        }
    }

    /**
     * Gets a given number of the most trending ongoing auctions, based on number of bids of each auction.
     *
     * @param  int $nAuctions
     * @return array
     */
    private function getTrendingInProgressAuctions($nAuctions)  
    {

        $publicOngoingAuctions =  Auction::where('auction.end_date', '>', Carbon::now()->toDateString())
                                         ->where('auction.start_date', '<', Carbon::now()->toDateString())
                                         ->where('auction.type', '=', 'Public')->get();

        $auctionsBids = [];

        foreach ($publicOngoingAuctions as $a) {
            array_push($auctionsBids, [$a, $a->getNumberBids()]);
        }
        usort($auctionsBids, fn ($a, $b) => $b[1] - $a[1]);

        $trendingAuctions = [];

        foreach ($auctionsBids as $result) {
            if (count($trendingAuctions) == $nAuctions) break;
            array_push($trendingAuctions, $result[0]);
        }

        return $trendingAuctions;
    }

    /**
     * Gets a given number of random past auctions.
     *
     * @param  int $nAuctions
     * @return Collection
     */
    private function getRandomPastAuctions($nAuctions) : Collection 
    {
        $value = Auction::inRandomOrder()->limit($nAuctions)->where('auction.end_date', '<', Carbon::now()->toDateString());
        return $value->where('auction.start_date', '!=', null)->get();
    }
    
}