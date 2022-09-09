<?php

namespace App\Dao;

use App\Dto\PayoutDto;
use DateTime;
use DateTimeInterface;
use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PayoutDao extends AbstractDao
{
    const TABLE_NAME = "payout";
    const ENTITY = PayoutDto::class;

    public function getBy(
        string $beneficiaryIban = "",
        string $creditorIban = ""
    ) {
        $queryParams = [];
        if (empty($creditorIban)) {
            $queryParams = [
                "IndexName" => "payoutBeneficiaryIbanIndex",
                "ExpressionAttributeValues" => [
                    ":v_iban" => ["S" => $beneficiaryIban],
                ],
                "ExpressionAttributeNames" => [
                    "#iban" => "beneficiaryIban",
                ],
            ];
        } else {
            $queryParams = [
                "IndexName" => "payoutCreditorIbanIndex",
                "ExpressionAttributeValues" => [
                    ":v_iban" => ["S" => $creditorIban],
                ],
                "ExpressionAttributeNames" => [
                    "#iban" => "creditorIban",
                ],
            ];
        }
        $result = $this->service->query(
            array_merge(
                [
                    "TableName" => self::TABLE_NAME,
                    "KeyConditionExpression" => "#iban = :v_iban",
                    "Select" => "ALL_ATTRIBUTES",
                ],
                $queryParams
            )
        );
        if (!isset($result["Items"])) {
            throw new NotFoundHttpException();
        }
        return $this->populate($result);
    }

    public function list(): array
    {
        $result = $this->service->scan([
            "TableName" => self::TABLE_NAME,
            //            'ScanIndexForward' => false,
        ]);

        return $this->populate($result);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(PayoutDto $payout): void
    {
        $payout->setId(Uuid::uuid4()->toString());
        $payout->setCreatedAt(
            (new DateTime())->format(DateTimeInterface::RFC3339)
        );
        $ddbData = $this->toDdb($payout);
        $this->service->putItem([
            "TableName" => self::TABLE_NAME,
            "Item" => $ddbData,
        ]);
    }
}
