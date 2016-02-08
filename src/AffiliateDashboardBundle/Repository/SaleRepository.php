<?php

namespace AffiliateDashboardBundle\Repository;

use AffiliateDashboardBundle\Entity\Tag;

/**
 * SaleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SaleRepository extends \Doctrine\ORM\EntityRepository
{

    // price * qty = revenue
    // revenue / rate = earnings

    public function getSalesPerMonth()
    {
        $subselect = $this->createQueryBuilder('s')
            ->select('YEAR(s.date) AS agYear, MONTH(s.date) AS agMonth, SUM(s.earnings) AS agEuro')
            ->groupBy('agYear')
            ->addGroupBy('agYear, agMonth');

        $arr = array();
        foreach ($subselect->getQuery()->getArrayResult() as $item) {
            $key = sprintf('%d-%02d', $item['agYear'], $item['agMonth']);
            $arr[$key] = array(
                (float)$item['agEuro']
            );
        }
        return $arr;
    }

    public function getSalesPerDay(Tag $tag)
    {
        $subselect = $this->createQueryBuilder('s')
            ->select('YEAR(s.date) AS agYear, MONTH(s.date) AS agMonth, DAY(s.date) AS agDay, SUM(s.earnings) AS agEuro')
            ->where('s.affiliateTag = :tag')
            ->groupBy('agYear')
            ->addGroupBy('agYear, agMonth, agDay')
            ->setParameter('tag', $tag);

        $arr = array();
        foreach ($subselect->getQuery()->getArrayResult() as $item) {
            $key = sprintf('%d-%02d-%02d', $item['agYear'], $item['agMonth'], $item['agDay']);
            $arr[$key] = array(
                (float)$item['agEuro']
            );
        }
        return $arr;
    }

}
