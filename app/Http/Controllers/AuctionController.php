<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Follow;
use App\Models\User;
use App\Models\Category;
use App\Models\Image;
use App\Models\Rating;
use App\Models\Notification;
use App\Models\Comment;

class AuctionController extends Controller
{
    /**
     * Shows the auction for a given id.
     *
     * @param int $id
     * @return Response
     */
    public function show($id) 
    {
        $auction = Auction::findOrFail($id);    

        if ($auction->isDraft()) {
            if (Auth::guest() || (Auth::id() != $auction->seller_id && !Auth::user()->isadmin)) {
                return redirect('/');
            }
        }

        $comments = Comment::where('auction_id', $id)->orderBy('date', 'ASC')->get();
        
        return view('pages.auction', [
            'auction' => $auction, 
            'comments' => $comments
        ]);
    }

    /**
     * Show create new auction form.
     *
     * @return Response
     */
    public function showCreateForm() 
    {
        return view('pages.create_auction', []);
    }

    /**
     * Show edit auction form.
     *
     * @param int $id
     * @return Response
     */
    public function showEditForm($id) {
        $auction = Auction::findOrFail($id);
        if (!Auth::guest() && (Auth::user()->isadmin || Auth::id() == $auction->seller_id) && 
            (Bid::where('auction_id', $id)->count() == 0 && $auction->getTimeDifference() != 'Cancelled')) {
            return view('pages.edit_auction', ['auction' => $auction]);
        } else {
            return redirect()->back();
        }
    }

