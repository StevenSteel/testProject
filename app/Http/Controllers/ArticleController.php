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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Article::with('user')
                    ->where('publish', true)
                    ->select(['articles.id', 'articles.title', 'articles.updated_at', 'articles.created_at', 'user.name'])
                    ->get();

        return [
            'data' => $data,
            'total' => $data->count(),
        ];
    }

    /**
     * Store a newly created article in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        return [
            'status' => 'success'
        ];
    }
}
