# Clean Architecture in LangConnect

This document explains the clean architecture implementation in the LangConnect project, following industry best practices similar to Spring Boot's layered architecture.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                         HTTP Request                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                    1. ROUTES (routes/)                       │
│                 Define HTTP endpoints                        │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              2. CONTROLLERS (app/Http/Controllers)           │
│  Responsibilities:                                           │
│  ✓ Receive HTTP requests                                    │
│  ✓ Delegate to Form Requests for validation                 │
│  ✓ Call Service layer for business logic                    │
│  ✓ Return HTTP responses                                    │
│  ✗ NO business logic                                        │
│  ✗ NO database queries                                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│            3. FORM REQUESTS (app/Http/Requests)              │
│  Responsibilities:                                           │
│  ✓ Validate incoming data                                   │
│  ✓ Authorization checks                                     │
│  ✓ Custom error messages                                    │
└──────────────────────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│               4. SERVICES (app/Services)                     │
│  Responsibilities:                                           │
│  ✓ ALL business logic                                       │
│  ✓ Data transformation                                      │
│  ✓ Complex queries                                          │
│  ✓ Transaction management                                   │
│  ✓ Calling multiple models                                  │
│  ✓ Event/Job dispatching                                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                5. MODELS (app/Models)                        │
│  Responsibilities:                                           │
│  ✓ Database interactions (Eloquent ORM)                     │
│  ✓ Relationships                                            │
│  ✓ Simple accessors/mutators                               │
│  ✓ Query scopes                                             │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                      6. DATABASE                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Comparison: Before vs After

### ❌ BEFORE (Fat Controller Anti-pattern)

```php
// ProfileController.php - 140 lines of mixed concerns
class ProfileController extends Controller
{
    public function show(User $user)
    {
        // Complex query logic in controller
        $user->load([
            'progress',
            'languages',
            'reviewsReceived' => function($query) {
                $query->where('is_public', true)
                      ->with(['reviewer', 'session.language'])
                      ->latest()
                      ->limit(10);
            }
        ]);

        // Business logic in controller
        $avgRatings = $user->reviewsReceived()
            ->where('is_public', true)
            ->selectRaw('AVG(overall_rating) as avg_overall, ...')
            ->first();

        // More business logic
        $recentSessionsCount = $user->sessionsAsUser1()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->count() + $user->sessionsAsUser2()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('profile.show', compact('user', 'avgRatings', 'recentSessionsCount'));
    }

    public function update(Request $request)
    {
        // Validation in controller
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // File handling logic in controller
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::exists($user->avatar_path)) {
                Storage::delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $path;
        }

        $user->update($validated);
        return redirect()->route('profile.show', $user);
    }
}
```

**Problems:**
- ❌ Controller has 140+ lines
- ❌ Mixes HTTP handling with business logic
- ❌ Hard to test
- ❌ Hard to reuse logic elsewhere
- ❌ Validation mixed with routing
- ❌ File handling in wrong layer

---

### ✅ AFTER (Clean Architecture)

#### 1. Controller (Thin - Just Routing)

```php
// app/Http/Controllers/ProfileController.php - 118 lines
class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService  // Dependency Injection
    ) {}

    public function show(User $user)
    {
        // Just delegate to service and return view
        $data = $this->profileService->getProfileData($user);
        return view('profile.show', $data);
    }

    public function update(UpdateProfileRequest $request)  // Form Request handles validation
    {
        $this->profileService->updateProfile(
            auth()->user(),
            $request->validated()
        );

        return redirect()
            ->route('profile.show', auth()->user())
            ->with('success', 'Profile updated successfully!');
    }
}
```

#### 2. Form Request (Validation Layer)

```php
// app/Http/Requests/UpdateProfileRequest.php
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'timezone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'avatar.image' => 'The avatar must be an image file.',
            'avatar.max' => 'The avatar must not be larger than 2MB.',
        ];
    }
}
```

#### 3. Service (Business Logic Layer)

```php
// app/Services/ProfileService.php
class ProfileService
{
    /**
     * Get complete profile data for display
     */
    public function getProfileData(User $user): array
    {
        $user->load([
            'progress',
            'languages',
            'reviewsReceived' => fn($q) => $q->where('is_public', true)
                ->with(['reviewer', 'session.language'])
                ->latest()
                ->limit(10)
        ]);

        return [
            'user' => $user,
            'avgRatings' => $this->calculateAverageRatings($user),
            'achievements' => $this->getUserAchievements($user),
            'recentSessionsCount' => $this->getRecentSessionsCount($user),
        ];
    }

    /**
     * Update user profile with avatar handling
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['avatar'])) {
            $data['avatar_path'] = $this->handleAvatarUpload($user, $data['avatar']);
            unset($data['avatar']);
        }

        $user->update($data);
        return $user->fresh();
    }

    /**
     * Handle avatar upload and delete old avatar
     */
    private function handleAvatarUpload(User $user, $avatar): string
    {
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        return $avatar->store('avatars', 'public');
    }

    // ... more business logic methods
}
```

**Benefits:**
- ✅ Controller is 22% smaller and cleaner
- ✅ Each class has single responsibility
- ✅ Business logic is reusable
- ✅ Easy to test (mock services)
- ✅ Validation is separate
- ✅ Can swap implementations easily

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/         # HTTP Request Handlers (Thin)
│   │   ├── ProfileController.php      ✓ Refactored
│   │   ├── MessageController.php      ✓ Refactored
│   │   ├── SessionController.php      (Using AchievementService already)
│   │   └── LearningRequestController.php (Using MatchingService already)
│   │
│   └── Requests/           # Validation Logic
│       ├── UpdateProfileRequest.php
│       ├── UpdateLanguagesRequest.php
│       └── SendMessageRequest.php
│
├── Services/               # Business Logic Layer
│   ├── ProfileService.php       ✓ New
│   ├── MessageService.php       ✓ New
│   ├── AchievementService.php   (Already existed)
│   └── MatchingService.php      (Already existed)
│
├── Models/                 # Data Layer (Eloquent ORM)
│   ├── User.php
│   ├── Message.php
│   ├── PracticeSession.php
│   └── ...
│
└── Events/                 # Domain Events
    └── MessageSent.php     (Broadcasting)
