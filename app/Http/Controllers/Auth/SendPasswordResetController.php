<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class SendPasswordResetController extends Controller
{
    protected $redirectTo = '/';    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Shows email form to send email for resetting password process.
     *
     * @param $token
     * @return Response
     */
    function showEmailForm() {
        return view('auth.forget_password');
    }

    /**
     * Handles email form to send email for resetting password process.
     *
     * @param $token
     * @return Response
     */
    function emailForm(Request $request) {
        $request->validate(['email' => 'required|email']);
    
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

}
