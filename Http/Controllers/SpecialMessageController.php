<?php

namespace Modules\Chat\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Chat\Models\SpecialMessage;

class SpecialMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['integer'],
        ]);
        $status = $request->get('status');
//        $specialMessages = SpecialMessage::when($status, function ($query) use ($status) {
//            \Log::info('Input status: ' . $status);
//            $query->filterByStatus($status);
//            \Log::info('Decoded status: ' . json_encode($status));
//        })->get();
//
        if (isset($status)) {
            $specialMessages = [];
            $messages = SpecialMessage::all();
            foreach ($messages as $specialMessage) {
                if (in_array($status, $specialMessage->status)) {
                    $specialMessages[] = $specialMessage;
                }
            }
        } else {
            $specialMessages = SpecialMessage::all();
        }
        return response()->json($specialMessages);
    }


}
