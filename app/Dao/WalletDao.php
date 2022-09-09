<?php

namespace App\Dao;

use App\Dto\WalletDto;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class WalletDao extends AbstractDao
{
    const TABLE_NAME = "wallet";
    const ENTITY = WalletDto::class;

    /**
     * @throws ExceptionInterface
     */
    public function get(string $id)
    {
        $result = $this->service->getItem([
            "ConsistentRead" => true,
            "TableName" => self::TABLE_NAME,
            "Key" => [
                "id" => ["S" => $id],
            ],
        ]);
        if (!isset($result["Item"])) {
            throw new NotFoundHttpException();
        }
        return $this->populate($result);
    }

    /**
     * @throws ExceptionInterface
     */
    public function list(): array
    {
        $result = $this->service->scan([
            "TableName" => self::TABLE_NAME,
        ]);

        return $this->normalize($this->populate($result));
    }

    /**
     * @throws ExceptionInterface
     */
    public function create($data)
    {
        $data["id"] = Uuid::uuid4()->toString();
        $data["createdAt"] = (new \DateTime())->format(
            \DateTimeInterface::RFC3339
        );
        $data["balance"] = 0;
        $data["currency"] = $data["currency"] ?? "EUR";
        $data["iban"] = \Faker\Factory::create()->iban();
        $ddbData = $this->toDdb($data);
        $this->service->putItem([
            "TableName" => self::TABLE_NAME,
            "Item" => $ddbData,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function getByIban(string $iban): ?WalletDto
    {
        if ($iban == null || $iban == "") {
            return null;
        }
        $result = $this->service->query([
            "TableName" => self::TABLE_NAME,
            "IndexName" => "iban",
            "KeyConditionExpression" => "#iban = :v_iban",
            "ExpressionAttributeNames" => [
                "#iban" => "iban",
            ],
            "ExpressionAttributeValues" => [
                ":v_iban" => ["S" => $iban],
            ],
            "Select" => "ALL_ATTRIBUTES",
        ]);
        if (count($result["Items"]) === 0) {
            return null;
        }

        return $this->populate($result)[0];
    }
}
