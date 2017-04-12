Permissions bundle
==================

The goal of this bundle is to add simple ExpressionLanguage based permissions to Symfony,
to rely on something with more logic than Roles and less heavy than creating Voters.

## Install

* Require it with composer

  ```bash
  $ composer require orbitale/permissions-bundle
  ```

* Add the bundle to your kernel

  ```php
  <?php

  class AppKernel extends Kernel
  {
      public function registerBundles()
      {
          $bundles = [
              // ...
              new Orbitale\Bundle\PermissionsBundle\PermissionsBundle(),
          ];

          return $bundles;
      }
  }
  ```

* Setup your desired permissions:

  ```yaml
  # app/config/security.yml
  permissions:
      rules:
          ADMIN_EDIT: 'user and user.getStatus() === constant("AppBundle\\Entity\\User::STATUS_ADMIN")'
          SUBSCRIBE: 'user and user.isMemberOfTheTeam()'
          CHUCK_NORRIS: 'user and user.getUsername() === "Chuck Norris"'
  ```

* Use them in your controllers

  ```php
  <?php

  namespace AppBundle\Controller;

  use Symfony\Bundle\FrameworkBundle\Controller\Controller;

  class DefaultController extends Controller
  {
      public function badassAction()
      {
          $this->denyAccessUnlessGranted('CHUCK_NORRIS');

          // ...
      }
  }
  ```

## Configuration reference

```yaml
permissions:
    defaults:
        # Variables to add to ExpressionLanguage, for easier access if you need
        expression_variables: []

        # Will be added to all not already set "supports" attributes
        supports:             null
    rules:
        # Full prototype
        # Key names *must* be uppercase
        PERMISSION_KEY_NAME:
            supports: null
            on_vote: null   # Required

        # Allow expression with a single string, if you don't care of "supports":
        PERMISSION_KEY_NAME: 'on_vote expression'
```

## Real life example

```yaml
permissions:
    defaults:
        expression_variables:
            user_class: AppBundle\Entity\User
            post_class: AppBundle\Entity\Post
        supports: 'instanceof(user, user_class)'
    rules:
        ADMIN: 'user.isAdmin()'
        EDIT_POST:
            supports: 'instanceof(user, user_class) and instanceof(subject, post_class)'
            on_vote: 'user.isAdmin() and post.getAuthor().getId() === user.getId()'
```
