<?php

namespace Modules\Chat\ChatTypes\SupportChat\Http\Controllers\Api\v1;

use App\Enums\UserRoleEnum;
use App\Exceptions\MobileConfirmException;
use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\Models\Chat;
use Throwable;

class SupportChatController extends Controller
{
    private function setMobile($user , $mobile){
        $user->cell = $mobile;
        $user->save();
    }

    public function __construct()
    {
        $this->userRole = auth('sanctum')->user()?->getRole() ?? null;
        $this->user = auth('sanctum')->user()?? null;

    }

    /**
     * @throws MobileConfirmException
     */
    private function makeTemporaryUser($name=null, $mobile){
        $user = User::where('cell', $mobile)->first();
            if (!$user) {
                $user = User::create(['name' => $name, 'cell' => $mobile]);
                $user->patient()->create(['user_id' => $user->id]);
                throw new  MobileConfirmException();
            }
        return $user;
    }
    /**
     * SupportChat Index
     *
     * Display a list of supportChats.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws ValidationException
     * @group SupportChat
     *
     * @bodyParam page integer Number of page by default is 1 Example: 1
     * @bodyParam user_id integer Example: 60
     *
     * @responseFile status=200 scenario="When list successfully fetched" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/list-200.json
     */
    public function index(Request $request): LengthAwarePaginator
    {
        /**
         * supportChat list
         *
         * @get(/api/v1/chat/support-chat)
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */


        $this->validate($request, [
            'user_id' =>['integer',Rule::exists(User::TABLE, 'id')],
        ]);

        if ($this->userRole !== 'admin') {
            $userId = $this->user->id;
        } else {
            $userId = $request->get('user_id');
        }

        $supportChatList = SupportChat::with('chat','chat.users');
        if ($userId) {
            $supportChatList->whereRelation("chat", function ($query) use ($userId) {
                $query->where("create_user_id", $userId);
            });
        }

        return $supportChatList->paginate();
    }

    /**
     * Store supportChat
     *
     * Store a new created supportChat in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     *
     * @group SupportChat
     *
     * @bodyParam title string required title of supportChat Example: iran
     * @bodyParam content string required content of supportChat Example: doctor Nasiri didn't answer me please change my doctor
     * @bodyParam name string Example: bita daghestani
     * @bodyParam mobile mobile mandatory if not existed Example: 09153514007
     * @bodyParam support_reason_id integer required Example: 1
     * @bodyParam related_question_id integer Example: 1
     * @responseFile status=201 scenario="When successfully stored" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/store-422-invalid-data.json
     */
    public function store(Request $request)
    {
        /**
         * store a new supportChat
         *
         * @post(/api/v1/chat/support-chat)
         */;
       $cell = $this->user->cell ?? false;
        $chatDataToSave = $this->validate($request, [
            'title' => ['required', 'string', 'min:10'],
            'content' => ['required', 'string', 'min:10'],
        ]);

        $this->validate($request, [
            'name' => ['string', 'min:5'],
            'mobile' => [Rule::requiredIf(!$this->user || !$cell), Rule::phone()->detect()->type('mobile')],
            'support_reason_id' => ['int', 'required'],
            'related_question_id' => ['int'],
        ]);

        if($this->user){
            $login = 1;
            if(!$cell){
                $this->setMobile($this->user , $request->get('mobile'));
                throw new  MobileConfirmException();
            }
        }
        else{
            $user = $this->makeTemporaryUser($request->get('name'),$request->get('mobile'));
        }

        $chatDataToSave['create_user_id'] =
        $chatDataToSave['last_respond_id'] =
//        $chatDataToSave['suspend_user_id'] =
            $this->user->id ?? $user->id;

        try {
            DB::beginTransaction();
            $chat = Chat::create($chatDataToSave);
            $chat->supportChat()->save(SupportChat::make([
                'support_reason_id' => $request->get('support_reason_id'),
                'related_question_id' => $request->get('related_question_id')??0,
                'is_login' => $login ?? false
            ]));

            $chat->users()->attach($this->user, ['role' => UserRoleEnum::Patient]);
            DB::commit();

            $data = Chat::where('id',$chat->id)
                ->with('supportChat','users')
                ->first();

              return $this->successCreatedResponse(
                  $data
               );

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Show SupportChat
     *
     * Show the specified resource.
     *
     * @group SupportChat
     *
     * @param SupportChat $supportChat
     * @return JsonResponse
     * @throws Throwable
     * @group supportChat
     *
     * @responseFile status=200 scenario="When successfully show" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/show-200.json
     *
     * @responseFile status=404 scenario="When not found" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/404-not-found.json
     */
    public function show(SupportChat $supportChat): JsonResponse
    {
        /**
         * show a specific supportChat
         *
         * @get(/api/v1/chat/support-chat/{supportChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $chat = Chat::where('id',$supportChat->chat_id)
            ->with('supportChat','users')
            ->first();
            return response()->json($chat);
    }

    /**
     * update supportChat
     *
     * update a  created support in storage.
     *
     * @param Request $request
     * @param SupportChat $supportChat
     * @return jsonResponse
     * @throws Throwable
     *
     * @group SupportChat
     *
     * @bodyParam title string title of supportChat Example: iran
     * @bodyParam content string content of supportChat Example: doctor Nasiri didn't answer me please change my doctor
     * @bodyParam support_reason_id integer Example: 1
     * @bodyParam related_question_id integer Example: 1
     *
     * @responseFile status=201 scenario="When successfully stored" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/store-422-invalid-data.json
     */
    public function update(Request $request, SupportChat $supportChat): jsonResponse
    {
        /**
         * update a specified supportChat
         *
         * @put(/api/v1/chat/support-chat/{supportChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $validateChat = $this->validate($request, [
            'title' => ['string', 'min:15'],
            'content' => ['string', 'min:15'],
        ]);
        $validateSupportChat = $this->validate($request, [
            'support_reason_id' => ['int'],
            'related_question_id' => ['int'],
        ]);

        $creator_id = $supportChat->chat->create_user_id;
        if ($this->userRole != 'admin' && $this->user->id != $creator_id) {
            throw new AuthorizationException();
        }

        if ($validateSupportChat !== null) {
                $supportChat->updateOrFail($validateSupportChat);
        }
        if ($validateChat !== null) {
                $supportChat->chat->updateOrFail($validateChat);
        }
            return $this->show($supportChat);
    }

    /**
     * Remove supportchat
     *
     * Remove the specified resource from storage.
     *
     * @param SupportChat $supportChat
     * @return JsonResponse
     * @throws Throwable
     *
     * @group SupportChat
     *
     * @responseFile status=200 scenario="When successfully deleted" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/delete-200.json
     *
     * @responseFile status=404 scenario="When not found" Modules/Chat/ChatTypes/SupportChat/Storage/example-response/supportChat/404-not-found.json
     */
    public function destroy(SupportChat $supportChat): JsonResponse
    {
        /**
         * delete a specified supportChat
         *
         * @delete(/api/v1/chat/support-chat/{supportChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $creator_id = $supportChat->chat->create_user_id;
        if ($this->userRole != 'admin' && $this->user->id != $creator_id) {
            throw new AuthorizationException();
        } else {
            try {
                DB::beginTransaction();
                $supportChat->deleteOrFail();
                $supportChat->chat->deleteOrFail();
                DB::commit();
                return $this->successDeletedResponse();
            } catch (Throwable $exception) {
                DB::rollBack();
                throw $exception;
            }
        }
    }
}

