<?php

namespace App\Http\Controllers;

use App\Models\EventDraft;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    public function supprimer(int $id)
    {
        $draft = EventDraft::where('id', $id)
            ->where('organizer_id', Auth::id())
            ->firstOrFail();

        $draft->delete();

        if (session('draft_id') == $id) {
            session()->forget(['draft_data', 'draft_id']);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Brouillon supprimé.');
    }
}
