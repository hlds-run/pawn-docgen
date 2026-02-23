<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class FileRepository
{
    private Connection $db;
    private string $table;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->table = 'pawnfiles';
    }

    public function getAll(): array
    {
        return $this->db->createQueryBuilder()
            ->select('ID', 'IncludeName')
            ->from($this->table)
            ->orderBy('IncludeName', 'ASC')
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    public function getByName(string $includeName): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('IncludeName = :includeName')
            ->setParameter('includeName', $includeName)
            ->executeQuery()
            ->fetchAssociative();

        return $result ?: null;
    }

    public function getContentByName(string $includeName): ?string
    {
        $result = $this->db->createQueryBuilder()
            ->select('Content')
            ->from($this->table)
            ->where('IncludeName = :includeName')
            ->setParameter('includeName', $includeName)
            ->executeQuery()
            ->fetchOne();

        return $result ?: null;
    }
}
