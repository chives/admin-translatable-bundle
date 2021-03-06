<?php

namespace FSi\Bundle\AdminTranslatableBundle\Repository;

use FSi\DoctrineExtensions\Translatable\TranslatableListener;
use FSi\Bundle\ResourceRepositoryBundle\Exception\ConfigurationException;
use FSi\Bundle\ResourceRepositoryBundle\Repository\MapBuilder as BaseMapBuilder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TranslatableMapBuilder extends BaseMapBuilder
{
    /**
     * @var \FSi\DoctrineExtensions\Translatable\TranslatableListener
     */
    protected $translatableListener;

    /**
     * @var array
     */
    protected $translatedKeys;

    /**
     * @var string
     */
    protected $mapPath;

    /**
     * @param string $mapPath
     * @param string[] $resourceTypes
     * @param \FSi\DoctrineExtensions\Translatable\TranslatableListener
     */
    public function __construct($mapPath, $resourceTypes = array(), TranslatableListener $translatableListener)
    {
        $this->mapPath = $mapPath;
        $this->translatableListener = $translatableListener;
        $this->translatedKeys = array();

        $this->resourceTypes = array();
        $this->resources = array();

        foreach ($resourceTypes as $type => $class) {
            $this->resourceTypes[$type] = $class;
        }

        $locale = $this->getCurrentLocale();
        $this->map[$locale] = $this->recursiveParseRawMap(Yaml::parse($mapPath));
    }

    /**
     * {@inheritdoc}
     */
    public function getMap()
    {
        $locale = $this->getCurrentLocale();

        if (isset($this->map[$locale])) {
            return $this->map[$locale];
        } else {
            return $this->map[$locale] = $this->recursiveParseRawMap(Yaml::parse($this->mapPath));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResource($key)
    {
        return $this->getResourceFromMap($key);
    }

    /**
     * {@inheritdoc}
     */
    public function hasResource($key)
    {
        $resource = $this->getResourceFromMap($key);
        return !empty($resource);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource($configuration, $path)
    {
        $locale = $this->getCurrentLocale();

        if ($this->isTranslatable($configuration)) {
            $path = sprintf("%s.%s", $path, $locale);
        }

        return parent::createResource($configuration, $path);
    }

    /**
     * @param $configuration
     * @param $path
     * @throws \FSi\Bundle\ResourceRepositoryBundle\Exception\ConfigurationException
     */
    protected function validateConfiguration($configuration, $path)
    {
        if (strlen($path) > 255) {
            throw new ConfigurationException(
                sprintf('"%s..." key is too long. Maximum key length is 255 characters', substr($path, 0, 32))
            );
        }

        if (!array_key_exists('type', $configuration)) {
            throw new ConfigurationException(
                sprintf('Missing "type" declaration in "%s" element configuration', $path)
            );
        }
    }

    /**
     * @param $configuration
     * @throws \FSi\Bundle\ResourceRepositoryBundle\Exception\ConfigurationException
     */
    protected function validateResourceConfiguration($configuration)
    {
        $validKeys = array(
            'form_options',
            'constraints',
            'translatable'
        );

        foreach ($configuration as $key => $options) {
            if ($key === 'type') {
                continue;
            }

            if ($key === 'translatable' && !is_bool($options)) {
                throw new ConfigurationException('Invalid value of "translatable" option. This option accepts only boolean value.');
            }

            if (!in_array($key, $validKeys)) {
                throw new ConfigurationException(
                    sprintf('"%s" is not a valid resource type option. Try one from: %s', $key, implode(', ', $validKeys))
                );
            }
        }
    }

    /**
     * @param array $configuration
     * @return boolean
     */
    private function isTranslatable(array $configuration)
    {
        return (isset($configuration['translatable']) && $configuration['translatable'] === true);
    }

    /**
     * @return string
     */
    private function getCurrentLocale()
    {
        return $this->translatableListener->getLocale() ?: $this->translatableListener->getDefaultLocale();
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getResourceFromMap($key)
    {
        $map = $this->getMap();

        $parts = explode('.', $key);
        $propertyPath = '';

        foreach ($parts as $part) {
            $propertyPath .= sprintf("[%s]", $part);
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($map, $propertyPath);
    }
}
