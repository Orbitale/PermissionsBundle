<?php

/**
 * This file is part of the permissionRules package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Bundle\PermissionsBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Role\RoleInterface;

class PermissionsVoter extends Voter
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var array[]
     */
    private $permissionRules;

    /**
     * @var array
     */
    private $permissionDefaults;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(ExpressionLanguage $expressionLanguage, TokenStorageInterface $tokenStorage, array $permissionRules, array $permissionDefaults)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->permissionRules = $permissionRules;
        $this->permissionDefaults = $permissionDefaults;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        $values = [
            'subject' => $subject,
            'token' => $token = $this->tokenStorage->getToken(),
            'user' => $token->getUser() ?: null,
        ];

        $permissionRule = null;

        foreach ($this->permissionRules as $id => $permissionRule) {
            if ($id === $attribute) {
                break;
            }
        }

        if (!$permissionRule) {
            return false;
        }

        if ($permissionRule['supports']) {

            foreach ($this->permissionDefaults['expression_variables'] as $variable => $value) {
                $values[$variable] = $value;
            }

            // Evaluate "supports" expression if provided
            return true === $this->expressionLanguage->evaluate($permissionRule['supports'], $values);
        }

        // Else, "empty" supports value will always be supported for configured keys
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $permission = null;

        $values = [
            'subject' => $subject,
            'token' => $token,
            'user' => $token->getUser(),
            'roles' => array_map(function(RoleInterface $role) { return $role->getRole(); }, $token->getRoles()),
            'access_granted' => self::ACCESS_GRANTED,
            'access_denied' => self::ACCESS_DENIED,
            'access_abstain' => self::ACCESS_ABSTAIN,
        ];

        foreach ($this->permissionDefaults['expression_variables'] as $variable => $value) {
            $values[$variable] = $value;
        }

        $permission = $this->permissionRules[$attribute];

        $result = $this->expressionLanguage->evaluate($permission['on_vote'], $values);

        // "true"-like results will be considered as granting access
        if ($result) {
            return self::ACCESS_GRANTED;
        }

        // Specific false result will be a denial
        if (false === $result) {
            return self::ACCESS_DENIED;
        }

        // Strings like "0" or empty non-false results will be considered as abstain
        return self::ACCESS_ABSTAIN;
    }
}
