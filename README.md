# YarhonRouteGuardBundle 

Symfony route authorization checker

[![Build Status](https://travis-ci.org/yarhon/YarhonRouteGuardBundle.svg?branch=master)](https://travis-ci.org/yarhon/YarhonRouteGuardBundle)
[![Code Coverage](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/yarhon/route-guard-bundle/v/stable)](https://packagist.org/packages/yarhon/route-guard-bundle)
# About

YarhonRouteGuardBundle (RouteGuard) is a tool to:
* check if user is authorized to access a route
* retrieve authorization tests for a route
* conditionally display blocks in Twig templates depending on authorization tests, avoiding
authorization checks duplication both in controller and template.

RouteGuard supports authorization tests from the following providers:
* Symfony SecurityBundle (`access_control` rules)
* Sensio FrameworkExtraBundle (`@IsGranted` and `@Security` annotations).
* [Planned in the next release] Dynamic tests (arbitrary code in controller). [Details](#whats-planned).

And allows to add your own authorization test providers. Read [more](#adding-your-own-authorization-test-provider).

RouteGuard has a few limitations for rare use cases. Read [more](#limitations).

Let the code speak:

A) Template rendering

When you need to conditionally display some content (basically, links) in Twig templates, depending on authorization tests,
you would typically write code like this:
```twig
{% if is_granted('ROLE_USER') %}
    <a href="{{ path('blog', {'page': 1}) }}">Blog link</a>
{% else %}
    No access
{% endif %}
```

RouteGuard allows you to get rid of authorization checks in templates, using those defined for route or route controller
by supported test providers:  
```twig
{% route 'blog', {'page': 1} %}
    <a href="{{ _route.ref }}">Blog link</a>
{% else %}
    No access
{% endroute %}
```
In the example above, link will be rendered only if none of the authorization tests for the `blog` route denied access, 
contents of the `else` block rendered otherwise.

The `_route.ref` variable would contain the generated URL.

Read more in [Twig templates](#twig-templates) section.

Moreover, being well aware of "naming things" problem, RouteGuard allows to configure the name of the Twig tag ("route" by default),
and the name of the special inner variable ("_route" by default). Read more in [Configuration](#configuration) section.

B) Check if user is authorized to access a route

```php
namespace App\Service;

use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;

class SomeService
{
    private $authorizationChecker;
    
    public function __construct(RouteAuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
    
    public function check()
    {
        $routeContext = new RouteContext('blog', ['page' => 10], 'GET');
        
        return $this->authorizationChecker->isGranted($routeContext);
    }
}    
```

Read more in [Public services](#public-services) section.


# Requirements
PHP 5.6+, Symfony 3.3+.

It's highly recommended to have OPcache extension enabled.

# Installation
```console
$ composer require yarhon/route-guard-bundle
```

If you are not using Symfony Flex, you have to manually add
`new Yarhon\RouteGuardBundle\YarhonRouteGuardBundle()` 
to the list of registered bundles in the `app/AppKernel.php` file of your project.

# Configuration

If you need to change default configuration values, you can set them under the `yarhon_route_guard` key in the configuration
file (typically, `/app/config/config.yml` for Symfony < 4.0 or `/config/packages/yarhon_route_guard.yml` for Symfony >= 4.0. 
In the latter case you have to create this file first).

Configuration options:
* `data_collector`. Options for RouteGuard authorization data collector.
  * `ignore_controllers`. Array of controller names that would be ignored by data collector 
  (i.e., routes bound to these controllers would not require authorization). 
  Controller names should be specified in class::method (service::method) notation. You can specify:
    * full controller name, i.e. `App\Controller\DefaultController::index`
    * controller name prefix, i.e. `App\Controller\DefaultController` or `App\Controller\`
    
    Note: for "controller-as-a-service" controllers you have to specify service name, not class name.
  
    Default value: `[]`
    
    This option could be useful to speed up cache warmup (and reduce cache size) or to exclude some particular
    route(s) that trigger an exception.
    
  * `ignore_controllers_symfony`. Array of default Symfony controllers that would be ignored by data collector.

    Default value: 
    ```php 
    [
        'twig.controller.preview_error',
        'web_profiler.controller.profiler',
        'web_profiler.controller.router',
        'web_profiler.controller.exception',
    ]
    ```
  
  * `ignore_exceptions`. Boolean, if `true` - data collector would ignore routes that trigger an exception     
     while collecting authorization data.            
     Logger error message would be written in this case.       
     Note: if exception was triggered for a route by one of the test providers, route would be ignored completely, 
     not taking into account tests from other providers, if they exist.
  
    Default value: `false`
    
    This option could be useful when you first try RouteGuard and facing some of it's limitations / bugs.
* `twig`. Options for RouteGuard Twig extension.
  * `tag_name`. Name of the Twig tag. Default value: `'route'`.
  * `tag_variable_name`. Name of the tag inner variable (array), that would contain route info (i.e., generated URL). Default value: `'_route'`.
  * `discover_routing_functions`. Boolean, specifies whether to use "discover" mode in twig tag. Default value: `true`.
  
# Usage

## Twig templates

### Twig tag ("route") syntax

Twig tag arguments are split into two parts: first one is route context arguments, second one, after the `as` keyword, 
specifies required reference type in literal form. 

Route context arguments are:
* routeName (string, required)
* parameters (array, optional, default value: `[]`)
* method (string, optional, default value: `'GET'`).

Reference type is specified in the following forms:
* path [absolute|relative]. Equal to generating URL with `path()` function.
* url [absolute|relative]. Equal to generating URL with `url()` function.
If no reference type is specified, `"path absolute"` would be used. If only first part ("path" or "url") is specified,
`"absolute"` would be used as a second part.

Examples:
```twig
{% route 'blog', {'page': 1}, 'GET' as path absolute %}
{% route 'blog', {'page': 1}, 'GET' as url relative %}
{% route 'blog' as url %}
{% route 'blog' %}
```

For those, who want to try RouteGuard with minimal effort, it provides "discover" mode. In this mode, RouteGuard will search
for `path()` or `url()` function call inside "route" tag, and then use function arguments as tag arguments.
Following two examples will produce the same result:
```twig
{% route discover %}
    <a href="{{ url('blog', {'page': 1}, true) }}">Blog link</a>
{% endroute %}
```
```twig
{% route 'blog', {'page': 1}, 'GET' as url relative %}
    <a href="{{ _route.ref }}">Blog link</a>
{% endroute %}
```
The limitation of "discover" mode is that you can't specify method - it would always be considered as `'GET'`.

### Twig functions

By analogy with standard `path()` and `url()` functions, RouteGuard provides its' own functions:
```php
route_guard_path($name, array $parameters = [], $method = 'GET', $relative = false)
route_guard_url($name, array $parameters = [], $method = 'GET', $relative = false)
```

And one more function, that is used internally by the "route" tag:
```php
route_guard_route($name, array $parameters = [], $method = 'GET', array $generateAs = [])
```

## Public services

### RouteAuthorizationChecker

Allows to check if user is authorized to access a route.

Service id: `yarhon_route_guard.route_authorization_checker`

Example:
```php
namespace App\Service;

use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;

class SomeService
{
    private $authorizationChecker;
    
    public function __construct(RouteAuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
    
    public function check()
    {
        $routeContext = new RouteContext('blog', ['page' => 10], 'GET');
        
        return $this->authorizationChecker->isGranted($routeContext);
    }
}
```

### AuthorizedUrlGenerator

Allows to generate URLs outside of Twig context.

Service id: `yarhon_route_guard.authorized_url_generator`

Example:
```php
namespace App\Service;

use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SomeService
{
    private $urlGenerator;
    
    public function __construct(AuthorizedUrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    
    public function generateUrl()
    {
        return $this->urlGenerator->generate('blog', ['page' => 10], 'GET', UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}    
```
The [`AuthorizedUrlGeneratorInterface::generate`] method signature is similar to Symfony's [`Symfony\...\UrlGeneratorInterface::generate`], 
except it adds `$method` as a 3rd parameter, moving `$referenceType` to 4th place.
It would return the generated URL, if none of the authorization tests denied access, or boolean `false` otherwise.

### TestLoader

Allows to retrieve all authorization tests for a route.

Service id: `yarhon_route_guard.test_loader`

Example:
```php
namespace App\Service;

use Yarhon\RouteGuardBundle\Security\TestLoaderInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;

class SomeService
{
    private $testLoader;
    
    public function __construct(TestLoaderInterface $testLoader)
    {
        $this->testLoader = $testLoader;
    }
    
    public function getTests()
    {
        $routeContext = new RouteContext('blog', ['page' => 10], 'GET');
        
        return $this->testLoader->load($routeContext);
    }
}    
```

The [`TestLoaderInterface::load`] method returns an array of [`TestInterface`] instances.

# Limitations

## Request object limitations

RouteGuard doesn't modify current [`Request`] object 
when it passes it to the Symfony's authorization checker for the particular route.

That means methods of [`Request`] object that return url / host / method related parameters, being used inside
security voters ([`Symfony\...\VoterInterface`]) or expressions ([`Symfony\...\Expression`]), 
would return values irrelevant to the route being checked.

In this case results of RouteGuard authorization checks are "Undefined behaviour". 
These methods include:
* getPathInfo
* getHost
* getHttpHost
* getMethod
* isMethod*
* getRequestUri
* getUri

## Runtime variables limitations

### In short

If you are using authorization tests with runtime variables supposed to be resolved from Request attributes,
and these variables are not a part of route parameters (route variables + defaults), 
RouteGuard would not be able to resolve them and will throw an exception. 

### In details

Test providers may provide authorization test that require runtime variables (basically, controller arguments).  
To resolve these variables, RouteGuard introduces [`ArgumentResolver`].  
It mimics Symfony's  [`Symfony\...\ArgumentResolver`], but resolves controller arguments from the [`RouteContextInterface`] instance
using [`ControllerMetadata`] cache.

Like Symfony's [`Symfony\...\ArgumentResolver`], it uses an array of [`ArgumentValueResolverInterface`] instances to delegate resolving 
to the specific resolver. They all work just like their Symfony's prototypes except handling of Request attributes.

In Symfony, Request attributes are initially set from the route parameters (route variables + defaults).  
See [`Symfony\...\RouterListener::onKernelRequest`].  
But besides, in Symfony, Request attributes are used more widely as just route parameters - they are used as implicit 
"information exchange point" between different components.

In RouteGuard, Request attributes for particular route are created by [`RequestAttributesFactory`].
It returns resolved route parameters (route variables + defaults).  

In turn, RouteGard can't use any Request attributes, other than those that came from parameters of the route being checked.
Using other attributes of the current [`Request`] could be irrelevant to that route.

Also, unlike standard flow, [`RequestAttributesFactory`] doesn't add special `'_route'` attribute and removes `'_controller'` parameter
from route parameters - so they can't be used as runtime variables too.

## Sensio FrameworkExtraBundle limitations

FrameworkExtraBundle allows to use user-defined runtime variables in authorization tests
("subject" argument of `@IsGranted` annotation or variables in `@Security` annotation expression).

Thus, limitations described in [Runtime variables limitations](#runtime-variables-limitations)
are applicable to authorization tests from FrameworkExtraBundle.

### ParamConverter is not supported

Currently, RouteGuard doesn't support controller arguments conversion provided by `@ParamConverter` annotation of FrameworkExtraBundle - 
it would use unconverted argument value, that could lead to unexpected results. 

Note, that ParamConverter can be involved implicitly, if `auto_convert` option of FrameworkExtraBundle is set to `true` 
and controller argument is type hinted by one of the ParamConverter supported types
(by default, they are `DateTimeInterface` and Doctrine entity classes).

# Under the hood

## Collecting data

RouteGuard collects all authorization tests and required metadata (route metadata and controller metadata) 
in compile time, at cache warmup. Entry point: [`AuthorizationCacheWarmer`].

Authorization tests and metadata are stored in PSR-6 caches.

Authorization tests for particular route are collected by [`ProviderAggregate`],
which iterates through all registered test providers (instances of [`ProviderInterface`]).

[`ProviderInterface::getTests`] method returns test bag (instance of [`AbstractTestBagInterface`]),
that contains tests (instances of [`TestInterface`]).

Built-in test providers:
* [`SymfonyAccessControlProvider`]. Reads `access_control` rules of Symfony SecurityBundle. 
  Returns test bag with instances of [`SymfonyAccessControlTest`].
* [`SensioExtraProvider`]. Reads `@IsGranted` and `@Security` annotations of Sensio FrameworkExtraBundle.
  Returns test bag with instances of [`SensioExtraTest`].

## Route authorization

Route authorization is performed by [`RouteAuthorizationChecker`] service.
It loads tests for a route and calls [`DelegatingAuthorizationChecker`] that passes tests to supporting authorization checker, 
depending on test instance class.

Built-in authorization checkers:
* [`SymfonySecurityAuthorizationChecker`]. Handles tests for Symfony's authorization checker ([`Symfony\...\AuthorizationCheckerInterface`]).
  Supports tests instances of [`AbstractSymfonySecurityTest`].

  [`SymfonySecurityAuthorizationChecker`] utilizes [`SymfonySecurityResolver`] to resolve runtime variables.

## SymfonyAccessControlProvider details

The complexity with access_control rules is they are not directly mapped to particular routes.

access_control rules may have 4 possible constraints:
* path (regexp)
* host (regexp)
* methods (array)
* ips (array)

[`SymfonyAccessControlProvider`] filters matching rules for every route at compile time, comparing rule constraints and route parameters.

The best case for performance is when it's possible to determine a rule that would always match the route at runtime -
then it will return simple [`TestBag`].

In other cases (one or many potentially matching rules, dependently on runtime [`Request`]), 
it will return a [`RequestDependentTestBag`], that would be resolved at runtime.

For every route that needs a request-dependent test bag, RouteGuard will produce a log warning message during cache warmup, i.e.
```console
11:04:03 WARNING [route_guard] Route "secure" (path "/secure1/{page}") requires runtime matching to access_control rule(s) #1 (zero-based), this would reduce performance.
```

#### Matching access_control rules to a route at compile time

See [`RouteMatcher`].

Matching ips can't be done at compile time, matching methods is a simple arrays intersection.
The trickiest thing is matching path and host constraints. They are done in the same way, so we'll continue with path only.

At compile time we have only static prefix of the path. 
For static routes (without any variables), it would be equal to the resulting path at runtime, so we can simply match it to constraint regexp.
For dynamic routes, we parse constraint regexp, compare it to the static prefix
(basically, by regexp's static prefix), and determine if it would always / possibly / never match the resulting path at runtime.

#### Performance tips

The general performance tip for path and host constraints - to always use "string start" assert (`^`).

This could be illustrated by the following examples:

Rule `path: /foo` at compile time would be determined as potentially matching to ANY dynamic route - 
because at runtime any variable used in a route could result in a string `"/foo"`.

But rule `path: ^/foo` at compile time would be determined as potentially matching to dynamic routes with path static prefix 
`"/"` or `"/f"` or `"/foo"` or `"/foob"`, but not `"/bar"`. 

Even more, when regexp static prefix (`/foo`) is shorter or same length as path static prefix, and regexp doesn't have restrictions
on the symbols followed by it's static prefix (regexp is `^/foo` or `^/foo.*` or `^/foo.*$`), 
it means regexp would always match dynamic routes with path static prefix `"/foo"` or `"/foob"`, not depending on runtime Request.
This would result in an always matching access_control rule for a route (if there were no potentially matching rules found before) 
that would allow direct mapping of a rule to a route, without need to use a request-dependent test bag.

## SensioExtraProvider details

Sensio FrameworkExtraBundle executes expressions from `@Security` annotation in-place, bypassing standard flow 
(passing authorization test arguments to [`Symfony\...\AuthorizationCheckerInterface`]). 
See [`Sensio\...\SecurityListener`].

To be consistent in its flow, RouteGuard wraps those expressions into [`ExpressionDecorator`]
instances, and registers [`SensioSecurityExpressionVoter`] to handle them.

# Adding your own authorization test provider

At first, read the [Under the hood](#under-the-hood) section.

To create your own provider, you have to create a provider class that implements [`ProviderInterface`]
and register it as a service.

Next step depends on yours tests targets:

* Tests are intended for Symfony's authorization checker ([`Symfony\...\AuthorizationCheckerInterface`]):
  * Create your test class, that extends [`AbstractSymfonySecurityTest`].
  * Create your test resolver class, that implements [`SymfonySecurityResolverInterface`] and register it as a service.
  
* Tests are intended to a different authorization checker:
  * Create your test class, that implements [`TestInterface`].
  * Create your authorization checker class, that implements [`AuthorizationCheckerInterface`] and register it as a service.

If you are not using services autoconfiguration, you would also need to manually add tags:
* for your test provider service - `yarhon_route_guard.test_provider`
* for your authorization checker service - `yarhon_route_guard.authorization_checker`
* for your symfony security test resolver service - `yarhon_route_guard.test_resolver.symfony_security`.

If your tests require runtime controller arguments, you may consider using RouteGuard's [`ArgumentResolver`].

# What's planned

* Support of ParamConverter for controller arguments.
* Debug CLI command(s) to view data stored in authorization cache.
* Support of dynamic authorization tests:
  ```php
  namespace App\Controller;

  use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
  use Yarhon\RouteGuardBundle\Annotation\MethodCallAuthorizationTest;

  class Controller extends AbstractController
  {    
      public function testAdminIsGranted()
      {  
          // some logic
        
          $this->denyAccessUnlessGranted('ROLE_ADMIN');
      }
    
      /**
      * @MethodCallAuthorizationTest("testAdminIsGranted")
      */
      public function admin()
      {
          $this->testAdminIsGranted();
        
          // ............
      }
  }    
  ```
  In the example above, authorization logic is extracted into a separate method.
  Using this technique, the same authorization code could be used when action is accessed (in this case it is called explicitly),
  and when the route authorization is checked (thanks to `@MethodCallAuthorizationTest` annotation), i.e. when displaying a link for a route.



[`Request`]: https://github.com/symfony/http-foundation/blob/master/Request.php
[`Symfony\...\UrlGeneratorInterface::generate`]: https://github.com/symfony/routing/blob/master/Generator/UrlGeneratorInterface.php
[`Symfony\...\RouterListener::onKernelRequest`]: https://github.com/symfony/http-kernel/blob/master/EventListener/RouterListener.php
[`Symfony\...\ArgumentResolver`]: https://github.com/symfony/http-kernel/blob/master/Controller/ArgumentResolver.php
[`Symfony\...\AuthorizationCheckerInterface`]: https://github.com/symfony/security/blob/master/Core/Authorization/AuthorizationCheckerInterface.php
[`Sensio\...\SecurityListener`]: https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/EventListener/SecurityListener.php
[`Symfony\...\VoterInterface`]: https://github.com/symfony/security/blob/master/Core/Authorization/Voter/VoterInterface.php
[`Symfony\...\Expression`]: https://github.com/symfony/expression-language/blob/master/Expression.php

[`ControllerMetadata`]: Controller/ControllerMetadata.php
[`ArgumentResolver`]: Controller/ArgumentResolver.php
[`ArgumentValueResolverInterface`]: Controller/ArgumentResolver/ArgumentValueResolverInterface.php

[`RouteContextInterface`]: Routing/RouteContextInterface.php
[`RequestAttributesFactory`]: Routing/RequestAttributesFactory.php
[`AuthorizedUrlGeneratorInterface::generate`]: Routing/AuthorizedUrlGeneratorInterface.php

[`TestInterface`]: Security/Test/TestInterface.php
[`AbstractSymfonySecurityTest`]: Security/Test/AbstractSymfonySecurityTest.php
[`SymfonyAccessControlTest`]: Security/Test/SymfonyAccessControlTest.php
[`SensioExtraTest`]: Security/Test/SensioExtraTest.php

[`AbstractTestBagInterface`]: Security/Test/AbstractTestBagInterface.php
[`TestBag`]: Security/Test/TestBag.php
[`RequestDependentTestBag`]: Security/Http/RequestDependentTestBag.php

[`ProviderInterface`]: Security/TestProvider/ProviderInterface.php
[`ProviderInterface::getTests`]: Security/TestProvider/ProviderInterface.php
[`ProviderAggregate`]: Security/TestProvider/ProviderAggregate.php
[`SymfonyAccessControlProvider`]: Security/TestProvider/SymfonyAccessControlProvider.php
[`SensioExtraProvider`]: Security/TestProvider/SensioExtraProvider.php

[`TestLoaderInterface::load`]: Security/TestLoaderInterface.php
[`RouteAuthorizationChecker`]: Security/RouteAuthorizationChecker.php
[`DelegatingAuthorizationChecker`]: Security/AuthorizationChecker/DelegatingAuthorizationChecker.php
[`AuthorizationCheckerInterface`]: Security/AuthorizationChecker/AuthorizationCheckerInterface.php
[`SymfonySecurityAuthorizationChecker`]: Security/AuthorizationChecker/SymfonySecurityAuthorizationChecker.php
[`SymfonySecurityResolverInterface`]: Security/TestResolver/SymfonySecurityResolverInterface.php 
[`SymfonySecurityResolver`]: Security/TestResolver/SymfonySecurityResolver.php

[`AuthorizationCacheWarmer`]: Cache/AuthorizationCacheWarmer.php

[`ExceptionInterface`]: Exception/ExceptionInterface.php

[`RouteMatcher`]: Security/Http/RouteMatcher.php

[`ExpressionDecorator`]: ExpressionLanguage/ExpressionDecorator.php
[`SensioSecurityExpressionVoter`]: Security/Authorization/SensioSecurityExpressionVoter.php




















