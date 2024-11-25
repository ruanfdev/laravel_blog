<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchPostRequest;
use App\Http\Requests\FilterPostRequest;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'You are not logged in']);
        }

        $posts = Post::get();
        return response()->json([
            'posts' => $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $request->user()->id
        ]);
        return response()->json($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::find($id);
        return response()->json($post);
    }

    /**
    * Update the specified resource in storage.
    */
    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $post = Post::find($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->update([
            'title' => $data['title'],
            'content' => $data['content']
        ]);
        return response()->json($post);
    }

    /**
    * Remove the specified resource from storage.
    */
    public function destroy(Request $request, string $id)
    {
        $post = Post::find($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        $posts = Post::where(function($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('content', 'LIKE', "%{$query}%")
              ->orWhereHas('comments', function($q) use ($query) {
                  $q->where('content', 'LIKE', "%{$query}%");
              });
        })
        ->with(['user:id,name,email', 'comments.user:id,name,email'])
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function filterByUser(Request $request)
    {
        $userId = $request->get('user_id');

        $posts = Post::where('user_id', $userId)
            ->with(['user:id,name,email', 'comments.user:id,name,email'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function searchAndFilter(Request $request)
    {
        $query = $request->get('query');
        $userId = $request->get('user_id');

        $posts = Post::query();

        if ($query) {
            $posts->where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhereHas('comments', function($q) use ($query) {
                      $q->where('content', 'LIKE', "%{$query}%");
                  });
            });
        }

        if ($userId) {
            $posts->where('user_id', $userId);
        }

        $results = $posts->with(['user:id,name,email', 'comments.user:id,name,email'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}
