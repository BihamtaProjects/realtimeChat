<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Http\Controllers\Api\v1;

use App\Enums\UserRoleEnum;
use App\Exceptions\MobileConfirmException;
use App\Http\Controllers\Controller;
use App\Models\RelatedPatient;
use App\Models\Setting;
use App\Models\User;
use App\Rules\IranianNationalCode;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Chat\ChatTypes\ConsultingChat\Events\VisitNumberCalculate;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\Models\Chat;
use Modules\Contract\Models\Hospital;
use Modules\Doctor\Models\Doctor;
use Modules\Financial\Models\Currency;
use Modules\Price\Models\Price;
use Str;
use Throwable;

class ConsultingChatController extends Controller
{
    private ?User $user;
    private ?string $userRole;
    private RelatedPatient|Model $related_patient_id;

    public function __construct()
    {
        $this->userRole = auth('sanctum')->user()?->getRole() ?? null;
        $this->user = auth('sanctum')->user()?? null;
    }

    private function setRelatedPatient($patient_nationalCode,$patient_name)
    {
        $related_patient = $this->user->relatedPatients()
            ->save(RelatedPatient::make([
                'national_code'=> $patient_nationalCode,
                'name'=> $patient_name,
            ]));

        if ($related_patient) {
            $this->related_patient = $related_patient;
        }
    }

    private function setMobile($user , $mobile){
        $user->cell = $mobile;
        $user->save();
    }

    /**
     * @throws MobileConfirmException
     */
    private function makeTemporaryUser($mobile, $name = null){
        $user = User::where('cell', $mobile)->first();
        if (!$user) {
            $user = User::create(['name' => $name, 'cell' => $mobile]);
            $user->patient()->create(['user_id' => $user->id]);
            throw new  MobileConfirmException();
        }
        return $user;
    }

    private function findUserOfDoctor($doctor_id)
    {
        $doctor = Doctor::findOrFail($doctor_id);
        event( new VisitNumberCalculate($doctor));
        $this->maindoctor = $doctor;
        $this->userDoc = User::where('id',$this->maindoctor->user_id)->first();
        $this->visit_number =  $this->maindoctor->visit_number;

    }

    private function getDoctorPrices($doctor_id, $currency, $hospital_id)
    {

        $doctor = Doctor::where('id',$doctor_id)->first();
        if ($doctor->custom_price_id !=null) {
            $price = CustomPrice::where('id',$doctor->custom_price_id)->first();
        }
        else{
            $doctorSpecialty = $doctor->specialties()->orderByDesc('type')->first();

            $DoctotMainContractCount = $doctor->contracts()
                ->Where('contractable_type','Modules\Contract\Models\Hospital')
                ->Where('contractable_id',$hospital_id)
                ->count();

            if(!empty($hospital_id) && $DoctotMainContractCount >=1){
                $price = Price::Where('priceable_type','Modules\Contract\Models\Hospital')
                    ->Where('priceable_id',$hospital_id)
                    ->where('type',$doctorSpecialty->type)
                    ->first();
            }else{
                $price = Price::where('type', $doctorSpecialty->type)->first();
            }
        }
        if($currency == 1 ){
            $this->price = $price->question_price;
        }
        elseif($currency == 2){
            $this->price = $price->question_euro_price;
        }
    }
    private function getEmergencySetting($currency)
    {
        if($currency == 1 ){
            $setting = Setting::where('name', 'question_emergency_cost')->first();
        }
        elseif($currency == 2){
            $setting = Setting::where('name', 'question_euro_emergency_cost')->first();
        }
        $this->emergencyPrice = $setting->value;

    }

