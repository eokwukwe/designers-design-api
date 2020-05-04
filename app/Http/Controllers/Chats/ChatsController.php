<?php

namespace App\Http\Controllers\Chats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Repositories\Contracts\IChat;
use App\Repositories\Contracts\IMessage;
use App\Repositories\Eloquent\Criteria\WithTrashed;

class ChatsController extends Controller
{
    protected $chats;
    protected $messages;

    public function __construct(IChat $chats, IMessage $messages)
    {
        $this->chats = $chats;
        $this->messages = $messages;
    }

    /**
     * Send message to user
     */
    public function sendMessage(Request $request)
    {
        $this->validate($request, [
            'recipient' => ['required'],
            'body' => ['required']
        ]);

        $recipient = $request->recipient;
        $user = $request->user(); // Same as auth()->user()
        $body = $request->body;

        // check if the user already has an existing chat with the recipient
        $chat = $user->getChatWithUser($recipient);
        if (!$chat) {
            // create a chat
            $chat = $this->chats->create([]);

            // create the participants
            $this->chats->createParticipants(
                $chat->id,
                [$user->id, $recipient]
            );
        }

        // add message to chat
        $message = $this->messages->create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => $body,
            'last_read' => null,
        ]);

        return new MessageResource($message);
    }

    /**
     * Get chats for user
     */
    public function getUserChats(Request $request)
    {
        $chats = $this->chats->getUserChats(auth()->id());
        return ChatResource::collection($chats);
    }

    /**
     * Get messages for a chat
     */
    public function getChatMessages($id)
    {
        $messages = $this->messages->withCriteria([
            new WithTrashed
        ])->findWhere('chat_id', $id);
        return MessageResource::collection($messages);
    }

    /**
     * Mark a message as read
     */
    public function markAsRead($id)
    {
        $chat = $this->chats->find($id);
        $chat->markAsReadForUser(auth()->id());

        return response()->json(['message' => 'Successful'], 200);
    }

    /**
     * Delete message from chat
     */
    public function destroyMessage($id)
    {
        $message = $this->messages->find($id);
        $this->authorize('delete', $message);
        $message->delete();

        return response()->json(['message' => 'Successful'], 200);
    }
}
