<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        $mustCompleteAthleteProfile = !$this->user()->profile_completed && $this->user()->hasRole('athlete');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'gender' => ['required', 'string', 'in:male,female,other,notsay'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'birthday' => [$mustCompleteAthleteProfile ? 'required' : 'nullable', 'date', 'before:today'],
        ];
    }
}
