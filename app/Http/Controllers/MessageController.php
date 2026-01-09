<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\SendMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MessageController - Handles messaging HTTP requests
 *
 * Responsibilities:
 * - Routing messaging requests
 * - Delegating to MessageService
 * - Returning views and JSON responses
 */
class MessageController extends Controller
{
    /**
     * Inject MessageService
     */
    public function __construct(
        private MessageService $messageService
    ) {}

    /**
     * Show messages inbox with conversation list
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $conversations = $this->messageService->getUserConversations($user);

        return view('messages.index', compact('conversations'));
    }

    /**
     * Show conversation with specific user
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Get messages
        $messages = $this->messageService->getConversation($currentUser, $user);

        // Mark messages as read
        $this->messageService->markAsRead($user, $currentUser);

        return view('messages.show', compact('user', 'messages'));
    }

    /**
     * Send a message
     *
     * @param SendMessageRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function send(SendMessageRequest $request, User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $message = $this->messageService->sendMessage(
            $currentUser,
            $user,
            $request->validated()['message']
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('messages.show', $user)
            ->with('success', 'Message sent!');
    }

    /**
     * Mark messages from a user as read
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function markRead(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $count = $this->messageService->markAsRead($user, $currentUser);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
        ]);
    }

    /**
     * Fetch new messages (for AJAX polling)
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetch(User $user, Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $lastMessageId = $request->input('last_message_id', 0);

        $messages = $this->messageService->fetchNewMessages(
            $currentUser,
            $user,
            $lastMessageId
        );

        return response()->json([
            'messages' => $messages,
            'unread_count' => $currentUser->getUnreadMessageCount(),
        ]);
    }
}
