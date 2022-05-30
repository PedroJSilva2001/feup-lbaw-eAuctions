<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Response;

use App\Models\User;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\Transaction;

class UserController extends Controller
{

    /**
     * Display the specified User.
     *
     * @param int $id
     * @return Response
     */
    public function showProfile($id)  
    {
        $user = User::findOrFail($id);
        if (Str::contains($user->username, "DeletedAccount") && $user->password == "")
            return redirect()->back();
        if (Auth::id() == $id || (!Auth::guest() && Auth::user()->isadmin))
            return redirect()->route('bids', $id);
        else
            return redirect()->route('owned', $id);
    }

    /**
     * Display the specified User's owned auctions.
     *
     * @param int $id
     * @return Response
     */
    public function showOwned($id)
    {
        $user = User::findOrFail($id);
        if (Str::contains($user->username, "DeletedAccount") && $user->password == "")
            return redirect()->back();

        $ownedAuctions = Auction::where('seller_id', $id)->where('end_date', '!=', null)->paginate(3);

        $followedAuctions = Auction::whereIn('id',
            Follow::where('user_id', $id)
            ->get()->map->only(['auction_id'])
        );

        $unreadNotifications = Notification::where('user_id', $id)->where('seen', false);
        $isAdmin = false;

        if (!Auth::guest()) {
            $isAdmin = Auth::user()->isadmin;
        }
        return view('partials.owned', [
            'owner' => $user,
            'ownedAuctions' => $ownedAuctions,
            'followedAuctions' => $followedAuctions->paginate(4),
            'authUserisAdmin' => $isAdmin,
            'nrUnreadNotifications' => count($unreadNotifications->get()),
        ]);
    }