    /**
     * Creates a new auction.
     * 
     * @param  Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        if (Auth::guest()) {
            return redirect('/');
        }

        $user = User::findOrFail(Auth::id());

        if ($user->isBlocked()) {
            return redirect()->back()->with('error', 'You can not create auctions while blocked');
        }

        if ($request->action == 'create') {
            $validator = Validator::make($request->all(),
            [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string|max:2000',
                'brand'         => 'nullable|string|max:255',
                'colour'        => 'nullable|string|max:255',
                'year'          => 'nullable|numeric',
                'condition'     => 'nullable|in:New,Mint,Reasonable,Poor',
                'end_date'      => 'required|date|after:'. Carbon::now()->addDays(1),
                'base_value'    => 'nullable|numeric',
                'pictures'      => 'required|array',
                'category'      => 'required|array',
            ]);
            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->with('error', 'Error in creating auction.')->withInput();
            }
        } else if ($request->action == 'draft') {
            $validator = Validator::make($request->all(),
            [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string|max:2000',
                'brand'         => 'nullable|string|max:255',
                'colour'        => 'nullable|string|max:255',
                'year'          => 'nullable|numeric',
                'condition'     => 'nullable|in:New,Mint,Reasonable,Poor',
                'end_date'      => 'nullable|date',
                'base_value'    => 'nullable|numeric',
                'pictures'      => 'nullable|array',
                'category'      => 'nullable|array',
            ]);
            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->with('error', 'Error in saving draft')->withInput();
            }
        }

        $auction = new Auction([
            'title'       => $request->get('title'),
            'seller_id'   => Auth::id(),
            'description' => $request->get('description'),
            'brand'       => $request->get('brand'),
            'colour'      => $request->get('colour'),
            'year'        => $request->get('year'),
            'condition'   => $request->get('condition'),
            'start_date'  => ($request->get('action') == 'create') ? Carbon::now() : null,
            'end_date'    => $request->get('end_date'),
            'base_value'  => ($request->get('base_value')) ? $request->get('base_value') : 1,
            'type'        => ($request->get('isPrivate') == 'on') ? 'Private' : 'Public'
        ]);
        $auction->save();

        if (!is_null($request->pictures)) {
            $directory = base_path('public/assets/auction_pictures/' . $auction->id);
            Storage::makeDirectory($directory);
            $pictures = $request->file('pictures');
            $num = 1;
            foreach ($pictures as $picture) {
                $fileNameExtension = $picture->extension();
                $fileName = $num . '.' . $fileNameExtension;
                $picture->move($directory, $fileName);
                
                $image = new Image([
                    'path'       => 'auction_pictures/' . $auction->id . '/' . $fileName,
                    'auction_id' => $auction->id,
                ]);
                $image->save();
                $num++;
            }
        }

        if (!is_null($request->category)) {
            $categories = $request->get('category');
            foreach ($categories as $category) {
                if (isset($category)){
                    $category_auction = new Category([
                        'auction_id' => $auction->id,
                        'category'   => $category
                    ]);
                    $category_auction->save();
                }
            }
        } 

        return redirect('/auctions/' . $auction->id);
    }

    /**
     * Edits an auction.
     * 
     * @param  int $id
     * @param  Request $request
     * @return Response
     */
    public function edit($id, Request $request)
    {
        $auction = Auction::where('id', $id);

        if ($request->action == 'cancel') {
            $auction->update(
                [
                    'end_date' => null,
                ]);

            $followers = User::wherein('id', 
                                Follow::where("auction_id", $id)->get()->map->only(['user_id'])
                            )->orWherein('id', Auction::where('id', $id)->get()->map->only(['seller_id']))->get();

            foreach ($followers as $follower) {
                $notification =  new Notification([
                    'user_id' => $follower->id,
                    'auction_id' => $id,
                    'date' => Carbon::now(),
                    'type' => 'Auction Cancelled',
                ]);
                $notification->save();
            }

            return redirect('/auctions/' . $id);
        }

        if ($request->action == 'save_auction') {
            $validator = Validator::make($request->all(),
            [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string|max:2000',
                'brand'         => 'nullable|string|max:255',
                'colour'        => 'nullable|string|max:255',
                'year'          => 'nullable|numeric',
                'condition'     => 'nullable|in:New,Mint,Reasonable,Poor',
                'end_date'      => 'required|date|after_or_equal:'. $auction->first()->end_date,
                'base_value'    => 'nullable|numeric',
                'pictures'      => 'nullable|array',
                'category'      => 'required|array',
            ]);

            if ($validator->fails() || (is_null($request->pictures) && $request->deleteOldPictures == 'on')) {
                $request->flash();
                return redirect()->back()->with('error', 'Error in editing auction.');
            }

            $auction->update(
            [
                    'title'       => $request->get('title'),
                    'description' => $request->get('description'),
                    'brand'       => $request->get('brand'),
                    'colour'      => $request->get('colour'),
                    'year'        => $request->get('year'),
                    'condition'   => $request->get('condition'),
                    'end_date'    => $request->get('end_date'),
                    'base_value'  => ($request->get('base_value')) ? $request->get('base_value') : 0,
                    'type'        => ($request->get('isPrivate') == 'on') ? 'Private' : 'Public',  
            ]);
        } 

        if ($request->action == 'save_draft') {
            $validator = Validator::make($request->all(),
            [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string|max:2000',
                'brand'         => 'nullable|string|max:255',
                'colour'        => 'nullable|string|max:255',
                'year'          => 'nullable|numeric',
                'condition'     => 'nullable|in:New,Mint,Reasonable,Poor',
                'end_date'      => 'nullable|date',
                'base_value'    => 'nullable|numeric',
                'pictures'      => 'nullable|array',
                'category'      => 'nullable|array',
            ]);

            if ($validator->fails()) {
                $request->flash();
                return redirect()->back()->with('error', 'Error in saving draft.');
            }

            $auction->update(
            [
                    'title'       => $request->get('title'),
                    'description' => $request->get('description'),
                    'brand'       => $request->get('brand'),
                    'colour'      => $request->get('colour'),
                    'year'        => $request->get('year'),
                    'condition'   => $request->get('condition'),
                    'end_date'    => $request->get('end_date'),
                    'base_value'  => ($request->get('base_value')) ? $request->get('base_value') : 0,
                    'type'        => ($request->get('isPrivate') == 'on') ? 'Private' : 'Public',  
            ]);
        } 

        if ($request->action == 'create') {
            $validator = Validator::make($request->all(),
            [
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string|max:2000',
                'brand'         => 'nullable|string|max:255',
                'colour'        => 'nullable|string|max:255',
                'year'          => 'nullable|numeric',
                'condition'     => 'nullable|in:New,Mint,Reasonable,Poor',
                'end_date'      => 'required|date|after:'. Carbon::now()->addDays(1),
                'base_value'    => 'nullable|numeric',
                'pictures'      => 'nullable|array',
                'category'      => 'required|array',
            ]);
            if ($validator->fails() || (is_null($request->pictures) && ($request->deleteOldPictures == 'on' || Image::where('auction_id', $id)->count() == 0))) {
                $request->flash();
                return redirect()->back()->with('error', 'Error in creating auction');
            }

            $auction->update([
                'title'       => $request->get('title'),
                'description' => $request->get('description'),
                'brand'       => $request->get('brand'),
                'colour'      => $request->get('colour'),
                'year'        => $request->get('year'),
                'condition'   => $request->get('condition'),
                'start_date'  => Carbon::now(),
                'end_date'    => $request->get('end_date'),
                'base_value'  => ($request->get('base_value')) ? $request->get('base_value') : 0,
                'type'        => ($request->get('isPrivate') == 'on') ? 'Private' : 'Public' ,
            ]);
        }

        if ($request->deleteOldPictures == 'on') {
            $directory = base_path('public/assets/auction_pictures/' . $id);
            File::deleteDirectory($directory);
            Image::where('auction_id', $id)->delete();
        }

        if (!is_null($request->pictures)) {
            $directory = base_path('public/assets/auction_pictures/' . $id);
            Storage::makeDirectory($directory);
            $pictures = $request->file('pictures');
            $num = Image::where('auction_id', $id)->count() + 1;
            foreach ($pictures as $picture) {
                $fileNameExtension = $picture->extension();
                $fileName = $num . '.' . $fileNameExtension;
                $picture->move($directory, $fileName);
                
                $image = new Image([
                    'path'       => 'auction_pictures/' . $id . '/' . $fileName,
                    'auction_id' => $id,
                ]);
                $image->save();
                $num++;
            }
        }

        if (!is_null($request->category)) {
            Category::where('auction_id', $id)->delete();
            $categories = $request->get('category');
            foreach ($categories as $category) {
                if (isset($category)){
                    $category_auction = new Category([
                        'auction_id' => $id,
                        'category'   => $category
                    ]);
                    $category_auction->save();
                }
            }
        } 

        return redirect('/auctions/' . $id);
    }

