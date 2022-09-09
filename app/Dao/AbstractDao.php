<?php

namespace App\Dao;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractDao
{
    const TABLE_NAME = self::TABLE_NAME;
    const ENTITY = self::ENTITY;

    protected DynamoDbClient $service;
    protected Serializer $serializer;

    public function __construct(DynamoDbClient $service, Serializer $serializer)
    {
        $this->service = $service;
        $this->serializer = $serializer;
    }

    public function incrementValue($id, $attribute, $incrValue)
    {
        $date = (new \DateTime())->format(\DateTimeInterface::RFC3339);

        $updateQuery = [
            "TableName" => static::TABLE_NAME,
            "Key" => [
                "id" => ["S" => $id],
            ],
            "ExpressionAttributeNames" => [
                "#${attribute}" => $attribute,
                "#updated" => "updated",
            ],
            "ExpressionAttributeValues" => [
                ":incr" => ["N" => $incrValue],
                ":updated" => ["S" => $date],
            ],
            "UpdateExpression" => "SET #${attribute} = #${attribute} + :incr, #updated = :updated",
        ];

        $this->service->updateItem($updateQuery);
    }

    /**
     * @throws ExceptionInterface
     */
    public function findBy($data, $indexName = null, $limit = null)
    {
        $parts = $this->getExpressionParts($data);
        $query = [
            "TableName" => static::TABLE_NAME,
            "KeyConditionExpression" => join(" AND ", $parts["conditions"]),
            "ExpressionAttributeNames" => $parts["names"],
            "ExpressionAttributeValues" => $parts["values"],
            "Select" => "ALL_ATTRIBUTES",
        ];
        if ($limit != null) {
            $query["Limit"] = $limit;
        }
        if ($indexName != null) {
            $query["IndexName"] = $indexName;
        }

        $result = $this->service->query($query);
        if (count($result["Items"]) == 0) {
            return [];
        }

        return $this->populate($result);
    }

    private function getExpressionParts($data): array
    {
        $values = [];
        $conditions = [];
        $names = [];
        $i = 0;
        foreach ($data as $field => $value) {
            $values[":val${i}"] = [is_numeric($value) ? "N" : "S" => $value];
            $conditions[] = "#${field} = :val${i}";
            $names["#${field}"] = $field;
            $i++;
        }

        return [
            "names" => $names,
            "values" => $values,
            "conditions" => $conditions,
        ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function findOneBy($data, $indexName = null)
    {
        $result = $this->findBy($data, $indexName, 1);

        if (count($result) === 0) {
            return null;
        } else {
            return $result[0];
        }
    }

    public function update($id, $data)
    {
        $data["updated"] = (new \DateTime())->format(
            \DateTimeInterface::RFC3339
        );
        $parts = $this->getExpressionParts($data);
        $updateQuery = [
            "TableName" => static::TABLE_NAME,
            "Key" => [
                "id" => ["S" => $id],
            ],
            "ExpressionAttributeNames" => $parts["names"],
            "ExpressionAttributeValues" => $parts["values"],
            "UpdateExpression" => "SET " . join(", ", $parts["conditions"]),
        ];

        $this->service->updateItem($updateQuery);
    }

    /**
     * @throws ExceptionInterface
     */
    public function toDdb($obj): array
    {
        $marshaler = new Marshaler();
        return $marshaler->marshalItem($this->normalize($obj));
    }

    /**
     * @throws ExceptionInterface
     */
    protected function populate(Result $result)
    {
        if ($result->hasKey("Items")) {
            return $this->populateList($result);
        }
        if ($result->hasKey("Item")) {
            return $this->populateItem($result);
        }
        return null;
    }

    /**
     * @throws ExceptionInterface
     */
    private function populateList(Result $result): array
    {
        $marshaler = new Marshaler();
        $data = [];
        foreach ($result["Items"] as $item) {
            $data[] = $this->denormalize($marshaler->unmarshalItem($item));
        }
        return $data;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function denormalize($arr)
    {
        return $this->serializer->denormalize($arr, static::ENTITY);
    }

    private function populateItem(Result $result)
    {
        $marshaler = new Marshaler();
        return $marshaler->unmarshalItem($result["Item"]);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function normalize($obj): array
    {
        return $this->serializer->normalize($obj);
    }
}
