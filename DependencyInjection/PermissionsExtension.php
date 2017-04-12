<?php

/**
 * This file is part of the AccessRules package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Bundle\PermissionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

class PermissionsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->addDefaultSupports($this->processConfiguration($configuration, $configs));

        foreach ($config['rules'] as $key => $rule) {
            if (!preg_match('~^[A-Z][A-Z0-9_]+$~', $key)) {
                throw new InvalidConfigurationException(sprintf(
                    'Permission keys must be uppercase strings with only alphanumerical characters and underscores,' .
                    "and cannot start with numbers.\n".
                    '"%s" given.',
                    $key
                ));
            }

            if (strpos($key, 'ROLE_') === 0) {
                throw new InvalidConfigurationException(sprintf(
                    'Please do not define permissions with "ROLE_" as prefix. For this, use the "role_hierarchy"' .
                    "security configuration.\n".
                    '"%s" given.',
                    $key
                ));
            }

            unset($config['rules'][$key]);

            // Automatically normalize permissions to upper case
            $config['rules'][strtoupper($key)] = $rule;
        }

        $this->validateExpressionVariables($config);

        $container->setParameter('permissions.rules', $config['rules']);
        $container->setParameter('permissions.defaults', $config['defaults']);
    }


    /**
     * @param array $config
     *
     * @return array
     */
    private function addDefaultSupports(array $config)
    {
        // Apply default "supports" for every permission
        if ($config['defaults']['supports']) {
            foreach ($config['rules'] as $permission => $rules) {
                if (!$rules['supports']) {
                    $config['rules'][$permission]['supports'] = $config['defaults']['supports'];
                }
            }
        }

        return $config;
    }

    private function validateExpressionVariables(array $config)
    {
        $builtin = [
            'subject',
            'token',
            'user',
            'roles',
            'access_granted',
            'access_denied',
            'access_abstain',
        ];

        foreach ($config['defaults']['expression_variables'] as $key => $value) {
            if (in_array($key, $builtin)) {
                throw new InvalidConfigurationException("Key \"$key\" cannot be set in expression variables because it is already built-in.");
            }
        }
    }
}