    /**
     * Handle a bidding process to an auction.
     * 
     * @param  Request $request
     * @param  int $auction_id
     * @return Response
     */
    public function bid(Request $request, int $auction_id) : RedirectResponse 
    {
        if (Auth::guest()) {
            abort(404);
        }

        $user = User::findOrFail(Auth::id());

        if ($user->isBlocked()) {
            return redirect()->back()->with('error', 'You can not bid in auctions while blocked');
        }

        $validator = Validator::make($request->all(),
                [
                'value' => 'required|numeric', 
                ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $bid = new Bid;
        $bid->id = $bid->getCount() + 1;
        $bid->user_id = Auth::id();
        $bid->auction_id = $auction_id;
        $bid->value = $request->get('value');

        try {
            $bid->save();
        } catch(\Exception $e) {
            return redirect()->back()->withErrors(['Invalid Value']);
        }

        $followers = User::wherein('id', 
                                        Follow::where("auction_id", $auction_id)->get()->map->only(['user_id'])
                                )->orWherein('id', Auction::where('id', $auction_id)->get()->map->only(['seller_id']))->get();

        foreach ($followers as $follower) {
            $notification =  new Notification([
                'user_id' => $follower->id,
                'bid_id' => $bid->id,
                'date' => Carbon::now(),
                'type' => 'New Bid',
            ]);
            $notification->save();
        }

        return redirect()->back();
    }  

    /**
     * Gets current highest bid from an auction.
     * 
     * @param  Request $request
     * @param  int $auction_id
     * @return JsonResponse or RedirectResponse
     */
    public function getCurrentBid(Request $request, int $auction_id)
    {
        $auction = Auction::findOrFail($auction_id);
        $currentBid = $auction->getCurrentHighestBid();

        if ($request->wantsJson()) {
            return response()->json($currentBid, 200);
        } else {
            return response()->json('', 415);
        }
    }

    /**
     * Handle a following process of an auction.
     * 
     * @param  Request $request
     * @return Response
     */
    public function followAuction(Request $request)
    {
        $auction_id = $request->route('id');
        if (Auth::guest()) {
            return redirect('/auctions/' . $auction_id);
        }

        $user_id = Auth::id();
        $followed_db = Follow::where('auction_id', $auction_id)->where('user_id', $user_id)->get();
        if ($followed_db->isEmpty()){
            $followed = new Follow([
                'auction_id' => $auction_id,
                'user_id' => $user_id,
            ]);
            $followed->save();
        }

        return redirect('/auctions/' . $auction_id);
    }

    /**
     * Handle an unfollowing process of an auction.
     * 
     * @param  Request $request
     * @return Response
     */
    public function unfollowAuction(Request $request)
    {
        $auction_id = $request->route('id');

        if (Auth::guest()) {
            return redirect('/auctions/' . $auction_id);
        }

        $user_id = Auth::id();
        $followed_db = Follow::where('auction_id', $auction_id)->where('user_id', $user_id)->first();
        if ($followed_db) {
            $followed_db->delete();
        }

        return redirect('/auctions/' . $auction_id);
    }

    /**
     * Handle a rating process of a seller.
     * 
     * @param  Request $request
     * @return Response
     */
    public function rateSeller(Request $request)
    {
        $auction_id = $request->route('id');
        if (Auth::guest()) {
            return redirect('/auctions/' . $auction_id);
        }

        $rater_id = Auth::id();
        $rated_id = Auction::findOrFail($auction_id)->seller_id;
        $rating_db = Rating::where('rater_id', $rater_id)->where('rated_id', $rated_id)->get();
        if ($rating_db->isEmpty()){
            $rating = new Rating([
                'rater_id' => $rater_id,
                'rated_id' => $rated_id,
                'score'    => $request->get('score')
            ]);
            $rating->save();
            return redirect()->back();
        }

        return redirect()->back()->with('error', 'Can not rate seller more than one time.');;
    }

    /**
     * Redirects if access url of a post.
     * 
     * @param  int $auction_id
     * @return Response
     */
    public function showRedirect($auction_id)
    {
        return redirect('/auctions/' . $auction_id);
    }

    /**
     * Remove a specified auction from storage.
     *
     * @param  Request $request
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        $auction = Auction::findOrFail($id);

        $this->authorize('delete', $auction);
        $auction->delete();

        return $auction;
    }
}
