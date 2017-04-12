<?php

/**
 * This file is part of the corahn_rin package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Bundle\PermissionsBundle\Security\Provider;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class PermissionsFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('instanceof', function($object, $class){
                return sprintf('is_object(%1$s) ? is_a(%1$s, %2$s, true) : false', $object, $class);
            }, function(array $variables, $object, $class){
                return is_object($object) ? is_a($object, $class, true) : false;
            })
        ];
    }
}
