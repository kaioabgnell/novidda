<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('changelogs')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.form', ['category' => new Category(['color' => '#6c5ce7'])]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));
        return redirect()->route('categories.index')->with('status', 'Categoria criada.');
    }

    public function edit(Category $category)
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request));
        return redirect()->route('categories.index')->with('status', 'Categoria atualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('status', 'Categoria removida.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:60'],
        ], [], ['name' => 'nome', 'color' => 'cor', 'icon' => 'ícone']);
    }
}
