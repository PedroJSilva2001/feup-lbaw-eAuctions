<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Block;
use App\Models\Transaction;

class AdminController extends Controller {

    public function __construct() {
        $this->middleware('admin')->except(['requestTransaction']);
    }

    /**
     * Shows admin page.
     *
     * @return Response
     */
    public function showPage(){

        if (Auth::guest() || !(Auth::user()->isadmin)) {
            return redirect('/');
        }

        return redirect()->route('show_blocked_users');

    }

    /**
     * Handles a block.
     * 
     * @param  Request $request
     * @param  int $user_id
     * @return Response
     */
    public function blockUser(Request $request, $user_id) {
        if (Auth::guest()) {
            abort(404);
        }

        $id = Auth::id();
        $user = User::findOrFail($user_id);

        if (!Auth::user()->isadmin) {
            abort(404);
        }

        $validator = Validator::make($request->all(),
            [
                'description' => 'required|string|max:2000',
                'end_date'    => 'nullable|date|after:' . Carbon::now()
            ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Error in blocking user: missing paramaters');
        }

        if ($user->isBlocked()) {
            return redirect()->back()->with('error', 'Error in blocking user: user is already blocked');
        }

        $block = new Block([
            'admin_id'    => $id,
            'user_id'     => $user->id,
            'description' => $request->get('description'),
            'end_date'    => $request->get('end_date'),
        ]);

        $block->save();

        return redirect('/admins/');
    }

    /**
     * Handles a unblock.
     * 
     * @param  Request $request
     * @param  int $user_id
     * @return Response
     */
    public function unblockUser($user_id) {
        if (Auth::guest()) {
            abort(404);
        }

        $user = User::findOrFail($user_id);

        if (!Auth::user()->isadmin) {
            abort(404);
        }

        if (!$user->isBlocked()) {
            return redirect()->back()->with('error', 'Error in unblocking user: user is not currently blocked');
        }

        $mostRecentBlock = $user->mostRecentBlock();
        $mostRecentBlock->delete();

        return redirect()->back()->with('success', 'User unblocked successfully');
    }

    /**
     * Shows block page.
     *
     * @param  int $user_id
     * @return Response
     */
    public function showBlockPage($user_id) {
        if (Auth::guest()) {
            abort(404);
        }

        $user = User::findOrFail($user_id);

        if (!Auth::user()->isadmin) {
            abort(404);
        }

        return view('pages.blockpage', [
            'user' => $user,
        ]);
    }

    /**
     * Shows blocked users page.
     *
     * @return Response
     */
    public function showBlockedUsers(Request $request) : View
    {
        return view('pages.admins', [
            'title' => "Blocked Users",
            'blocks' => Block::paginate(5)
        ]);
    }

    /**
     * Shows transactions pending page.
     *
     * @return Response
     */
    public function showTransactionsPending(Request $request) : View 
    {
        $pendingTransactions = Transaction::where('status', '=', 'Pending')->where('user_id', '<>', Auth::id())->orderBy('date', 'desc');


        return view('pages.admins', [
            'title' => "Transactions",
            'array' => $pendingTransactions->paginate(4)
        ]);
    }

    /**
     * Shows transactions accepted page.
     *
     * @return Response
     */
    public function showTransactionsAccepted(Request $request) : View 
    {
        $acceptedTransactions = Transaction::where('status', '=', 'Accepted')->orderBy('date', 'desc');

        return view('pages.admins', [
            'title' => "Transactions",
            'array' => $acceptedTransactions->paginate(4)
        ]);
    }

    /**
     * Shows transactions declined page.
     *
     * @return Response
     */
    public function showTransactionsDeclined(Request $request) : View 
    {
        $declinedTransactions = Transaction::where('status', '=', 'Declined')->orderBy('date', 'desc');

        return view('pages.admins', [
            'title' => "Transactions",
            'array' => $declinedTransactions->paginate(4)
        ]);
    }

    /**
     * Handles a transaction request.
     * 
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function requestTransaction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['amount'=> 'required|numeric']);
        if ($validator->fails()) {
            $request->flash();
            return redirect()->back()->with('error', 'Error Confirming Transaction Request')->withInput();
        }

        if ($request->get('action') == 'PayPal') return redirect('/handle-payment/'. $id . '/' . $request->get('amount'));
        else{
            if ($request->get('amount') < 0 && -$request->get('amount') > User::findOrFail($id)->credit) 
                return redirect()->back()->with('error', 'Can not widthdraw more than credit.');

            $transaction = new Transaction([
                'user_id'            => $id,
                'value'              => $request->get('amount'),
                'date'               => Carbon::now(),
                'description'        => $request->get('amount') > 0 ? 'Add Credit to ' : 'Take Credit From ',
                'method'             => 'Transfer',
                'status'             => 'Pending',
            ]);
            $transaction->save();
    
            return redirect()->back();
        }
    }

    /**
     * Handles a transaction confirmation.
     * 
     * @param  Request $request
     * @param  int $transaction_id
     * @return Response
     */
    public function confirmTransaction(Request $request, $transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        $transaction->update([
            'date'               => Carbon::now(),
            'status'             =>  $request->get('action')
        ]);

        $transaction->save();

        return redirect('/admins/transactions-pending');
    }

    /**
     * Redirects if access url of a post.
     * 
     * @param  int $id
     * @return Response
     */
    public function showRedirect($id)
    {
        return redirect()->back();
    }
}