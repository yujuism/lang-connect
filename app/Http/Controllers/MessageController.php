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
     * Show messages inbox with unified split view
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $conversations = $this->messageService->getUserConversations($currentUser);

        // Auto-select first conversation if available
        $activeUser = null;
        $messages = collect();

        if ($conversations->isNotEmpty()) {
            $activeUser = $conversations->first()->user;
            $messages = $this->messageService->getConversation($currentUser, $activeUser);
            $this->messageService->markAsRead($activeUser, $currentUser);
        }

        return view('messages.index', compact('conversations', 'activeUser', 'messages'));
    }

    /**
     * Show conversation with specific user (unified view)
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $conversations = $this->messageService->getUserConversations($currentUser);
        $messages = $this->messageService->getConversation($currentUser, $user);
        $this->messageService->markAsRead($user, $currentUser);

        $activeUser = $user;

        return view('messages.index', compact('conversations', 'activeUser', 'messages'));
    }

    /**
     * Get conversations list as JSON (for real-time updates)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function conversationsJson()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $conversations = $this->messageService->getUserConversations($user);

        return response()->json([
            'conversations' => $conversations->map(fn($conv) => [
                'user_id' => $conv->user->id,
                'user_name' => $conv->user->name,
                'user_avatar' => $conv->user->avatar_path,
                'last_message_at' => $conv->last_message_at,
                'unread_count' => $conv->unread_count,
            ]),
        ]);
    }

    /**
     * Get messages for a conversation as JSON
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function messagesJson(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $messages = $this->messageService->getConversation($currentUser, $user);
        $this->messageService->markAsRead($user, $currentUser);

        return response()->json([
            'messages' => $messages,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_path' => $user->avatar_path,
            ],
        ]);
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