    /**
     * Display the specified User's bidding history.
     *
     * @param int $id
     * @return Response
     */
    public function showBids($id)
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }
        
        $biddingAuctions = Bid::where('user_id', $id)
            ->orderBy('date', 'DESC')->paginate(4);

        $followedAuctions = Auction::whereIn('id',
            Follow::where('user_id', $id)
            ->get()->map->only(['auction_id'])
        );

        $unreadNotifications = Notification::where('user_id', $id)->where('seen', false);
        $isAdmin = false;

        if (!Auth::guest()) {
            $isAdmin = Auth::user()->isadmin;
        }
        return view('partials.bids', [
            'owner' => $user,
            'biddingAuctions' => $biddingAuctions,
            'followedAuctions' => $followedAuctions->paginate(4),
            'authUserisAdmin' => $isAdmin,
            'nrUnreadNotifications' => count($unreadNotifications->get()),
        ]);
    }

    /**
     * Display the specified User's draft auctions.
     *
     * @param int $id
     * @return Response
     */
    public function showDrafts($id) 
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        $followedAuctions = Auction::whereIn('id',
            Follow::where('user_id', $id)
            ->get()->map->only(['auction_id'])
        );

        $draftAuctions = Auction::where('seller_id', $id)->where('start_date', null)->paginate(4);
        $unreadNotifications = Notification::where('user_id', $id)->where('seen', false);
        $isAdmin = false;

        if (!Auth::guest()) {
            $isAdmin = Auth::user()->isadmin;
        }
        return view('partials.drafts', [
            'owner' => $user,
            'followedAuctions' => $followedAuctions->paginate(4),
            'draftAuctions' => $draftAuctions,
            'authUserisAdmin' => $isAdmin,
            'nrUnreadNotifications' => count($unreadNotifications->get()),
        ]);
    }

    /**
     * Display the specified User's followed auctions.
     *
     * @param int $id
     * @return Response
     */
    public function showFollowed($id) 
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        $followedAuctions = Auction::whereIn('id',
            Follow::where('user_id', $id)
            ->get()->map->only(['auction_id'])
        );

        $unreadNotifications = Notification::where('user_id', $id)->where('seen', false);
        $isAdmin = false;

        if (!Auth::guest()) {
            $isAdmin = Auth::user()->isadmin;
        }
        return view('partials.followed', [
            'owner' => $user,
            'followedAuctions' => $followedAuctions->paginate(4),
            'authUserisAdmin' => $isAdmin,
            'nrUnreadNotifications' => count($unreadNotifications->get()),
        ]);
    }

    /**
     * Display the specified User's notifications.
     *
     * @param int $id
     * @return Response
     */
    public function showNotifications($id) 
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        $followedAuctions = Auction::whereIn('id',
            Follow::where('user_id', $id)
            ->get()->map->only(['auction_id'])
        );

        $unreadNotifications = Notification::where('user_id', $id)->where('seen', false)->get();

        foreach($unreadNotifications as $notification){
            $notification->update([
                'seen' => true,
            ]);
        }

        $notifications = Notification::where('user_id', $id)->orderByDesc('date');
        $isAdmin = false;

        if (!Auth::guest()) {
            $isAdmin = Auth::user()->isadmin;
        }
        return view('partials.notifications', [
            'owner' => $user,
            'notifications' => $notifications->paginate(4),
            'followedAuctions' => $followedAuctions->paginate(4),
            'authUserisAdmin' => $isAdmin,
            'nrUnreadNotifications' => 0,
        ]);
    }

    /**
     * Shows the edit profile form of an user.
     *
     * @param int $id
     * @return Response
     */
    public function showEditProfile($id) 
    {
        $user = User::findOrFail($id);
        if ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "") 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        return view('pages.settings', [
            'title' => 'My Account',
            'owner' => $user,
        ]);
    }

    /**
     * Shows the payments page in profile settings of an user.
     *
     * @param int $id
     * @return Response
     */
    public function showPaymentsProfile($id)
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        $payments = Transaction::where('user_id', $user->id)->orderBy('date', 'desc');

        return view('pages.settings', [
            'title' => 'Payments',
            'owner' => $user,
            'payments' => $payments->paginate(3)
        ]);
    }

    /**
     * Shows the delete account page in profile settings of an user.
     *
     * @param int $id
     * @return Response
     */
    public function showDeleteProfile($id)
    {
        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        return view('pages.settings', [
            'title' => 'Delete Account',
            'owner' => $user
        ]);
    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:user',
            'email' => 'nullable|string|email|max:255|unique:user',
            'password' => 'nullable|string|min:8|confirmed',
            'oldpassword' => 'nullable|string|min:8',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Error in creating auction.')->withInput();
        }

        $user = User::findOrFail($id);
        if (Auth::guest() || ((Str::contains(Auth::user()->username, "DeletedAccount") && Auth::user()->password == "")) 
            || (!Auth::user()->isadmin && $id != Auth::id())) {
            return redirect()->back();
        }

        if ($request->has('profile_image')) {
            $file = base_path('public/assets/profile_pictures/' . $id);

            if (Storage::exists($file)) {
                Storage::delete($file);
            }
            
            $picture = $request->file('profile_image');
            $fileName = $id . '.' . $picture->extension();
            $picture->move(public_path('assets/profile_pictures'), $fileName);
        
            $user->picture = 'profile_pictures/' . $fileName;
            $user->save();
                        
        }

        if ($request->oldpassword) {      
            if (Hash::check($request->oldpassword, $user->password)) {                                  
                $user->password = Hash::make($request->password);
                $user->save(); 
            }
            else  {
                return redirect(url('/users/' . $id . '/settings/account'))->with('error', 'Error in updating password. Current password does not match.');
            }
        }

        $user->update(array_filter($request->except(['password'])));

        return redirect(url('/users/' . $id . '/settings/account'))->with('success', 'User updated successfully.');
    }

    /**
     * Redirects if access url of a post.
     * 
     * @param  int $id
     * @return Response
     */
    public function showRedirect($id)
    {
        return redirect('/users/' . $id);
    }

    /**
     * Remove the specified User from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function delete($id) 
    {
        $user = User::findOrFail($id);

        $user->username = "DeletedAccount-" . $id;
        $user->name = "";
        $user->password = "";
        $user->email = "_@_.com";
        $user->picture = null;

        $user->save();

        $user->cancelAllAuctions();
        
        return redirect('/')->withSuccess('Successful profile deletion.');
    }
}
