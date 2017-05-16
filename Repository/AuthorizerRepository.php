<?php

namespace Kariae\AuthorizerBundle\Repository;

/**
 * AuthorizerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AuthorizerRepository extends \Doctrine\ORM\EntityRepository
{
    public function isAuthorized(array $roles, string $controller, string $action)
    {
        $qb = $this->createQueryBuilder('a');

        $auth = $qb->select('a.vote')
                    ->where($qb->expr()->in('a.role', array_values($roles)))
                    ->andWhere('a.controller = :controller')
                    ->andWhere('a.action = :action')
                    ->setParameter('controller', $controller)
                    ->setParameter('action', $action)
                    ->getQuery()
                    ->getOneOrNullResult();

        return ($auth) ? $auth['vote'] : false;
    }

    public function getAllAuthorizations()
    {
        return $this->createQueryBuilder('a')
                    ->getQuery()
                    ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}