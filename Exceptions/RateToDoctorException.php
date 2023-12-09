<?php

namespace Modules\Chat\Exceptions;

use App\Exceptions\ApiResponseException;
use Symfony\Component\HttpFoundation\Response;

class RateToDoctorException extends ApiResponseException
{
    public function __construct()
    {
        parent::__construct('user wants to rate doctor',"rate doctor", Response::HTTP_LOCKED);
    }
}
