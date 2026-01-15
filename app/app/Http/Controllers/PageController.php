<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of pages
     */
    public function index()
    {
        $pages = Page::with('featuredImage')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page
     */
    public function create()
    {
        return view('pages.create');
    }

    /**
     * Store a newly created page in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'featured_image_id' => 'nullable|exists:media,id',
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page = Page::create($validated);

        return redirect()->route('pages.index')
            ->with('success', 'Halaman berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified page
     */
    public function edit(Page $page)
    {
        return view('pages.edit', compact('page'));
    }

    /**
     * Update the specified page in storage
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'featured_image_id' => 'nullable|exists:media,id',
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page->update($validated);

        return redirect()->route('pages.index')
            ->with('success', 'Halaman berhasil diperbarui.');
    }

    /**
     * Remove the specified page from storage
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('pages.index')
            ->with('success', 'Halaman berhasil dihapus.');
    }

    /**
     * Display the specified page for public viewing
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->with('featuredImage')
            ->firstOrFail();

        return view('storefront.pages.show', compact('page'));
    }
}
