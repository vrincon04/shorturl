<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Libraries\Helper;

class LinkController extends Controller
{
    public function get($code)
    {
        // Search the code in the database.
        $link = Link::where('code', 'LIKE BINARY', $code)->firstOrFail();
        // Insert the history
        $link->histories()->save(new LinkHistory());
        // Redirect to the url.
        return redirect()->to($link->url);
    }

    public function getTop()
    {
        $links = Link::withCount('histories')
            ->orderBy('histories_count', 'desc')
            ->take(100)
            ->get();

        return response()->json([
            'links' => $links
        ], 200);
    }

    public function generate(Request $request)
    {
        // Create the link object.
        $link = new Link();
        // Set the url attribute.
        $link->fill([
            'url' => $request->input('url')
        ]);
        // Check is valid.
        if ( !$link->validate() )
        {
            return response()->json(['errors' => $link->errors()], 400);
        }
        // search the link in the database.
        $exist = Link::where('url', $link->url)->first();
        // Check is exist
        if ( $exist ) {
            // set the exist link.
            $link = $exist;
        } else {
            // Save the new link.
            $link->save();
            // Refresh de link object.
            $link->refresh();
            // Set the code
            $link->code = Helper::numberToAlphabet($link->id - 1);
            // Save the new code.
            $link->save();
        }

        return response()->json([
            'url' => $link->url,
            'generated' => [
                'url' => url("/{$link->code}"),
                'code' => $link->code
            ]
        ], 201);
    }
}
