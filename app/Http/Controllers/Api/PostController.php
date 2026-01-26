<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    public function index(){
        $posts = Post::all();
        return $posts;
    }

    public function show(Post $post){ //de esta forma te ahorras poner un find() explicito.
        return $post;
    }

    public function destroy(Post $post){
        $post->delete();
    }

    public function store(StorePostRequest $request){
        $data = $request->validated();
        // dd($data);
        $post = Post::create($data);
        return $post;


    }
}
