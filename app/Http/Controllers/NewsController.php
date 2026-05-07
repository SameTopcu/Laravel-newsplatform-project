<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $heroQuery = News::query()
            ->with(['author', 'category'])
            ->published()
            ->latest('published_at');

        if ($request->filled('category')) {
            $heroQuery->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $heroNews = $heroQuery->first();

        $query = News::query()
            ->with(['author', 'category'])
            ->published()
            ->latest('published_at');

        if ($heroNews) {
            $query->where('id', '!=', $heroNews->id);
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
        }

        $newsList = $query->paginate(9);

        return view('news.index', [
            'heroNews' => $heroNews,
            'newsList' => $newsList,
        ]);
    }

    public function show(string $slug)
    {
        $news = News::query()
            ->with(['author', 'category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedNews = News::query()
            ->with(['author', 'category'])
            ->published()
            ->where('category_id', $news->category_id)
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('news.show', compact('news', 'relatedNews'));
    }

    public function incrementView(Request $request, int $id): JsonResponse
    {
        $news = News::query()
            ->published()
            ->whereKey($id)
            ->firstOrFail();

        $news->increment('view_count');

        return response()->json([
            'ok' => true,
            'view_count' => $news->view_count,
        ]);
    }
}
