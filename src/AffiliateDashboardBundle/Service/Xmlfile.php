<?php

namespace AffiliateDashboardBundle\Service;

use AffiliateDashboardBundle\Entity\Sale;
use AffiliateDashboardBundle\Entity\Tag;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DomCrawler\Crawler;

class Xmlfile
{
    private $data;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param File $file
     * @return Sale[]
     */
    public function crawl(File $file)
    {
        $sales = [];

        $crawler = new Crawler(file_get_contents($file->getPathname()));

        /** @var $saleItem \DOMElement */
        foreach ($crawler->filterXPath('//Data/Items/Item') as $saleItem) {
            $saleObj = new Sale();

            $tag = $saleItem->getAttribute('Tag');

            $tagEntity = $this->getEm()->getRepository('AffiliateDashboardBundle:Tag')->findbyName($tag);
            if (!$tagEntity) {
                $tagEntity = new Tag();
                $tagEntity->setName($tag);
                $this->getEm()->persist($tagEntity);
                $this->getEm()->flush();
            }

            $saleObj->setAsin($saleItem->getAttribute('ASIN'));
            $saleObj->setCategory($saleItem->getAttribute('Category'));
            $saleObj->setDate(new \DateTime(date('Y-m-d H:i:s', $saleItem->getAttribute('EDate'))));
            $saleObj->setEarnings($this->parseFloat($saleItem->getAttribute('Earnings')));
            $saleObj->setLinkType($saleItem->getAttribute('LinkType'));
            $saleObj->setPrice($this->parseFloat($saleItem->getAttribute('Price')));
            $saleObj->setQty((int)$saleItem->getAttribute('Qty'));
            $saleObj->setRate($this->parseFloat($saleItem->getAttribute('Rate')));
            $saleObj->setRevenue($this->parseFloat($saleItem->getAttribute('Revenue')));
            $saleObj->setAffiliateTag($tagEntity);
            $saleObj->setSeller($saleItem->getAttribute('Seller') ?: null);
            $saleObj->setTitle($saleItem->getAttribute('Title'));

            $sales[] = $saleObj;
        }

        return $sales;
    }

    private function parseFloat($value)
    {
        // TODO: Better way?
        return (float)str_replace(',', '.', str_replace('.', '', $value));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }
}