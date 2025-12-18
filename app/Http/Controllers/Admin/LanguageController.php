<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        $this->ensureAdmin();

        return view('admin.languages.index', [
            'languages' => Language::withCount('users')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->ensureAdmin();

        return view('admin.languages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:languages,code'],
            'name' => ['required', 'string', 'max:50'],
        ]);

        Language::create($validated);

        return redirect()
            ->route('admin.languages.index')
            ->with('status', __('Language added.'));
    }

    public function show(Language $language): RedirectResponse
    {
        return redirect()->route('admin.languages.edit', $language);
    }

    public function edit(Language $language): View
    {
        $this->ensureAdmin();

        return view('admin.languages.edit', [
            'language' => $language,
        ]);
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:languages,code,' . $language->id],
            'name' => ['required', 'string', 'max:50'],
        ]);

        $language->update($validated);

        return redirect()
            ->route('admin.languages.edit', $language)
            ->with('status', __('Language updated.'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        $this->ensureAdmin();

        if ($language->users()->exists()) {
            return back()->withErrors(['language' => __('Cannot delete a language that is assigned to users.')]);
        }

        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('status', __('Language deleted.'));
    }

    protected function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }
}