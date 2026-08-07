<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function show(Request $request)
    {
        $account = $request->user()->account;
        $token = $account->widget_token;

        return view('preview.show', compact('token', 'account'));
    }
}
