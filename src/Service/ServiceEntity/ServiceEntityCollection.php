<?php
namespace TurboLabIt\TLIBaseBundle\Service\ServiceEntity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use TurboLabIt\TLIBaseBundle\Traits\Foreachable;


abstract class ServiceEntityCollection implements \Iterator, \Countable, \ArrayAccess
{
    use Foreachable;

    protected string $entityClassName;
    protected EntityManager $em;
    protected ServiceEntityRepository $repository;


    public function __construct(EntityManagerInterface $em, string $entityClassName)
    {
        $this->em           = $em;
        $this->repository   = $this->em->getRepository($entityClassName);
    }


    public function loadByIds(array $arrIds)
    {
        $arrEntities = $this->repository->getByIds($arrIds);
        return $this->loadFromEntities($arrEntities, true);
    }


    public function loadAll()
    {
        $arrEntities = $this->repository->loadWholeTable()->getWholeTable();
        return $this->loadFromEntities($arrEntities, true);
    }


    public function loadFromEntities(\Traversable $entities, bool $useCurrentKeys = false)
    {
        $this->arrData = [];
        foreach($entities as $key => $entity) {

            $idx = $useCurrentKeys ? $key : $entity->getId();
            $idx = (string)$idx;

            $this->arrData[$idx] =
                $this->createService()
                    ->setEntity($entity);
        }

        return $this;
    }


    public function toCsv($separator = ', ', $method = 'getTitle'): string
    {
        $arrData = [];
        foreach($this->arrData as $id => $oneService) {

            $arrData[] = $oneService->$method();
        }

        return implode($separator, $arrData);
    }


    public function contains($object): bool
    {
        if( empty($object) || empty($this->arrData) ) {

            return false;
        }

        foreach($this->arrData as $id => $oneService) {

            if(
                get_class($oneService) == get_class($object) &&
                $oneService->getId()   == $object->getId()
            ) {
                return true;
            }
        }

        return false;
    }


    public function getAsArray(): array
    {
        $arrDataAsArray = [];
        foreach($this->arrData as $id => $oneService) {

            $arrDataAsArray[$id] = $oneService->getAsArray();
        }

        return $arrDataAsArray;
    }


    abstract protected function createService();
}