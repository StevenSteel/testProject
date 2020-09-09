<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $showPublished = (!Auth::check() || $request->get('publish', 1));

        $data = Article::join('users', 'users.id', '=', 'articles.user_id')
                    ->where('articles.publish', $showPublished)
                    ->select(['articles.id', 'articles.title', 'articles.updated_at', 'articles.created_at', 'users.name as user'])
                    ->get();

        return response()->json([
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Store a newly created article in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        abort_if(! Auth::check(), 403, "Sorry, you don't have access.");

        $request->validate([
            'title' => 'required|min:10|max:200',
            'text' => 'required|min:10',
            'publish' => 'required|boolean',
        ]);

        $article = new Article();
        $article->title = $request->title;
        $article->text = $request->text;
        $article->user_id = Auth::id();
        $article->save();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
