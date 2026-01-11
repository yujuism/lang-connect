<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Language;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateLanguagesRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ProfileController - Handles HTTP requests for user profiles
 *
 * This controller follows the Single Responsibility Principle:
 * - Receives HTTP requests
 * - Validates input (via Form Requests)
 * - Delegates business logic to ProfileService
 * - Returns responses
 *
 * Business logic is in ProfileService, not here.
 */
class ProfileController extends Controller
{
    /**
     * Inject ProfileService via constructor
     */
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Display user profile
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $data = $this->profileService->getProfileData($user);

        return view('profile.show', $data);
    }

    /**
     * Show profile edit form
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('userLanguages.language');

        $availableLanguages = Language::orderBy('name')->get();

        return view('profile.edit', compact('user', 'availableLanguages'));
    }

    /**
     * Update user profile
     *
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->profileService->updateProfile($user, $request->validated());

        return redirect()
            ->route('profile.show', $user)
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user languages
     *
     * @param UpdateLanguagesRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLanguages(UpdateLanguagesRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->profileService->updateLanguages($user, $request->validated()['languages']);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Languages updated successfully!');
    }

    /**
     * Update recording preference
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRecordingPreference(Request $request)
    {
        $request->validate([
            'preference' => 'required|in:ask,always,never',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'recording_preference' => $request->input('preference'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete user account
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->profileService->deleteAccount($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
