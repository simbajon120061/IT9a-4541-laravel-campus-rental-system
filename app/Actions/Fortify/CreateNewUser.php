<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'ends_with:@umindanao.edu.ph',
            ],
            'phone_number' => ['required', 'digits:11'],
            'course' => ['required', 'string', Rule::in(User::PROGRAMS)],
            'year_level' => ['required', 'string', Rule::in(User::SCHOOL_LEVELS)],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'email.ends_with' => 'Only University of Mindanao emails are allowed.',
            'course.in' => 'Please select a valid program.',
            'year_level.in' => 'Please select a valid school level.',
        ])->validate();

        $firstName = trim($input['first_name']);
        $lastName = trim($input['last_name']);

        return User::create([
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $input['email'],
            'phone_number' => $input['phone_number'],
            'course' => $input['course'],
            'year_level' => $input['year_level'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
