<?php

namespace App\Exceptions;

use App\Helper\Common;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Class Handler
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @param Throwable $exception
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * @param HttpExceptionInterface $e
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $status = $e->getStatusCode();
        $area = Common::getArea();
        view()->replaceNamespace('errors', [
            app_path("Views/{$area}/errors"),
            __DIR__ . '/Views',
        ]);
        if (config('app.env') == 'production' && view()->exists("errors::{$status}")) {
            return response()->view("errors::{$status}", ['exception' => $e, 'area' => $area, 'title' => config('app.title')], $status, $e->getHeaders());
        }
        return $this->convertExceptionToResponse($e);
    }
}
