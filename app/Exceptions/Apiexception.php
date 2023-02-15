<?php

namespace App\Exceptions;

use Exception;

class Apiexception extends Exception
{
    public function render($request, Exception $exception)
{
    if ($exception instanceof ModelNotFoundException) {
        return response()->json([
            'error' => 'Entry for '.str_replace('App', '', $exception->getModel()).' not found'
        ], 404);
    } else if ($exception instanceof GithubAPIException) {
        return response()->json(['error' => $exception->getMessage()], 500);
    } else if ($exception instanceof RequestException) {
        return response()->json(['error' => 'External API call failed.'], 500);
    }

    return parent::render($request, $exception);
}
}
