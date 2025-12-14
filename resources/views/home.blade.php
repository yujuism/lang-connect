@extends('layout')

@section('title', 'Home - LangConnect')

@section('extra-css')
<style>
    .hero-section {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        padding: 5rem 0;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1.5rem;
        padding: 2rem;
    }

    .feature-icon {
        width: 64px;
        height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
        border-radius: 1rem;
        color: white;
        font-size: 1.75rem;
        margin-bottom: 1rem;
    }

    .language-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1.25rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .language-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-color);
    }

    .request-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .request-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .section-subtitle {
        color: var(--text-secondary);
        font-size: 1.125rem;
    }

    .badge-modern {
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<div class="hero-section text-white position-relative">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-3 fw-bold mb-4">Connect. Practice. Grow.</h1>
                <p class="lead mb-4 opacity-90" style="font-size: 1.25rem;">
                    Join a community-driven language exchange platform. Help others learn your native language and practice the languages you're learning - all for free.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="#" class="btn btn-light btn-lg px-4 py-3">
                        <i class="bi bi-search me-2"></i> Find a Partner
                    </a>
                    <a href="#" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="bi bi-plus-circle me-2"></i> Post Request
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stats-card">
                    <h4 class="mb-4 fw-semibold">Platform Statistics</h4>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="display-5 fw-bold mb-2">{{ $stats['languages_available'] }}</div>
                            <div class="small opacity-90">Languages</div>
                        </div>
                        <div class="col-4">
                            <div class="display-5 fw-bold mb-2">{{ $stats['total_sessions'] }}</div>
                            <div class="small opacity-90">Sessions</div>
                        </div>
                        <div class="col-4">
                            <div class="display-5 fw-bold mb-2">{{ $stats['active_members'] }}</div>
                            <div class="small opacity-90">Members</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Get started in just a few simple steps</p>
    </div>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="text-center">
                <div class="feature-icon mx-auto">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h5 class="fw-semibold mb-3">Create Profile</h5>
                <p class="text-secondary small">Tell us what languages you speak and want to learn</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="text-center">
                <div class="feature-icon mx-auto">
                    <i class="bi bi-search"></i>
                </div>
                <h5 class="fw-semibold mb-3">Find Partners</h5>
                <p class="text-secondary small">Post what you want to learn or browse requests</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="text-center">
                <div class="feature-icon mx-auto">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <h5 class="fw-semibold mb-3">Practice Together</h5>
                <p class="text-secondary small">Connect via text or voice and help each other</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="text-center">
                <div class="feature-icon mx-auto">
                    <i class="bi bi-trophy"></i>
                </div>
                <h5 class="fw-semibold mb-3">Earn Rewards</h5>
                <p class="text-secondary small">Unlock achievements, levels, and recognition</p>
            </div>
        </div>
    </div>
</div>

<!-- Available Languages -->
<div class="bg-white py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Available Languages</h2>
            <p class="section-subtitle">Practice with native speakers of {{ $languages->count() }} languages</p>
        </div>
        <div class="row g-3">
            @foreach($languages->take(12) as $language)
            <div class="col-6 col-md-4 col-lg-2">
                <div class="language-card">
                    <div class="fs-1 mb-2">{{ $language->flag_emoji }}</div>
                    <h6 class="mb-0 fw-semibold small">{{ $language->name }}</h6>
                </div>
            </div>
            @endforeach
        </div>
        @if($languages->count() > 12)
        <div class="text-center mt-4">
            <a href="#" class="btn btn-outline-primary">View All {{ $languages->count() }} Languages</a>
        </div>
        @endif
    </div>
</div>

<!-- Recent Learning Requests -->
@if($recentRequests->count() > 0)
<div class="container my-5 py-5">
    <div class="mb-5">
        <h2 class="section-title">Recent Learning Requests</h2>
        <p class="section-subtitle">Help someone learn today</p>
    </div>
    <div class="row g-4">
        @foreach($recentRequests->take(6) as $request)
        <div class="col-md-6 col-lg-4">
            <div class="request-card p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-person fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ $request->user->name }}</h6>
                        <small class="text-secondary">{{ $request->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="badge badge-modern bg-primary text-white me-2">{{ $request->language->flag_emoji }} {{ $request->language->name }}</span>
                    <span class="badge badge-modern" style="background-color: #e0e7ff; color: #4f46e5;">{{ ucfirst($request->topic_category) }}</span>
                    <span class="badge badge-modern" style="background-color: #f3f4f6; color: #6b7280;">{{ $request->proficiency_level }}</span>
                </div>
                @if($request->topic_name)
                <h6 class="fw-semibold mb-2">{{ ucfirst(str_replace('_', ' ', $request->topic_name)) }}</h6>
                @endif
                @if($request->specific_question)
                <p class="text-secondary small mb-3">{{ Str::limit($request->specific_question, 100) }}</p>
                @endif
                <a href="#" class="btn btn-primary w-100">
                    <i class="bi bi-hand-thumbs-up me-2"></i> I Can Help
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Features -->
<div class="bg-white py-5">
    <div class="container my-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Join LangConnect?</h2>
            <p class="section-subtitle">Everything you need for successful language exchange</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">100% Free</h5>
                    <p class="text-secondary">No credits, no payments. Pure community exchange based on helping each other.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-chat-square-heart"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Intent-Based Matching</h5>
                    <p class="text-secondary">Post exactly what you want to learn RIGHT NOW and get matched with experts.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Track Progress</h5>
                    <p class="text-secondary">Visual skill trees, topic mastery tracking, and achievement system.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Community Status</h5>
                    <p class="text-secondary">Earn karma points, unlock levels, and get recognized as a helpful member.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Unlock Features</h5>
                    <p class="text-secondary">Help others to create groups, host workshops, and customize your profile.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Quality Ratings</h5>
                    <p class="text-secondary">Multi-dimensional ratings help you find partners matching your learning style.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="container my-5 py-5 text-center">
    <h2 class="section-title mb-3">Ready to Start Your Language Journey?</h2>
    <p class="section-subtitle mb-4">Join thousands of language learners helping each other grow</p>
    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
        <a href="#" class="btn btn-primary btn-lg px-5 py-3">
            <i class="bi bi-person-plus me-2"></i> Create Free Account
        </a>
        <a href="#" class="btn btn-outline-primary btn-lg px-5 py-3">
            <i class="bi bi-info-circle me-2"></i> Learn More
        </a>
    </div>
</div>
@endsection
