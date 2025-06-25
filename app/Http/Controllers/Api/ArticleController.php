<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function getArticle()
    {
        $article = Article::latest()->paginate(5);

        return response()->json([
            'status' => 'true',
            'data' => $article,
        ]);
    }

    public function getArticleById(Request $request)
    {
        $id = $request->query('id');
        $article = Article::where('id',$id)->first();

        return response()->json([
            'status' => 'true',
            'data' => $article,
        ]);
    }

    public function addArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_link' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/image_articles', $image->hashName());

        $article = Article::create([
            'title' => $request->title,
            'description' => $request->description,
            'link' => $request->link,
            'is_link' => $request->is_link,
            'image' => $image->hashName(),
        ]);

        if($article)
        {
            return response()->json([
                'status' => 'true',
                'messsage' => 'berhasil upload article',
                'data' => $article,
            ]);
        }

        return response()->json([
            'status' => 'false',
            'messsage' => 'gagal upload article, silahkan coba lagi'
        ]);
    }
}
