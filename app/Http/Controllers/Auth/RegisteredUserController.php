<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Nation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller {
    /**
     * Display the registration view.
     */
    public function create(): View {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nationality' => ['required', 'string', 'exists:' . Nation::class . ',name'],
            'academy_id' => ['required', 'int', 'exists:' . Academy::class . ',id'],
            'how_found_us' => ['required', 'string', 'max:255'],
        ]);

        $nation = Nation::where('name', $request->nationality)->first();
        $academy = Academy::find($request->academy_id);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nation_id' => $nation->id,
            'battle_name' => $request->battle_name ?? ($request->name . $request->surname . rand(10, 99)),
            'how_found_us' => $request->how_found_us,
        ]);

        $user->academyAthletes()->syncWithoutDetaching($academy->id);
        $user->roles()->syncWithoutDetaching(7);

        Announcement::create([
            'object' => 'New user registered',
            'content' => 'A new user has registered to the platform. - Name: ' . $user->name . ' ' . $user->surname . ' - Email: ' . $user->email . ' - Academy: ' . $academy->name . '.',
            'type' => 4,
            'user_id' => $academy->rector()->id ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
