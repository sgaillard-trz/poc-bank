<?php

namespace App\Http\Controllers;

use App\Dao\PayinDao;
use App\Dao\WalletDao;
use App\Dto\PayinDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PayinController extends Controller
{
    protected PayinDao $payins;

    public function __construct(PayinDao $payins)
    {
        $this->payins = $payins;
    }

    /**
     * @throws ExceptionInterface
     */
    public function getByBeneficiary(string $beneficiaryIban)
    {
        return $this->payins->getByBeneficiary($beneficiaryIban);
    }

    /**
     * @throws ExceptionInterface
     * @throws ValidationException
     */
    public function create(
        PayinDto $payin,
        WalletDao $wallets,
        Request $request
    ): JsonResponse {
        $iban = $payin->getBeneficiaryIban();
        $wallet = $wallets->getByIban($iban);

        if ($wallet === null) {
            $validator = new Validator(app("translator"), [], []);
            $validator->addFailure("beneficiaryIban", "IbanNotExists");
            $this->throwValidationException($request, $validator);
        }

        $this->payins->create($payin);
        $wallets->incrementValue(
            $wallet->getId(),
            "balance",
            $payin->getAmount()
        );

        return response()->json(null, 201, [], JSON_FORCE_OBJECT);
    }
}
