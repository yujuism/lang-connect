<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Found - LangConnect</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .match-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .helper-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .helper-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
            margin-right: 15px;
        }
        .helper-details h3 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 20px;
        }
        .helper-details p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .request-details {
            background-color: white;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        .request-details h4 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 16px;
        }
        .request-details p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #667eea;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .next-steps {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .next-steps h4 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🎉 Great News, {{ $requester->name }}!</h1>
            <p>Your learning request has been matched</p>
        </div>

        <div class="email-body">
            <p>Hello {{ $requester->name }},</p>
            <p>We have exciting news! <strong>{{ $helper->name }}</strong> has accepted your learning request and is ready to help you improve your {{ $learningRequest->language->name }} skills.</p>

            <div class="match-card">
                <div class="helper-info">
                    <div class="helper-avatar">
                        {{ strtoupper(substr($helper->name, 0, 1)) }}
                    </div>
                    <div class="helper-details">
                        <h3>{{ $helper->name }}</h3>
                        <p>
                            @if($helper->progress)
                                Level {{ $helper->progress->level }} • {{ $helper->progress->karma_points }} Karma
                            @else
                                Your Language Learning Partner
                            @endif
                        </p>
                    </div>
                </div>

                <div class="request-details">
                    <h4>📚 Your Learning Request</h4>
                    <p><strong>Language:</strong> <span class="badge">{{ $learningRequest->language->name }}</span></p>
                    <p><strong>Topic:</strong> {{ ucfirst($learningRequest->topic_category) }}
                        @if($learningRequest->topic_name)
                            - {{ $learningRequest->topic_name }}
                        @endif
                    </p>
                    <p><strong>Your Level:</strong> {{ $learningRequest->proficiency_level }}</p>
                    @if($learningRequest->specific_question)
                        <div class="divider"></div>
                        <p><strong>Your Question:</strong></p>
                        <p style="font-style: italic; color: #666;">{{ $learningRequest->specific_question }}</p>
                    @endif
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/messages/' . $helper->id) }}" class="cta-button">
                    💬 Start Chatting with {{ $helper->name }}
                </a>
            </div>

            <div class="next-steps">
                <h4>📋 Next Steps:</h4>
                <ul>
                    <li><strong>Send a message</strong> to {{ $helper->name }} to introduce yourself and schedule a session</li>
                    <li><strong>Discuss the topic</strong> you want to focus on during your practice session</li>
                    <li><strong>Schedule a time</strong> that works for both of you</li>
                    <li><strong>After your session</strong>, remember to leave a review to help the community</li>
                </ul>
            </div>

            <div class="divider"></div>

            <p style="color: #666; font-size: 14px;">
                <strong>💡 Tip:</strong> Be prepared with specific questions or topics you want to practice. The more focused your session, the more you'll learn!
            </p>
        </div>

        <div class="email-footer">
            <p><strong>LangConnect</strong> - Connect, Learn, Grow</p>
            <p>
                <a href="{{ url('/learning-requests/' . $learningRequest->id) }}">View Request Details</a> •
                <a href="{{ url('/profile/' . $requester->id) }}">Your Profile</a> •
                <a href="{{ url('/dashboard') }}">Dashboard</a>
            </p>
            <p style="margin-top: 15px;">
                You're receiving this email because someone accepted your learning request on LangConnect.<br>
                <a href="{{ url('/profile/edit') }}">Manage email preferences</a>
            </p>
        </div>
    </div>
</body>
</html>