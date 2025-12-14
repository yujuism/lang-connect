# LangConnect - Factories Guide

## Overview

Factories have been created for all main models to make testing and demo data generation easy. Factories use Laravel's Faker library to generate realistic fake data.

## Available Factories

### 1. UserProgressFactory
Generates user progress with realistic levels and statistics:
- **contribution_hours**: 0-150 hours
- **level**: Auto-calculated (1-10) based on contribution hours
- **karma_points**: 0-5000 points
- **total_sessions**: 0-200 sessions
- **members_helped**: 0-50 unique members
- **sessions_received**: 0-150 sessions

### 2. LearningRequestFactory
Generates learning requests with various topics:
- **Topics**: Grammar (past_tense, present_perfect, future_tense, conditionals, subjunctive)
- **Topics**: Pronunciation (r_sound, th_sound, intonation, stress_patterns, vowel_sounds)
- **Topics**: Vocabulary (ordering_food, business_terms, daily_conversation, travel_phrases)
- **Proficiency Levels**: A1, A2, B1, B2, C1, C2
- **Statuses**: pending, matched, completed, cancelled
- **Realistic Questions**: Sample questions like "How do I use the past tense correctly?"

### 3. PracticeSessionFactory
Generates practice sessions with realistic data:
- **Session Types**: random, scheduled, workshop
- **Topics**: Past Tense Practice, Pronunciation Training, Business Conversation, Daily Life Chat, Grammar Review
- **Duration**: 15-120 minutes
- **Statuses**: scheduled, in_progress, completed, cancelled
- **Timestamps**: Started/completed dates in last 30 days

### 4. SessionReviewFactory
Generates multi-dimensional reviews:
- **Overall Rating**: 3-5 stars
- **Dimension Ratings**: helpfulness, patience, clarity, engagement (slightly varied from overall)
- **Comments**: Realistic positive comments
- **Public Visibility**: 80% public, 20% private
- **Topics Rated Well**: Random selection of 1-3 topics (grammar, pronunciation, vocabulary, conversation)

## Demo Data Seeder

The `DemoDataSeeder` creates a realistic dataset using these factories:

### What It Creates:
- **20 Users** with progress tracking
- **1-3 Languages** per user (native or learning)
- **~15 Learning Requests** from users learning languages
- **~30 Practice Sessions** between users with common languages

### Running Demo Data Seeder

```bash
# Run just the demo data seeder
DB_DATABASE=langconnect DB_PASSWORD=rootpassword php artisan db:seed --class=DemoDataSeeder

# Or run with interactive prompt
DB_DATABASE=langconnect DB_PASSWORD=rootpassword php artisan db:seed
# (will ask if you want demo data)
```

## Usage Examples

### Create Single Records

```php
// Create a user with progress
$user = User::factory()->create();
$progress = UserProgress::factory()->create(['user_id' => $user->id]);

// Create a learning request
$request = LearningRequest::factory()->create([
    'user_id' => $user->id,
    'language_id' => 1, // Spanish
    'status' => 'pending',
]);

// Create a practice session
$session = PracticeSession::factory()->create([
    'user1_id' => $user1->id,
    'user2_id' => $user2->id,
    'language_id' => 1,
]);
```

### Create Multiple Records

```php
// Create 10 users
User::factory(10)->create();

// Create 20 learning requests
LearningRequest::factory(20)->create();

// Create 50 sessions
PracticeSession::factory(50)->create();
```

### Create With Relationships

```php
// Create user with progress and languages
$user = User::factory()->create();

UserProgress::factory()->create(['user_id' => $user->id]);

UserLanguage::create([
    'user_id' => $user->id,
    'language_id' => 1, // English
    'proficiency_level' => 'native',
    'is_native' => true,
    'is_learning' => false,
]);

UserLanguage::create([
    'user_id' => $user->id,
    'language_id' => 2, // Spanish
    'proficiency_level' => 'B1',
    'is_native' => false,
    'is_learning' => true,
]);
```

## Testing with Factories

### Feature Test Example

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\LearningRequest;

class LearningRequestTest extends TestCase
{
    public function test_user_can_create_learning_request()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/learning-requests', [
            'language_id' => 1,
            'topic_category' => 'grammar',
            'topic_name' => 'past_tense',
            'specific_question' => 'How do I use past tense?',
            'proficiency_level' => 'A2',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('learning_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_matching_algorithm()
    {
        $learner = User::factory()->create();
        $helper = User::factory()->create();

        $request = LearningRequest::factory()->create([
            'user_id' => $learner->id,
            'language_id' => 1,
            'topic_category' => 'grammar',
            'status' => 'pending',
        ]);

        // Run matching logic
        $matched = MatchingService::findPartner($request);

        $this->assertNotNull($matched);
        $this->assertEquals('matched', $request->fresh()->status);
    }
}
```

## Factory Customization

### Custom States

You can define custom states in the factories:

```php
// In LearningRequestFactory.php
public function pending()
{
    return $this->state(fn (array $attributes) => [
        'status' => 'pending',
        'matched_with_user_id' => null,
        'matched_at' => null,
    ]);
}

public function matched()
{
    return $this->state(fn (array $attributes) => [
        'status' => 'matched',
        'matched_with_user_id' => User::factory(),
        'matched_at' => now(),
    ]);
}

// Usage
LearningRequest::factory()->pending()->create();
LearningRequest::factory()->matched()->create();
```

## Best Practices

1. **Use Factories in Tests**: Always use factories instead of manually creating records
2. **Override Specific Fields**: Pass array to `create()` to override specific fields
3. **Relationships**: Use `for()` method for belongs-to relationships
4. **Sequences**: Use `sequence()` for creating varied data
5. **Count**: Use `count()` or just pass number: `User::factory(10)->create()`

## Factory Files Location

```
database/factories/
├── UserFactory.php (Laravel default)
├── UserProgressFactory.php
├── LearningRequestFactory.php
├── PracticeSessionFactory.php
├── SessionReviewFactory.php
├── LanguageFactory.php
├── UserLanguageFactory.php
├── AchievementFactory.php
├── TopicMasteryFactory.php
└── UserExpertiseFactory.php
```

## Data Realism

All factories generate realistic data:
- **Names**: Uses Faker's name generator
- **Dates**: Realistic date ranges (last 30 days for sessions)
- **Ratings**: Skewed toward positive (3-5 stars)
- **Topics**: Actual language learning topics
- **Questions**: Real questions language learners might ask
- **Comments**: Genuine-sounding feedback

This makes the demo data useful for:
- Development and testing
- Screenshots and demos
- User acceptance testing
- Performance testing with realistic datasets

## Notes

- All models now have the `HasFactory` trait added
- Factories auto-create related models where needed (using `User::factory()` for foreign keys)
- The demo seeder is smart about relationships (only creates sessions between users with common languages)
- You can run the seeder multiple times to add more data
