<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    /**
     * Send message to user
     */
    public function sendMessage(Request $request)
    {
        # code...
    }

    /**
     * Get chats for user
     */
    public function getUserChats(Request $request)
    {
        # code...
    }

    /**
     * Get messages for chat
     */
    public function getChatMessages($id)
    {
        # code...
    }

    /**
     * Mark a message as read
     */
    public function markAsRead($id)
    {
        # code...
    }
    
    /**
     * Delete message from chat
     */
    public function destroyMessage($id)
    {
        # code...
    }


}
