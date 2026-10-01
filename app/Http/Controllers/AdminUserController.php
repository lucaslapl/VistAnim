<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function lister()
    {
        $users = User::withCount('events')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.gestion-structures', compact('users'));
    }

    public function creer()
    {
        return view('admin.creer-structure');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Validation mot de passe : majuscule + minuscule + chiffre
        if (! preg_match('/[A-Z]/', $request->input('password'))) {
            return back()->withErrors(['password' => 'Le mot de passe doit contenir au moins une majuscule.'])->withInput();
        }
        if (! preg_match('/[a-z]/', $request->input('password'))) {
            return back()->withErrors(['password' => 'Le mot de passe doit contenir au moins une minuscule.'])->withInput();
        }
        if (! preg_match('/[0-9]/', $request->input('password'))) {
            return back()->withErrors(['password' => 'Le mot de passe doit contenir au moins un chiffre.'])->withInput();
        }

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'organisateur',
        ]);

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Création structure',
            'details' => 'Création de l\'utilisateur : '.$request->input('email'),
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.structures.lister')
            ->with('success', 'Structure créée avec succès.');
    }

    public function supprimer(int $id)
    {
        if ($id === Auth::id()) {
            return redirect()->route('admin.structures.lister')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user = User::findOrFail($id);

        $email = $user->email;
        $user->delete();

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression structure',
            'details' => 'Suppression de l\'utilisateur : '.$email,
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.structures.lister')
            ->with('success', 'Structure supprimée avec succès.');
    }
}
