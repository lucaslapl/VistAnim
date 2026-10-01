<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStructureRequest;
use App\Models\AdminLog;
use App\Models\User;
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

    public function store(StoreStructureRequest $request)
    {
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
