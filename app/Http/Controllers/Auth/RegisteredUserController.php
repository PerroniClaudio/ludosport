<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Announcement;
use App\Models\Nation;
use App\Models\School;
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
        $isUserMinor = $request->input('registration_type') === 'minor';
        $adultCutoffDate = date('Y-m-d', strtotime('-18 years'));

        $request->validate([
            'registration_type' => ['required', 'string', 'in:adult,minor'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nationality' => ['required', 'string', 'exists:' . Nation::class . ',name'],
            'academy_id' => ['required', 'int', 'exists:' . Academy::class . ',id'],
            'school_id' => ['nullable', 'int', 'exists:' . School::class . ',id'],
            'how_found_us' => ['required', 'string', 'max:255'],
            'birthday' => $isUserMinor
                ? ['required', 'date', 'before:today', 'after:' . $adultCutoffDate]
                : ['required', 'date', 'before_or_equal:' . $adultCutoffDate],
            'subscription_year' => ['required', 'int', 'min:' . 2006, 'max:' . (date('Y'))],
            'gender' => ['required', 'string', 'in:male,female,other,notsay'],
            'battle_name' => ['nullable', 'string', 'max:255'],
            'minor_documents' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $nation = Nation::where('name', $request->nationality)->first();
        $academy = Academy::find($request->academy_id);
        $battle_name = preg_replace('/[^A-Za-z0-9 ]/', '', $request->battle_name);
        $hasUploadedDocuments = false;

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'academy_id' => $request->academy_id,
            'school_id' => $request->school_id,
            'nation_id' => $nation->id,
            'battle_name' => $battle_name ?? ($request->name . $request->surname . rand(10, 99)),
            'how_found_us' => $request->how_found_us,
            'subscription_year' => $request->subscription_year ?? date('Y'),
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'is_user_minor' => $isUserMinor,
            'has_user_uploaded_documents' => false,
        ]);

        if ($isUserMinor && $request->hasFile('minor_documents')) {
            $file = $request->file('minor_documents');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = time() . '_minor_documents.' . $fileExtension;
            $filePath = "/users/{$user->id}/approval_documents/{$fileName}";

            $stored = $file->storeAs("/users/{$user->id}/approval_documents/", $fileName, 'gcs');

            if ($stored) {
                $user->uploaded_documents_path = $filePath;
                $user->has_user_uploaded_documents = true;
                $user->save();
            }
        }

        $user->academyAthletes()->syncWithoutDetaching($academy->id);
        $user->setPrimaryAcademyAthlete($academy->id);
        $user->schoolAthletes()->syncWithoutDetaching($request->school_id);
        $user->setPrimarySchoolAthlete($request->school_id);
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
