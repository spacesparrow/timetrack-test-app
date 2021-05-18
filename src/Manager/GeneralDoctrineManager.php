<?php

declare(strict_types=1);

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Exception;

class GeneralDoctrineManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param object $entity the instance to make managed and persistent
     *
     * @return object persistent entity
     *
     * @throws Exception
     */
    public function save(object $entity): object
    {
        /* start SQL transaction */
        $this->entityManager->getConnection()->beginTransaction();

        try {
            /* make entity managed and persistent, insert into database and commit transaction */
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            /* rollback changes if exception was thrown */
            $this->$this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $entity;
    }
}