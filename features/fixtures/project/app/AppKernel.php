<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FSi\Bundle\DataSourceBundle\DataSourceBundle(),
            new FSi\Bundle\DataGridBundle\DataGridBundle(),
            new FSi\Bundle\AdminBundle\FSiAdminBundle(),
            new FSi\Bundle\DoctrineExtensionsBundle\FSiDoctrineExtensionsBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new FSi\Bundle\ResourceRepositoryBundle\FSiResourceRepositoryBundle(),
            new FSi\Bundle\AdminTranslatableBundle\FSiAdminTranslatableBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),

            new FSi\FixturesBundle\FSiFixturesBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(sprintf('%s/config/config.yml', __DIR__));
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/FSiAdminTranslatableBundle/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/FSiAdminTranslatableBundle/logs';
    }
}
