<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import controller

Route::get('/', function () {
    // Redirect to login or admin panel by default
    return redirect()->route('filament.admin.auth.login');
    // Or redirect directly to panel if user is logged in
    // return auth()->check() ? redirect(\App\Providers\Filament\AdminPanelProvider::getUrl()) : redirect()->route('filament.admin.auth.login');
});

// Apply 'guest' middleware to the registration routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']); // The POST route already exists, just ensure it's inside the group
});

// Registration Routes
// Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
// Route::post('register', [RegisterController::class, 'register']);

// Note: Filament routes are usually handled by the Panel Provider
// Standard Laravel Auth routes (like password reset) might need to be added if required
// e.g., Auth::routes(['register' => false]); // Disable default Laravel registration if using custom
// use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/download-example-csv', function () {
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="users-example.csv"',
    ];

    $callback = function () {
        $file = fopen('php://output', 'w');
        // Add CSV headers matching the importer columns
        fputcsv($file, ['name', 'email', 'password', 'mobile_number', 'nationality']);
        // Add example rows with sample data
        fputcsv($file, [
            'John Doe',
            'john@example.com',
            '123456',
            '9586714114',
            'Indian'
        ]);
        fputcsv($file, [
            'Jane Smith',
            'jane@example.com',
            '123456',
            '9664696893',
            'Indian'
        ]);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->name('download-example-csv');
