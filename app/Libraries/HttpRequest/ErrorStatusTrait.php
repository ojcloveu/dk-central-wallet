<?php

namespace App\Libraries\HttpRequest;

use App\Enum\SessionEnum;
use App\Enum\SessionErrorEnum;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Session;

trait ErrorStatusTrait
{
    protected function errorStatus422($http, $skip = false)
    {
        if ($skip) {
            return ;
        }

        $errorBag = session('errors', new MessageBag());

        if (!($errorBag instanceof MessageBag)) {
            $errorBag = new MessageBag($errorBag);
        }
        // Get new errors from the response
        $errors = $http->json('errors', []);

        // Add new errors to the MessageBag
        foreach ($errors as $field => $message) {
            $errorBag->add($field, is_array($message) ? $message[0] ?? 'Error' : $message);
        }

        // Flash the updated MessageBag to the session
        $errorBag->any() && Session::flash('errors', $errorBag);
    }

    protected function errorStatus400($http, $skip = false)
    {
        if ($skip) {
            return ;
        }

        $defaultMessage = 'Bad Request';
        $message = $http->json('message', $defaultMessage);

        Session::flash(
            SessionErrorEnum::HTTP_ERROR_MESSAGE->value,
            is_array($message)
                ? $message[0] ?? $defaultMessage
                : $message
        );
    }

    protected function errorStatus401($skip = false)
    {
        if ($skip) {
            return ;
        }

        Session::flash(SessionErrorEnum::HTTP_ERROR_MESSAGE->value, 'Forbidden');

        SessionEnum::logout();
    }

    protected function errorStatus403($skip = false)
    {
        if ($skip) {
            return ;
        }

        Session::flash(SessionErrorEnum::HTTP_ERROR_MESSAGE->value, 'Unauthorized');
    }
}
