<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Handles a comment.
     * 
     * @param  Request $request
     * @param  int $auction_id
     * @return Response
     */
    public function store(Request $request, $auction_id) {
        $this->validate($request, [
            'comment' => 'required|string|max:1000',
        ]);

        $comment = new Comment([
            'user_id' => Auth::id(),
            'auction_id' => $auction_id,
            'message' => $request->get('comment'),
            'date' => Carbon::now()
        ]);
        $comment->save();

        return redirect('/auctions/' . $comment->auction_id);
    }

    /**
     * Redirects if access url of comments.
     * 
     * @param  int $auction_id
     * @return Response
     */
    public function showComments($auction_id) {
        return redirect('/auctions/' . $auction_id);
    }

    /**
     * Redirects if access url of comment.
     * 
     * @param  int $auction_id
     * @return Response
     */
    public function showComment($auction_id) {
        return redirect('/auctions/' . $auction_id);
    }

    /**
     * Handles a comment deletion.
     * 
     * @param  Request $request
     * @param  int $auction_id
     * @param  int $comment_id
     * @return Response
     */
    public function delete(Request $request, $auction_id, $comment_id) {
        $comment = Comment::findOrFail($comment_id);
        $comment->delete();

        return redirect('/auctions/' . $comment->auction_id);
    }

}
