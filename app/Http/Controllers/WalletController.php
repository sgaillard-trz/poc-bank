<?php

namespace App\Http\Controllers;

use App\Dao\WalletDao;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class WalletController extends Controller
{
    protected WalletDao $wallets;
    public function __construct(WalletDao $wallets)
    {
        $this->wallets = $wallets;
    }

    /**
     * @throws ExceptionInterface
     */
    public function get($id)
    {
        return $this->wallets->get($id);
    }

    public function edit(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $this->wallets->update($id, $request->input());
        return response()->json(null, 201, [], JSON_FORCE_OBJECT);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->wallets->create($request->input());
        return response()->json(null, 201, [], JSON_FORCE_OBJECT);
    }

    /**
     * @throws ExceptionInterface
     */
    public function list(): array
    {
        return $this->wallets->list();
    }
}
