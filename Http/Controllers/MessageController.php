<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use DB;
use http\Env\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Modules\Chat\ChatTypes\ConsultingChat\Exceptions\EditRulesException;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Events\SetConsultingStatus;
use Modules\Chat\Events\SetSupportStatus;
use Modules\Chat\Exceptions\RateToDoctorException;
use Modules\Chat\Jobs\UpdateAverageTime;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\DoctorUserBlocked;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\SpecialMessage;
use Modules\Doctor\Models\Doctor;
use Modules\Location\Models\Location;
use Modules\Location\Models\Place;
use Modules\Location\Models\Province;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;


class MessageController extends Controller
{
    private ?User $user;

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function processFileUploads($files, Message $message): array
    {
        if (isset($files)) {
            $fileReferences = [];

            foreach ($files as $file) {
                $fileFormat = $file->getClientOriginalExtension();
                $fileName = md5($message->id . $file->getClientOriginalName() . time());

                $media = $message->addMedia($file)
                    ->setName($fileName)
                    ->setFileName($fileName . '.' . $fileFormat)
                    ->toMediaCollection('chatPictures', 'files');

                $fileReferences[] = [
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                ];
            }
            return $fileReferences;
        }
        return [];
    }

    private function seenMessages($chatMessageIds)
    {
        $update = false;
        $updatedMessage = 0;

        foreach ($chatMessageIds as $messageId) {
            $message = Message::where([
                'id' => $messageId,
                'is_delete' => 0,
            ])->first();
            if ($message && !in_array($this->user->id, $message->seen)) {
                $seen = $message->seen;
                $seen[] = $this->user->id;
                $updatedMessage = Message::where('id', $messageId)->update(['seen' => $seen]);
                broadcast(new MessageSent($this->user, $updatedMessage, $message->chat_id));
            }
        }
        if ($updatedMessage > 0) {
            $update = true; // Set the flag to true if an update occurred
        }
        return $update;
    }

    public function deliveredMessages($chatMessageIds)
    {
        $update = false;
        $updatedMessage = 0;
        foreach ($chatMessageIds as $messageId) {
            $message = Message::where([
                'id' => $messageId,
                'is_delete' => 0,
            ])->first();
            if ($message && !in_array($this->user->id, $message->delivered)) {
                $delivered = $message->delivered;
                $delivered[] = $this->user->id;
                $updatedMessage = Message::where('id', $messageId)->update(['delivered' => $delivered]);
                broadcast(new MessageSent($this->user, $updatedMessage, $message->chat_id));
            }
        }
        if ($updatedMessage > 0) {
            $update = true;
        }
        return $update;
    }

    public function sendInfo($chat)
    {
        $doctorUser = $this->user;
        $role = $doctorUser->getRole();

        $doctor = Doctor::where('user_id', $doctorUser->id)->first();
        if($role == 'doctor'){
            return Place::where('placeable_type', 'Modules\Doctor\Models\Doctor')
                ->where('placeable_id', $doctor->id)
                ->with('province','city','location')
                ->get();
        }

    }

    public function phoneCallNeeded($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_CLOSE;
        $consultingChat->save();

    }

    public function blockUser($chat)
    {
        $doctor = $this->user;
        $role = $doctor->getRole();
        if($role == 'doctor'){
            $user = $chat->users()->where('role','patient')->first();
            $blockUser = [
                $user->id => ['block_type'=>DoctorUserBlocked::BLOCK_TYPE_QUESTION],
            ];
            $doctor->userBlocked()->attach($blockUser);
        }
    }

    public function consultingEnded($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_CLOSE;
        $consultingChat->save();
    }

    public function consultingEndedAndArchived($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_CLOSE;
        $consultingChat->save();
    }

    public function consultingArchived($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_DOCTOR_RESPOND;
        $consultingChat->save();

    }

    /**
     * @throws EditRulesException
     */
    public function rateToDoctor($chat)
    {
       throw new EditRulesException();
    }

    public function refundRequest($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_REFUND;
        $consultingChat->save();


    }

