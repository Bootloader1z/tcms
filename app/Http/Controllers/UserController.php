<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\G5ChatMessage;
class UserController extends Controller
{
 
//     public function getUserMessages(User $user)
// {
//     // Fetch messages for the specified user
//     $messages = G5ChatMessage::where('user_id', $user->id)->get();

//     return response()->json(['messages' => $messages]);
// }
// public function startChat($userId)
// {
//     try {
//         // Fetch the messages between the current user and the selected user
//         $messages = G5ChatMessage::where(function($query) use ($userId) {
//             $query->where('user_id', Auth::id())->where('receiver_id', $userId);
//         })->orWhere(function($query) use ($userId) {
//             $query->where('user_id', $userId)->where('receiver_id', Auth::id());
//         })->latest()->with('user', 'receiver')->limit(10)->get();

//         // Transform messages and format date
//         $messages->transform(function ($message) {
//             $message->created_at_formatted = Carbon::parse($message->created_at)->format('M d, Y H:i A');
//             return $message;
//         });

//         // Get current user details
//         $user = Auth::user();

//         // Prepare the response data
//         $data = [
//             'messages' => $messages,
//             'user' => $user,
//         ];

//         // Return the data as JSON
//         return response()->json($data);
//     } catch (\Exception $e) {
//         // Log the error
//         Log::error('Error fetching chat messages: ' . $e->getMessage());
        
//         // Return an error response
//         return response()->json(['error' => 'Failed to fetch chat messages.'], 500);
//     }
// }

}