    /**
     * ConsultingChat index
     *
     * Display a listing of the ConsultingChats.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     * @throws ValidationException
     * @group ConsultingChat
     *
     * @bodyParam page integer Number of page by default is 1 Example: 1
     * @bodyParam user_id integer Example: 60
     *
     * @responseFile status=200 scenario="When list successfully fetched" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/list-200.json
     */
    public function index(Request $request): LengthAwarePaginator
    {
        /**
         * consultingChat list
         *
         * @get(/api/v1/chat/consulting-chat)
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $this->validate($request, [
            'user_id' =>['integer', Rule::exists(User::TABLE, 'id')],
        ]);

        if ($this->userRole !== 'admin') {
            $userId = $this->user->id;
        } else {
            $userId = $request->get('user_id');
        }

        $consultingChatList = ConsultingChat::with([
            'chat:id,title,content,last_respond_id,create_user_id',
            'chat.users:id,name,family'
        ]);

        if ($userId) {
            $consultingChatList->whereRelation("chat", function ($query) use ($userId) {
                $query->where("create_user_id", $userId);
            });
        }

        return $consultingChatList->paginate();
    }

    /**
     * Store consultingChat
     *
     * Store a newly created support in storage.
     *
     * @param Request $request
     * @return mixed
     * @throws Throwable
     *
     * @group ConsultingChat
     *
     * @bodyParam title string required title of consultingChat Example: iran
     * @bodyParam content string required content of consultingChat Example: 1
     * @bodyParam private boolean required shows users request for private consultingChat Example: 0
     * @bodyParam priority boolean required shows users request for a consultingChat with priority Example: 0
     * @bodyParam doctor_id integer required Example: 0
     * @bodyParam hospital_id integer shows if user select a doctor through a hospital Example: 0
     * @bodyParam currency integer required Example: 0
     * @bodyParam mobile mobile Example: 09153514007
     * @bodyParam national_code string user national code(Mandatory field when user wants to get a visit and national code not existed). Example: 1111111111
     * @bodyParam forOthers  boolean just to show user want to get a visit for others Example: 0
     * @bodyParam related_patient_national_code string user's national code(Mandatory field when user wants to get a visit for others). Example: 1111111111
     * @bodyParam related_patient_name  string user's name(Mandatory field when user wants to get a visit for others). Example: sina
 *
     * @responseFile status=201 scenario="When successfully stored" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-422-invalid-data.json
     */
    public function store(Request $request): mixed
    {
        /**
         * store a new consultingChat
         *
         * @post(/api/v1/chat/consulting-chat)
         */
        $national_code = $this->user->national_code ?? false;
        $forOthers = $request->get('forOthers');

        $chatDataToSave = $this->validate($request, [
            'title' => ['required', 'string', 'min:10'],
            'content' => ['required', 'string', 'min:15'],
        ]);

        $data = $this->validate($request, [
            'doctor_id' => ['required','integer', Rule::exists(Doctor::TABLE, 'id')],
            'hospital_id' => ['integer', Rule::exists(Hospital::TABLE, 'id')],
            'priority' => 'required|boolean',
            'private' => 'required|integer',
            'currency' => ['required', Rule::exists(Currency::TABLE, 'id')],
            'mobile' => [Rule::requiredIf(!$this->user || !$this->user->cell), Rule::phone()->detect()->type('mobile')],
            'national_code' => [Rule::requiredIf(!$this->user || !$national_code),  new IranianNationalCode()],
            'related_patient_national_code' => [Rule::requiredIf($forOthers === 1),  new IranianNationalCode()],
            'related_patient_name' => [Rule::requiredIf($forOthers === 1)],

        ]);


        if (!$this->user) {
//            dd($request->get('mobile'));
            $user = $this->makeTemporaryUser($request->get('name'),$request->get('mobile'));
        }

        $user = $this->user??$user;
        if (!$user->cell){
            $this->setMobile($user , $request->get('mobile'));
            if($user->mobile_confirm == false) {
                throw new  MobileConfirmException();
            }
        }

        if($request->has('related_patient_national_code')) {
            $patient = RelatedPatient::where('user_id',$this->user->id)
                ->where('national_code',$request->get('related_patient_national_code'))
                ->first();
            if(!$patient) {
                $this->setRelatedPatient($request->get('related_patient_national_code'), $request->get('related_patient_name'));
                $patient = $this->related_patient_id;
            }
        }

        $this->findUserofDoctor($data['doctor_id']);

        $chatDataToSave['create_user_id'] =
        $chatDataToSave['last_respond_id'] =
            $this->user->id ??$user->id;

        try {
            DB::beginTransaction();

            /** @var Chat $chat */
            $chat = Chat::make($chatDataToSave);
            $chat->saveOrFail();

            // create an array of userIds with it's pivot 'role'
            $chatUserItems = [
                $this->user->id ?? $user->id => ['role' => UserRoleEnum::Patient],
                $this->userDoc->id => ['role' => UserRoleEnum::Doctor]
            ];

            $chat->users()->attach($chatUserItems);

            do {
                $uniqueId = Str::random(8);
            } while (ConsultingChat::where('unique_id', $uniqueId)->count() !== 0);
             $chat->consultingChat()->save(ConsultingChat::make([
                'unique_id' => $uniqueId,
                'priority' => $request->get('priority'),
                'private' => $request->get('private'),
                'visit_number' => $this->visit_number,
                'related_patient_id' => $patient->id??0,
                'status' => ConsultingChat::STATUS_PENDING,
                'open_time' => Carbon::now(),
            ]));


//            $this->getDoctorPrices($data['doctor_id'], $data['currency'], isset($data['hospital_id'])??null);
//
//            $this->getEmergencySetting($data['currency']);
            //TODO::make invoices and send user to payment .
            ////////////
//            $invoiceDetails = [];
//            $invoice = new Invoice();
//            $invoice->currency_id = $data['currency'];
//            $invoice->user_id = $this->user->id;
//            $invoice->type  = Invoice::STATUS__WAITE_FOR_PAYMENT;
/////////////
//            if($data['priority']==1) {
//                $invoiceDetail = new InvoiceDetail();
//                $invoiceDetail->amount =$this->price+$this->emergencyPrice;
//                $invoiceDetail->type = InvoiceDetail::TYPE__PRIORITY_CHAT;
//                $invoiceDetails[] = $invoiceDetail;
//            }else{
//                $invoiceDetail = new InvoiceDetail();
//                $invoiceDetail->amount = $this->price;
//                $invoiceDetail->type = InvoiceDetail::TYPE__MAIN_COST;
//                $invoiceDetails[] = $invoiceDetail;
//            }
//            $consultingChat->invoice()->save($invoice);
//            $invoice->details()->saveMany($invoiceDetails);

            DB::commit();

            return $chat->consultingChat;
        }
         catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Show consultingChat
     *
     * Show the specified consultingChat.
     *
     * @group ConsultingChat
     *
     * @param  ConsultingChat $consultingChat
     * @return JsonResponse
     * @responseFile status=200 scenario="When successfully show" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/show-200.json
     *
     * @responseFile status=404 scenario="When not found" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/404-not-found.json
     */
    public function show(ConsultingChat $consultingChat): JsonResponse
    {
        /**
         * show a specific consultingChat
         *
         * @get(/api/v1/chat/consulting-chat/{consultingChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $chat = Chat::where('id', $consultingChat->chat_id)
            ->with('consultingChat', 'users')
            ->first();

        return response()->json($chat);
    }

    /**
     * update consultingChat
     *
     * Update a consultingChat in storage.
     *
     * @param Request $request
     * @param ConsultingChat $consultingChat
     * @return mixed
     * @throws AuthorizationException
     * @throws Throwable
     * @throws ValidationException
     *
     * @group ConsultingChat
     *
     * @bodyParam title string title of consultingChat Example: iran
     * @bodyParam content string content of consultingChat Example: 1
     * @bodyParam private integer Example: 0
     * @bodyParam priority integer Example: 0
     * @bodyParam doctor_id integer Example: 0
     * @bodyParam hospital_id integer shows if user select a doctor through a hospital Example: 0
     * @bodyParam currency integer Example: 0
     * @bodyParam forOthers  boolean just to show user want to get a visit for others Example: 0
     * @bodyParam related_patient_national_code string user's national code(Mandatory field when user wants to get a visit for others). Example: 1111111111
     * @bodyParam related_patient_name  string user's name(Mandatory field when user wants to get a visit for others). Example: sina
     *
     * @responseFile status=201 scenario="When successfully stored" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-422-invalid-data.json
     */
    public function update(Request $request, ConsultingChat $consultingChat): mixed
    {
        /**
         * update a specific consultingChat
         *
         * @put(/api/v1/chat/consulting-chat/{consultingChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $forOthers = $request->input('forOthers');

        $chatDataToSave = $this->validate($request, [
            'title' => 'string|min:5',
            'content' => ['string', 'min:10'],
        ]);
        $consultingChatToSave = $this->validate($request, [
            'priority' => 'boolean',
            'private' => 'boolean',
        ]);

        $data = $this->validate($request, [
            'doctor_id' => ['integer',Rule::exists(Doctor::TABLE, 'id')],
            'currency' => [Rule::exists(Currency::TABLE, 'id')],
        ]);

        $creator_id = $consultingChat->chat->create_user_id;
        if ($this->userRole != 'admin' && $this->user->id !== $creator_id) {
            throw new AuthorizationException();
        }

        if ($consultingChatToSave != null) {
            $consultingChat->updateOrFail($consultingChatToSave);
        }

        if ($chatDataToSave != null) {
           $consultingChat->chat->updateOrFail($chatDataToSave);

        }

        $chat = $consultingChat->chat;
        if(isset($data['doctor_id'])) {
            $this->findUserofDoctor($data['doctor_id']);
            $chat->users()->sync( [$this->userDoc->id => ['role' => UserRoleEnum::Doctor]]);
        }


        if ($forOthers === 1) {
            $relatedPatientToSave = $this->validate($request, [
                'related_patient_national_code' => ['require',  new IranianNationalCode()],
                'related_patient_name' => ['require'],
            ]);

            $patient = RelatedPatient::where('user_id', $this->user->id)
                ->where('national_code', $relatedPatientToSave['related_patient_national_code'])
                ->first();

            if (!$patient) {
                $this->setRelatedPatient($request->get('related_patient_national_code'), $request->get('related_patient_name'));
                $patient = $this->related_patient;
            }

            $consultingChat->related_patient_id = $patient->id;
            $consultingChat->save();
        }
        //TODO::make invoices and send user to payment .

//        if ($consultingChat->status == ConsultingChat::STATUS_PENDING && isset($data['currency'])){
//
//        }
        return $this->show($consultingChat);
    }


    /**
     * Remove consultingchat
     *
     * Remove the specified resource from storage.
     *
     * @param  ConsultingChat $consultingChat
     * @return JsonResponse
     * @throws Throwable
     *
     * @group ConsultingChat
     *
     * @responseFile status=200 scenario="When successfully deleted" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/delete-200.json
     *
     * @responseFile status=404 scenario="When not found" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/404-not-found.json
     */
    public function destroy(ConsultingChat $consultingChat): JsonResponse
    {
        /**
         * remove a specific consultingChat
         *
         * @delete(/api/v1/chat/consulting-chat/{consultingChat})
         * @middlewares(api, auth:sanctum, mobile.confirm)
         */
        $creator_id = $consultingChat->chat->create_user_id;
        if ($this->userRole != 'admin' && $this->user->id != $creator_id) {
            throw new AuthorizationException();
        }
        try {
                DB::beginTransaction();
                $consultingChat->deleteOrFail();
                $consultingChat->chat->deleteOrFail();
                DB::commit();
                return $this->successDeletedResponse();
            } catch (Throwable $exception) {
                DB::rollBack();
                throw $exception;
            }

    }

    /**
     * consulting moves
     *
     * move a consultingChat to another doctor
     *
     * @param Request $request
     * @param ConsultingChat $consultingChat
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @throws ValidationException
     * @group ConsultingChat
     * @bodyParam after_specified_time integer users preference after the doctor didn't answer Example: 1
     *
     * @responseFile status=201 scenario="When successfully stored" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-201.json
     * @responseFile status=422 scenario="When data is invalid" Modules/Chat/ChatTypes/ConsultingChat/Storage/consultingChat/store-422-invalid-data.json
     */
    public function ConsultingResponderHistory(Request $request, ConsultingChat $consultingChat): JsonResponse
    {
        /**
         * supportChat(consulting move) users preference after doctor didn't answer
         *
         * @post(/api/v1/chat/consulting-chat/consulting-responder-history/{consultingChat})
         * @middlewares(api, auth:sanctum, mobile.confirm, paymentDone)
         */
        $validData = $this->validate($request, [
            'after_specified_time' => ['required', 'integer', Rule::in([
                ConsultingChat::WAITE_FOR_DOCTOR,
                ConsultingChat::SEND_TO_ANOTHER_DOCTOR,
                ConsultingChat::REFUND_MONEY_TO_WALLET,
            ])],
        ]);

        $creator_id = $consultingChat->chat->create_user_id;

        if ($this->userRole != 'admin' && $this->user->id != $creator_id) {
            throw new AuthorizationException();
        }

        try {
            DB::beginTransaction();
            $consultingChat->update($validData);
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $this->show($consultingChat);
    }
}