    public function notMySpecialty($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_DOCTOR_FREE;
        $consultingChat->save();
        $users =  $chat->users;
           foreach ($users as $user){
               if ($user->getRole() === 'doctor') {
                   $chat->users()->detach($user->id);
               }
           }
       //           TODO sending message to User
    }

    public function notInMyInterests($chat)
    {
        $consultingChat = $chat->consultingChat;
        $consultingChat->status = ConsultingChat::STATUS_DOCTOR_FREE;
        $consultingChat->save();
        $users =  $chat->users;
        foreach ($users as $user){
            if ($user->getRole() === 'doctor') {
                $chat->users()->detach($user->id);
            }
        }
        //           TODO sending message to User

    }

    public function __construct()
    {
        $this->user = auth('sanctum')->user() ?? null;
    }

    /**
     * Message index
     *
     * Display a chat's messages list.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     * @group Message
     *
     * @bodyParam chat_id integer required Example: 60
     *
     * @responseFile status=200 scenario="When chat's messages list successfully fetched" Modules/Chat/Storage/chat/List-200.json
     * @responseFile status=422 scenario="When data in invalid" Modules/Chat/Storage/chat/List-invalid-422.json
     * @throws AuthorizationException
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $request->validate([
            'chat_id' => ['required', Rule::exists(Chat::TABLE, 'id')],
        ]);

        $chatId = $request->get('chat_id');
        $chat = Chat::findOrFail($chatId);

        if (!$chat->users->contains($this->user)) {
            throw new AuthorizationException();
        }

            broadcast(new messageSent($this->user, null, $chatId));

            $chatMessageIds = Message::where('chat_id', $chatId)
                ->where('user_id', '!=', $this->user->id)
                ->orderBy('id')
                ->pluck('id')->toArray();

            $this->deliveredMessages($chatMessageIds);
            $this->seenMessages($chatMessageIds);

        return Message::where('chat_id', $chatId)
                ->with('user')
                ->orderBy('id')
                ->paginate();
    }


    /**
     * Message store
     *
     * store a message for a certain chat.
     *
     * @param Request $request
     * @return JsonResponse
     * @group Message
     *
     * @bodyParam text string required Example: hello
     * @bodyParam special_message_id integer Example: 1
     * @bodyParam chat_id integer required Example: 14
     * @bodyParam respond_to integer Example: 1
     * @bodyParam pictures files
     *
     * @throws Throwable
     *
     * @responseFile status=200 scenario="when a message sending in a chat" Modules/Chat/Storage/chat/Store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/Storage/chat/Store-422-invalid-data.json
     */
    public function store(Request $request)
    {
       $request->validate([
            'text' => ['required_without:special_message_id', 'string'],
            'special_message_id' => ['required_without:text', Rule::exists(SpecialMessage::TABLE, 'id')],
            'chat_id' => ['required', Rule::exists(Chat::TABLE, 'id')],
            'respond_to' => [Rule::exists(Message::TABLE, 'id')],
            'pictures' => ['array', 'nullable'],
            'pictures.*' => ['file', 'mimes:jpeg,png,gif,bmp,webp,jpg', 'max:2048'],
        ]);
        $chat = Chat::findOrFail($request->get('chat_id'));
        $data = $request->except('pictures');

        $files = $request->file('pictures');


        if (!$chat->users->contains($this->user)) {
            throw new AuthorizationException();
        }

        if($request->get('special_message_id')) {
            $specialMessageId = $request->get('special_message_id');
            $specialMessage = SpecialMessage::whereId($specialMessageId)->first();
            $method = $specialMessage->controller_method;

            if($method != null) {
                if ($method != 'sendInfo') {
                    $this->$method($chat);
                    $content = $specialMessage->content;
                    $data['text'] = $content;
                } else {
                    $m = $this->$method($chat);
                }
            }else{
                $content = $specialMessage->content;
                $data['text'] = $content;
            }
        }

        try {
            DB::beginTransaction();

            $data['user_id'] = $this->user->id;

            $message = Message::create($data);
            $chatId = $request->get('chat_id');

            $fileReferences = $this->processFileUploads($files,$message);

            Chat::where('id', $chatId)
                ->update([
                    'last_respond_id' => $this->user->id,
                    'last_answer_time' => Carbon::now(),
                ]);

            DB::commit();
            if($chat->supportChat){
                event(new SetSupportStatus($chatId, $this->user));
            }elseif ($chat->consultingChat){
                $consultingChat = ConsultingChat::where('chat_id', $chatId)->first();
                if ($consultingChat->doctor_first_answer_at == null && $this->user->getRole() == 'doctor') {
                    UpdateAverageTime::dispatch($chatId, $this->user);
                }
                event(new SetConsultingStatus($chatId, $this->user));
            }

            broadcast(new messageSent($this->user, $message, $chatId));

            if (isset($method) && $method == 'sendInfo') {
                $response = [
                    'message' => $message,
                    'files' => $fileReferences,
                    'doctorInfo' => $m ?? ' ',
                ];
            } else {
                $response = [
                    'message' => $message,
                    'files' => $fileReferences,
                ];
            }

            return $this->successCreatedResponse($response);

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Message update
     *
     * edit a message for a certain chat.
     *
     * @param Request $request
     * @param Message $message
     * @return JsonResponse
     * @group Message
     *
     * @bodyParam text string required Example: hello
     *
     * @throws AuthorizationException
     * @responseFile status=200 scenario="when a message needs to be edited after sending" Modules/Chat/Storage/chat/Store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/Storage/chat/Store-422-invalid-data.json
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        if ($this->user && $this->user->id != $message->user_id) {
            throw new AuthorizationException();
        }
        $request->validate([
            'text' => ['required', 'string'],
        ]);
        if ($message->user_id == $this->user->id) {
            $message->text = $request->get('text');
            $message->is_edit = 1;
            $message->save();
        }
        $chatId = $message->chat_id;
        broadcast(new messageSent($this->user, $message, $chatId));
        return $this->successCreatedResponse(
            $message
        );
    }


    /**
     * Message destroy
     *
     * delete a message for a certain chat.
     *
     * @param Message $message
     * @retur           return $chat;
n JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @group Message
     *
     * @responseFile status=200 scenario="When successfully deleted" Modules/Chat/Storage/chat/delete-200.json
     * @responseFile status=404 scenario="When not found" Modules/Chat/Storage/chat/404-not-found.json
     */
    public function destroy(Message $message): JsonResponse
    {
        $chatId = $message->chat_id;
        if ($this->user && $this->user->id != $message->user_id) {
            throw new AuthorizationException();
        }
        try {
            if ($message->seen == [] && $message->is_delete == 0) {
                DB::beginTransaction();
                $message->is_delete = 1;
                $message->save();
                DB::commit();
                broadcast(new messageSent($this->user, $message, $chatId))->toOthers();
                return $this->successDeletedResponse();
            } else {
                return $this->errorMessage('the message has been seen or deleted before');
            }
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Message delivered
     *
     * message has been delivered to a certain user.
     *
     * @param Request $request
     * @bodyParam messageIds array required Example: ["23","24"]
     * @return JsonResponse
     * @throws Throwable
     * @group Message
     *
     * @responseFile status=200 scenario="when some messages delivered successfully" Modules/Chat/Storage/chat/Delivered-200.json
     */
    public function deliveredMessage(Request $request): JsonResponse
    {
        $request->validate([
            "messageIds" => ['required'],
        ]);
        $ids = $request->get('messageIds');
        $response = $this->deliveredMessages($ids);
        if($response){
            return $this->successMessageResponse('message delivered successfully');
        }else{
            return $this->errorMessage('the message has been delivered before');
        }
    }

    /**
     * Message seen
     *
     * message has been seen by a certain user.
     *
     * @param Request $request
     * @return JsonResponse
     * @bodyParam messageIds array required Example: ["23","24"]
     * @group Message
     *
     * @responseFile status=200 scenario="when some messages have been seen successfully" Modules/Chat/Storage/chat/Seen-200.json
     */
    public function seenMessage(Request $request): JsonResponse
    {
        $request->validate([
            "messageIds" => ['required'],
        ]);
        $response = $this->seenMessages($request->get('messageIds'));
        if($response){
            return $this->successMessageResponse('message seen successfully');
        }else{
            return $this->errorMessage('message has been seen before');
        }
    }
}
