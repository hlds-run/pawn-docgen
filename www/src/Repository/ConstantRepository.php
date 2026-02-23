<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class ConstantRepository
{
    private Connection $db;
    private string $table;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->table = 'pawnconstants';
    }

    public function getByFile(string $includeName): array
    {
        return $this->db->createQueryBuilder()
            ->select('Constant', 'Comment', 'Tags')
            ->from($this->table)
            ->where('IncludeName = :includeName')
            ->setParameter('includeName', $includeName)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function search(string $query): array
    {
        $pattern = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query) . '%';

        $results = $this->db->createQueryBuilder()
            ->select('IncludeName as includeName', 'Comment as value')
            ->from($this->table)
            ->where('Constant LIKE :pattern')
            ->orWhere('Comment LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($results as &$result) {
            $result['type'] = 'constant';
        }

        return $results;
    }
}
