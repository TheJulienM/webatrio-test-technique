<?php

namespace App\Repository;

use App\Entity\People;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<People>
 *
 * @method People|null find($id, $lockMode = null, $lockVersion = null)
 * @method People|null findOneBy(array $criteria, array $orderBy = null)
 * @method People[]    findAll()
 * @method People[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, People::class);
    }

    /**
     * @return People[] Returns an array of People objects
     */
    public function findAllOrderByLastname(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.lastname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return People[] Returns an array of People objects
     */
    public function findAllByCompany(?string $companyName): array
    {
        return $this->createQueryBuilder('p')
            ->select("p.lastname, p.firstname, jobs.position, jobs.startDate, jobs.endDate")
            ->join("p.jobs", "jobs")
            ->where("jobs.companyName = :companyName")
            ->setParameter('companyName', $companyName)
            ->orderBy('p.lastname', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return People[] Returns an array of People objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?People
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