```

---

## Refactored Controllers Summary

### 1. ProfileController ✅

**Before:** 140 lines with mixed concerns
**After:** 118 lines, clean separation

**Changes:**
- Extracted validation to `UpdateProfileRequest` and `UpdateLanguagesRequest`
- Created `ProfileService` with methods:
  - `getProfileData()` - Aggregates all profile display data
  - `updateProfile()` - Handles profile updates and avatar
  - `updateLanguages()` - Manages user languages in transaction
  - `deleteAccount()` - Cleanup on account deletion
  - Private helpers for calculations

### 2. MessageController ✅

**Before:** 136 lines with complex SQL and business logic
**After:** 107 lines, delegated to service

**Changes:**
- Extracted validation to `SendMessageRequest`
- Created `MessageService` with methods:
  - `getUserConversations()` - Complex SQL for conversation list
  - `getConversation()` - Get messages between users
  - `sendMessage()` - Create message + notification + broadcast
  - `markAsRead()` - Update read status
  - `fetchNewMessages()` - For AJAX polling

---

## Key Principles Applied

### 1. **Single Responsibility Principle (SRP)**
Each class has ONE reason to change:
- Controllers: Change when HTTP interface changes
- Services: Change when business logic changes
- Form Requests: Change when validation rules change

### 2. **Dependency Injection**
```php
public function __construct(
    private ProfileService $profileService  // Injected by Laravel container
) {}
```
Benefits:
- Easy to mock in tests
- Loose coupling
- Can swap implementations

### 3. **Transaction Management**
```php
// In Service
public function updateLanguages(User $user, array $languages): void
{
    DB::transaction(function () use ($user, $languages) {
        $user->userLanguages()->delete();
        // Create new ones
    });
}
```

### 4. **Clear Method Naming**
```php
// BAD
public function data($user) { ... }

// GOOD
public function getProfileData(User $user): array { ... }
```

---

## Testing Benefits

### Before (Hard to Test)
```php
// Have to make HTTP requests to test business logic
public function test_profile_update()
{
    $response = $this->post('/profile/update', [...]);
    // Tests HTTP layer + validation + business logic all at once
}
```

### After (Easy to Test)
```php
// Can test service directly
public function test_update_profile_handles_avatar()
{
    $service = new ProfileService();
    $user = User::factory()->create();
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $result = $service->updateProfile($user, ['avatar' => $avatar]);

    $this->assertNotNull($result->avatar_path);
    Storage::disk('public')->assertExists($result->avatar_path);
}
```

---

## Laravel vs Spring Boot Comparison

| Layer | Laravel | Spring Boot |
|-------|---------|-------------|
| **Routing** | `routes/web.php` | `@RequestMapping` annotations |
| **Controller** | `app/Http/Controllers` | `@RestController` classes |
| **Validation** | `FormRequest` classes | `@Valid` + DTO classes |
| **Business Logic** | `app/Services` | `@Service` classes |
| **Data Access** | Eloquent Models | `@Repository` + JPA |
| **DI Container** | Laravel Container | Spring Container |

**Key Difference:**
- Spring Boot: Annotations everywhere (`@Autowired`, `@Service`)
- Laravel: Convention over configuration (auto-injection by type hints)

---

## When to Create a Service?

### ✅ Create Service When:
1. Method has more than 10 lines of business logic
2. Logic is reused in multiple places
3. Method does complex calculations
4. Method interacts with multiple models
5. Method handles file uploads/processing
6. Method sends emails/notifications
7. Method has transactions

### ❌ Keep in Controller When:
1. Simple CRUD (just calls `Model::create()`)
2. Just passing data to view
3. One-line operations
4. No business logic

---

## Example: Complete Flow

**User updates their profile with a new avatar:**

```
1. HTTP Request
   POST /profile/update
   { name: "John", avatar: <file> }

2. Routes
   routes/web.php
   Route::put('/profile/update', [ProfileController::class, 'update'])

3. Form Request (Validation)
   UpdateProfileRequest validates:
   - name is required
   - avatar is image < 2MB

4. Controller (Routing)
   ProfileController receives validated data
   Calls: $this->profileService->updateProfile(...)

5. Service (Business Logic)
   ProfileService:
   - Deletes old avatar from storage
   - Stores new avatar
   - Updates user record
   - Returns updated user

6. Model (Data Access)
   User model saves to database via Eloquent

7. Response
   Controller returns redirect with success message
```

---

## Migration Guide

If you want to refactor more controllers, follow this pattern:

### Step 1: Create Form Request
```bash
php artisan make:request StoreSessionRequest
```

### Step 2: Create Service
```bash
# Manual creation (no artisan command)
# Create: app/Services/SessionService.php
```

### Step 3: Move Logic
1. Move validation rules to Form Request
2. Move business logic to Service
3. Inject service in controller
4. Update controller to delegate

### Step 4: Test
```bash
php artisan test
```

---

## Additional Resources

- [Laravel Service Container](https://laravel.com/docs/11.x/container)
- [Form Request Validation](https://laravel.com/docs/11.x/validation#form-request-validation)
- [Clean Architecture Book](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494164)

---

**Author:** AI Assistant
**Date:** December 2025
**Version:** 1.0
