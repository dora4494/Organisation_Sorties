<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;

    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findBySearchTerm($searchTerm, $siteId, $organisateurFilter, $inscritFilter, $nonInscritFilter, $sortiesPasseesFilter, $startDate, $endDate)
    {

        if ($this->security->getUser() !== null) {
            $userId = $this->security->getUser()->getId();
        }
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.Site', 'Site');
        if ($searchTerm) {
            $qb->andWhere('s.nom LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
        if ($siteId && $siteId !== 'null') {
            $qb->andWhere('s.Site = :siteId')
                ->setParameter('siteId', $siteId);
        }
        // Filtres des cases à cocher
        if ($organisateurFilter) {
            $qb->andWhere('s.idOrganisateur = :userId')
                ->setParameter('userId', $userId);
        }
        if ($inscritFilter) {
            $qb->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $userId);
        }
        if ($nonInscritFilter) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $userId);
        }
        if ($sortiesPasseesFilter) {
            $qb->andWhere('s.etats_no_etat = :etatPasse')
                ->setParameter('etatPasse', 5); // 5 = L'id de l'état "passé"
        }
        if ($startDate) {
            $qb->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', new \DateTime($startDate));
        }
        if ($endDate) {
            $qb->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', new \DateTime($endDate));
        }
        return $qb->getQuery()->getResult();
    }

    public function findLatestSix()
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }
}
