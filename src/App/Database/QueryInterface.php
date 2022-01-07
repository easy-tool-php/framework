<?php

namespace EasyTool\Framework\App\Database;

interface QueryInterface
{
    /**
     * Collect all matched records
     */
    public function fetchAll(): array;

    /**
     * Collect all matched records and assign them as an [key => value] array
     */
    public function fetchPairs(string $keyColumn, string $valueColumn): array;

    /**
     * Collect a column of all matched records
     */
    public function fetchColumn(string $column): array;

    /**
     * Collect the first matched record
     */
    public function fetchRow(): array;

    /**
     * Collect one column of the first matched record
     */
    public function fetchOne(): string;

    /**
     * Collect IDs of all matched records
     */
    public function getAllIds(): array;

    /**
     * Retrieve then connection object
     */
    public function getConnection(): object;

    /**
     * Get collection size
     */
    public function getSize(): int;
}
