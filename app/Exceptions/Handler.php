<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Str;
use App\Constant\ApiV2Constant;
use App\Constant\BackendApiConstant;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiV1Exception::class,
        ApiV2Exception::class,
        ServiceException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->wantsJson()) {
            // 后台的异常错误
            if (Str::contains($request->getUri(), '/backend/api/v1')) {
                $code = BackendApiConstant::ERROR_CODE;
                $exception instanceof AuthenticationException && $code = BackendApiConstant::NO_AUTH_CODE;
                return response()->json([
                    'status' => $code,
                    'message' => $exception->getMessage(),
                ]);
            }
            if (!($exception instanceof ApiV2Exception)) {
                // apiV2异常错误
                if (Str::contains($request->getUri(), '/api/v2')) {
                    $code = ApiV2Constant::ERROR_CODE;
                    $exception instanceof AuthenticationException && $code = ApiV2Constant::ERROR_NO_AUTH_CODE;
                    return response()->json([
                        'code' => $code,
                        'message' => __('error'),
                    ]);
                }
            }
        }

        return parent::render($request, $exception);
    }
}
