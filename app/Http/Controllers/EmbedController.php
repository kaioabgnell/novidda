<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmbedController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->user()->account->widget_token;
        $snippet = '<script src="' . url('widget.js') . '?token=' . $token . '" async></script>';

        return view('embed.show', compact('token', 'snippet'));
    }
}
