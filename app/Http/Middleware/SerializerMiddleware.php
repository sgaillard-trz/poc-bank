<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Laravel\Lumen\Routing\Router;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerMiddleware
{
    use ProvidesConvenienceMethods;

    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws ExceptionInterface|ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $method = $request->getMethod();

        list($found, $route, $params) = $request->route() ?: [false, [], []];

        if ($route == null) {
            return $next($request);
        }

        if (isset($route["validator"])) {
            $validator = "\\App\\Http\\Requests\\" . $route["validator"];
            $datas = $this->validate(
                $request,
                $validator::getInputValidators($request)
            );
            $request->replace($datas);
        } else {
            $datas = $request->input();
        }

        if (isset($route["entity"])) {
            $entity = "\\App\\Dto\\" . $route["entity"];
            $obj = null;
            if ($method == "POST") {
                $obj = $this->denormalize($datas, $entity);
            }
            if ($obj != null) {
                app()->singleton(get_class($obj), function () use ($obj) {
                    return $obj;
                });
            }
        }
        return $next($request);
    }

    /**
     * @throws ExceptionInterface
     */
    private function denormalize($arr, $class)
    {
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer([$normalizer]);
        return $serializer->denormalize($arr, $class);
    }
}
