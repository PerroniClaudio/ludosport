<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller {
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View {
        return view('profile.edit', [
            'languages' => Language::all(),
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse {

        $request->user()->fill($request->validated());

        $surname = $request->surname;
        $battle_name = $request->battle_name ?? $request->user()->generateBattleName();

        // Dato che viene usato per vedere il profilo dell'utente, il battle name deve essere unico.
        // Se esiste già gli lascia il suo (se non è doppio) oppure ne genera uno nuovo.
        $battleNameError = false;
        if(User::where('battle_name', $battle_name)->whereNot('id', $request->user()->id)->exists()) {
            if($battle_name != $request->user()->battle_name && !User::where('battle_name', $request->user()->battle_name)->whereNot('id', $request->user()->id)->exists()) {
                $battle_name = $request->user()->battle_name;
            } else {
                $battle_name = $request->user()->generateBattleName();
            }
            $battleNameError = true;
        }

        $request->user()->update([
            'surname' => $surname,
            'battle_name' => $battle_name,
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if($battleNameError) {
            return Redirect::route('profile.edit')->with('status', 'Profile updated! Battle name already exists.');
        }

        return Redirect::route('profile.edit')->with('status', 'Profile updated!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->is_disabled = true;
        $user->save();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
