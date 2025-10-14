<?php

namespace App\Http\Controllers;

use App\Models\Modules\Assistant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('/');
    }


//    public function index()
//    {
//        $user = Auth::user();
//        $config = Assistant::where('user_id', Auth::id())->first();
//        if ($user && $config) {
//            $allowedOrigins = $config->domains()->pluck('domain_name')->toArray();
//            config(['cors.allowed_origins' => $allowedOrigins]);
//        } else {
//            // No authenticated user, maybe fallback or default
//            config(['cors.allowed_origins' => ['https://codes.automationguru.io/public/']]);
//        }
//
//        return view('assistant.user-index', compact('config'));
//    }
}
