<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function privacy_policy(Request $request)
    {
        return view('pages.privacy_policy');
    }

    public function term_and_condition(Request $request)
    {
        return view('pages.term_and_condition');
    }

    public function delete_account(Request $request)
    {
        return view('pages.delete_account');
    }

    public function support(Request $request)
    {
        return view('pages.support');
    }

}
