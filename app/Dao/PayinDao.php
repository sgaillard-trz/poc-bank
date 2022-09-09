<?php

namespace App\Dao;

use App\Dto\PayinDto;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PayinDao extends AbstractDao
{
    const TABLE_NAME = "payin";
    const ENTITY = PayinDto::class;

    /**
     * @throws ExceptionInterface
     */
    public function getByBeneficiary(string $beneficiaryIban)
    {
        $result = $this->service->query([
            "TableName" => self::TABLE_NAME,
            "IndexName" => "beneficiaryIbanIndex",
            "KeyConditionExpression" => "#iban = :v_iban",
            "ExpressionAttributeNames" => [
                "#iban" => "beneficiaryIban",
            ],
            "ExpressionAttributeValues" => [
                ":v_iban" => ["S" => $beneficiaryIban],
            ],
            "Select" => "ALL_ATTRIBUTES",
        ]);
        if (!isset($result["Items"])) {
            throw new NotFoundHttpException();
        }
        return $this->populate($result, PayinDto::class);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(PayinDto $payin)
    {
        $payin->setId(Uuid::uuid4()->toString());
        $payin->setCreatedAt(
            (new \DateTime())->format(\DateTimeInterface::RFC3339)
        );
        $ddbData = $this->toDdb($payin);
        $this->service->putItem([
            "TableName" => self::TABLE_NAME,
            "Item" => $ddbData,
        ]);
    }
}
