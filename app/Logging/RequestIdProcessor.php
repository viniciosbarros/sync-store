<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RequestIdProcessor implements ProcessorInterface {

    public function __invoke(LogRecord $record): LogRecord
    {
        if (app()->has('request') && $request = request()) {
            $requestId = $request->attributes->get('request_id');
            if ($requestId) {
                $record->extra['request_id'] = $requestId;
            }
        }

        return $record;
    }
}
