<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Exceptions;

use App\Exceptions\ApiResponseException;
use Symfony\Component\HttpFoundation\Response;

class EditRulesException extends ApiResponseException
{
    public function __construct()
    {
        parent::__construct('un editable consulting chat',"you can't edit this consultingChat now", Response::HTTP_LOCKED);
    }
}
