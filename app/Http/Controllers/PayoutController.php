<?php

namespace App\Http\Controllers;

use App\Dao\PayoutDao;
use App\Dao\WalletDao;
use App\Dto\PayoutDto;
use App\Dto\WalletDto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Serializer;

class PayoutController extends Controller
{
    protected PayoutDao $payouts;
    protected Serializer $serializer;
    private Validator $validator;

    public function __construct(PayoutDao $payout, Serializer $serializer)
    {
        $this->payouts = $payout;
        $this->serializer = $serializer;
    }

    /**
     */
    public function getBy(Request $request)
    {
        $sanitized = $request->input();
        return $this->payouts->getBy(
            $sanitized["beneficiaryIban"] ?? "",
            $sanitized["creditorIban"] ?? ""
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws ValidationException
     */
    public function create(
        PayoutDto $payout,
        WalletDao $wallets
    ): \Illuminate\Http\JsonResponse {
        $benefWallet = $this->checkWalletByIban(
            $payout->getBeneficiaryIban(),
            "beneficiaryIban",
            $wallets
        );
        $creditWallet = $this->checkWalletByIban(
            $payout->getCreditorIban(),
            "creditorIban",
            $wallets
        );

        $this->checkBalance($creditWallet, $payout->getAmount());
        if (count($this->getValidator()->failed()) > 0) {
            $this->throwValidationException(request(), $this->getValidator());
        }

        $this->payouts->create($payout);
        $wallets->incrementValue(
            $benefWallet->getId(),
            "balance",
            $payout->getAmount()
        );
        $wallets->incrementValue(
            $creditWallet->getId(),
            "balance",
            -1 * $payout->getAmount()
        );
        return response()->json(null, 201, [], JSON_FORCE_OBJECT);
    }

    /**
     * @throws ExceptionInterface
     */
    private function checkWalletByIban(
        $iban,
        $attribute,
        WalletDao $wallets
    ): ?WalletDto {
        $errorType = "IbanExists";
        $wallet = $wallets->getByIban($iban);
        if ($wallet == null) {
            $validator = $this->getValidator();
            $validator->addFailure($attribute, $errorType);
        }
        return $wallet;
    }

    private function checkBalance(WalletDto $wallet, $payoutAmount)
    {
        if ($wallet->getBalance() < $payoutAmount) {
            $validator = $this->getValidator();
            $validator->addFailure("amount", "CreditorBalanceTooLow");
        }
    }

    private function getValidator(): Validator
    {
        if (!isset($this->validator)) {
            $this->validator = new Validator(app("translator"), [], []);
        }
        return $this->validator;
    }
}
