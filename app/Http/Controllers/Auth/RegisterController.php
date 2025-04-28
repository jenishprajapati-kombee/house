<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers; // Can use this trait for boilerplate
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request; // Use Illuminate\Http\Request
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Providers\Filament\AdminPanelProvider; // Import Panel Provider to get URL
use Illuminate\Support\Facades\Log;
use Filament\Facades\Filament; // Add this line
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    // use RegistersUsers; // Optionally use trait, but we'll implement manually for clarity

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
     // Redirect to the Filament admin panel dashboard after registration
     protected function redirectTo()
     {
         return AdminPanelProvider::getUrl();
         //return Filament::getPanel('admin')->getDashboardUrl();
     }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register'); // We'll create this view next
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate(); // Validate input

        $user = $this->create($request->all()); // Create the user

        // Assign the 'User' role
        $userRole = Role::findByName('User', 'web'); // Ensure guard name matches
        if ($userRole) {
            $user->assignRole($userRole);
        } else {
            // Handle error: Role not found (should not happen if seeder ran)
            Log::error("Default 'User' role not found during registration.");
            // Optionally redirect back with an error
        }

        Auth::login($user); // Log the user in

        return redirect($this->redirectTo()); // Redirect to Filament panel
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users'], // Add validation
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' checks password_confirmation field
            // 'nationality' => ['nullable', 'string', 'max:100'], // Optional validation for nationality
        ]);
    }
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile_number' => $data['mobile_number'], // Add field
            // 'nationality' => $data['nationality'] ?? null, // Add field (if included in form)
            'password' => Hash::make($data['password']),
        ]);
    }
}