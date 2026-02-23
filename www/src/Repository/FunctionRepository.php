<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class FunctionRepository
{
    private Connection $db;
    private string $table;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->table = 'pawnfunctions';
    }

    public function getAll(): array
    {
        $results = $this->db->createQueryBuilder()
            ->select('Function', 'Type', 'Comment', 'IncludeName')
            ->from($this->table)
            ->orderBy('Type', 'ASC')
            ->addOrderBy('Function', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row['IncludeName']][] = [
                'Function' => $row['Function'],
                'Comment' => $row['Comment'],
                'Type' => $row['Type'],
            ];
        }
        return $grouped;
    }

    public function getByFile(string $includeName): array
    {
        return $this->db->createQueryBuilder()
            ->select('Function', 'Comment')
            ->from($this->table)
            ->where('IncludeName = :includeName')
            ->setParameter('includeName', $includeName)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function getByNameAndFile(string $functionName, string $includeName): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('Function', 'FullFunction', 'Type', 'Comment', 'Tags', 'IncludeName')
            ->from($this->table)
            ->where('Function = :functionName')
            ->andWhere('IncludeName = :includeName')
            ->setParameter('functionName', $functionName)
            ->setParameter('includeName', $includeName)
            ->executeQuery()
            ->fetchAssociative();

        return $result ?: null;
    }

    public function getForSitemap(): array
    {
        $results = $this->db->createQueryBuilder()
            ->select('Function', 'IncludeName')
            ->from($this->table)
            ->orderBy('IncludeName', 'ASC')
            ->addOrderBy('Function', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row['IncludeName']][] = $row;
        }
        return $grouped;
    }

    public function search(string $query): array
    {
        $pattern = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query) . '%';

        $results = $this->db->createQueryBuilder()
            ->select('IncludeName as includeName', 'Function as value')
            ->from($this->table)
            ->where('Function LIKE :pattern')
            ->orWhere('Comment LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($results as &$result) {
            $result['type'] = 'function';
        }

        return $results;
    }
}
