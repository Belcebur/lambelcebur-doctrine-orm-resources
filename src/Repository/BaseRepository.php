<?php

namespace LamBelcebur\DoctrineORMResources\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Repository\RepositoryFactory;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;
use Laminas\Router\SimpleRouteStack;

class BaseRepository implements RepositoryFactory
{

    /**
     * @var TranslatorAwareTreeRouteStack|RouteStackInterface
     */
    protected $router;

    /**
     * @var null|RouteMatch
     */
    protected $routeMatch;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * @param Request $request
     * @param TranslatorAwareTreeRouteStack|TreeRouteStack|SimpleRouteStack|RouteStackInterface $router
     * @param RouteMatch $routeMatch
     */
    public function __construct(?Request $request, ?RouteStackInterface $router, ?RouteMatch $routeMatch)
    {
        $this->request = $request;
        $this->router = $router;
        $this->routeMatch = $routeMatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /**
     * @param EntityManagerInterface $entityManager The EntityManager instance.
     * @param string $entityName The name of the entity.
     *
     * @return ObjectRepository
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        /* @var $metadata ClassMetadata */
        $metadata = $entityManager->getClassMetadata($entityName);

        if ($metadata->customRepositoryClassName) {
            $repositoryClassName = $metadata->customRepositoryClassName;
            return new $repositoryClassName($entityManager, $metadata, $this->request, $this->router);
        }

        $repositoryClassName = $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        return new $repositoryClassName($entityManager, $metadata);
    }


}
