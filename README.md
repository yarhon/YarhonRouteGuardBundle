# RouteGuardBundle 

Symfony routes authorization checker

[![Build Status](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yarhon/RouteGuardBundle/?branch=master)
# About

RouteGuardBundle is a tool to:
* check if user is authorized to access a route
* retrieve authorization rules for a route
* conditionally display blocks in Twig templates depending on authorization rules, avoiding
authorization checks duplication both in controller and template.

RouteGuard supports authorization rules from:
* Symfony SecurityBundle (`access_control` rules). Read [details](#symfony-securitybundle-details). 
* Sensio FrameworkExtraBundle (`@IsGranted` and `@Security` annotations). Read [details](#sensio-frameworkextrabundle-details).

And allows to add your own authorization rules providers. Read [more](#adding-your-own-authorization-rules).

RouteGuard has a few limitations for rare use cases. Read [more](#limitations).

Let the code speak:

A) Template rendering

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog/{page}", name="blog")
     */
    public function index($page)
    {
        return $this->render('default/blog.html.twig', []);
    }
```


```twig
{% route 'blog', { page: 1} %}
    <a href="{{ _route.ref }}">Blog link</a>
{% else %}
    No access
{% endroute %}
```
In this example, link will be rendered only if user is authorized to access route `blog` (by any of the supported rules), contents of the `else` block rendered otherwise.

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
* `data_collector`
  * `ignore_controllers`. Array of controller names that would be ignored by RouteGuard. Controller names should be specified in
class::method notation. You can specify:
    * full controller name, i.e. `App\Controller\DefaultController::index`
    * controller class name, i.e. `App\Controller\DefaultController`
    * controller name prefix, i.e. `App\Controller\`
  
    Note: for "controller-as-a-service" controllers you have to specify service name, not class name.
  
    Default value: `[]`
  * `ignore_controllers_symfony`. Array of default Symfony controllers that would be ignored by RouteGuard.

    Default value: 
    ```php 
    [
        'twig.controller.preview_error',
        'web_profiler.controller.profiler',
        'web_profiler.controller.router',
        'web_profiler.controller.exception',
    ]
    ```
  
  * `ignore_exceptions`. Boolean, if true - 
  
    Default value: `false`  
* `twig`
  * `tag_name`. Name of the Twig tag. Default value: `'route'`.
  * `tag_variable_name`. Name of the tag inner variable (array), that would contain route info (i.e., generated URL). Default value: `'_route'`.
  * `discover_routing_functions`. Boolean, specifies whether to use "discover" mode in twig tag. Default value: `true`.
  
# Usage

## Twig templates

### Twig tag ("route") syntax

Twig tag arguments are split into two parts: first one is route context array, second one, after the `as` keyword, 
specifies required reference type in literal form. 

Route context array has the following arguments:
* routeName (string, required)
* parameters (array, optional, default value: [])
* method (string, optional, default value: 'GET').

Reference type is specified in the following forms:
* path [absolute|relative]. Equal to generating URL with `path()` function.
* url [absolute|relative]. Equal to generating URL with `url()` function.
If no reference type is specified, "path absolute" would be used. If only first part ("path" or "url") is specified,
"absolute" would be used as a second part.

Examples:
```twig
{% route 'blog', { page: 1}, 'GET' as path absolute %}
{% route 'blog', { page: 1}, 'GET' as url relative %}
{% route 'blog' as url %}
{% route 'blog' %}
```

For those, who want to try RouteGuard with minimal effort, it has "discover" mode. In this mode, RouteGuard will search
for `path()` or `url()` function call inside "route" tag, and then use function arguments as tag arguments.
Following two examples will produce the same result:
```twig
{% route discover %}
    <a href="{{ url('blog', { page: 1}, true) }}">Blog link</a>
{% endroute %}
```
```twig
{% route 'blog', { page: 1}, 'GET' as url relative %}
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

Interface: `Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface`

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
```

### RouteTestResolver

Allows to retrieve all authorization tests for a route.

Service id: `yarhon_route_guard.route_test_resolver`

Interface: `Yarhon\RouteGuardBundle\Security\RouteTestResolverInterface`

Example:
```php
namespace App\Service;

use Yarhon\RouteGuardBundle\Security\RouteTestResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;

class SomeService
{
    private $testResolver;
    
    public function __construct(RouteTestResolverInterface $testResolver)
    {
        $this->testResolver = $testResolver;
    }
    
    public function getTests()
    {
        $routeContext = new RouteContext('blog', ['page' => 10], 'GET');
        
        return $this->testResolver->getTests($routeContext);
    }
```

The `RouteTestResolverInterface::getTests()` method will return an array of `Yarhon\RouteGuardBundle\Security\Test\TestArguments` instances.

### AuthorizedUrlGenerator

Allows to generate URLs outside of Twig context.

Service id: `yarhon_route_guard.authorized_url_generator`

Interface: `Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGeneratorInterface`

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
```
The `AuthorizedUrlGeneratorInterface::generate` method signature is similar to Symfony's `UrlGeneratorInterface::generate`, except
it adds `$method` as a 3rd parameter, moving `$referenceType` to 4th place.

# Limitations

## General limitations

RouteGuard doesn't modify current `Request` object when it passes it to the security voters and `ExpressionLanguage` expressions.

That means methods of `Request` object, that return url / host / method related parameters, being used inside
voters / expressions, would return values irrelevant to the route being checked.

In this case results of RouteGuard authorization checks are "Undefined behaviour". 
These methods include:
* getPathInfo
* getHost
* getHttpHost
* getMethod
* isMethod*
* getRequestUri
* getUri

## Sensio FrameworkExtraBundle limitations

#### In short

Sensio FrameworkExtraBundle allows to use user-defined variables as arguments to authorization tests
("subject" argument or variables in `ExpressionLanguage` expressions).

If you are using some variable that is supposed to be resolved from Request attributes,
and this variable is not a part of route parameters (route variables + defaults), 
RouteGuard would not be able to resolve it and will throw an exception. 

#### Details

See `Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter`.
Variables could be taken from:
* Request attributes
* Controller arguments (that can be resolved from different sources, including Request attributes).

Request attributes are initially set from the route parameters (route variables + defaults). 
See `Symfony\Component\HttpKernel\EventListener\RouterListener::onKernelRequest`.

However, in Symfony, Request attributes are used more widely as just route parameters - they are used as implicit 
"information exchange point" between different components.

In turn, RouteGard can't use any Request attributes, other than those that came from parameters of the route being checked.
Usage of attributes of the current Request could be irrelevant to this route.

Additionally, unlike standard flow, RouteGuard doesn't add special "_route" attribute and removes "_controller" attribute
from Request attributes - so they can't be used as authorization test arguments too.

## Troubleshooting

If you are facing problem(s), described above, you can add controller(s) triggering it to the list of ignored ones.

Feel free to contact the author with any undescribed problems / bugs.


# Under the hood

RouteGuard collects all authorization tests in compile time, at cache warmup. 
Entry point: `Yarhon\RouteGuardBundle\CacheWarmer\AccessMapCacheWarmer`.

Tests are collected by `Yarhon\RouteGuardBundle\Security\AccessMapBuilder` and stored in `Yarhon\RouteGuardBundle\Security\AccessMap`.

Each authorization rules provider is presented by two main classes:
* Test provider - collects authorization tests at compile time.

  Interface: `Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface`.
  
  It returns `Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface` instance, which in a simple case is a collection of
  `Yarhon\RouteGuardBundle\Security\Test\TestArguments` instances.
* Test resolver - resolves authorization tests bag (`AbstractTestBagInterface` instance) at runtime: 
  resolves required runtime variables, etc.
  
  Interface: `Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface`.
  
  It returns a collection of resolved `TestArguments` instances, ready to be passed to the `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`.

## Symfony SecurityBundle details

The complexity with access_control rules is they are not directly mapped to particular routes.

access_control rules may have 4 possible constraints:
* path (regexp)
* host (regexp)
* methods (array)
* ips (array)

RouteGuard filters matching rules for every route at compile time, comparing rule constraints and route parameters.

The best case for performance is when it's possible to determine a rule that would always match the route at runtime.

In other cases (one or many potentially matching rules, dependently on runtime Request), 
RouteGuard will create a map of rules to match (`Yarhon\RouteGuardBundle\Security\Http\TestBagMap` instance), 
that needs to be resolved at runtime.

For every route that needs a map of rules, RouteGuard will produce a log warning message during cache warmup, i.e.
```console
11:04:03 WARNING [route_guard] Route "secure" (path "/secure1/{page}") requires runtime matching to access_control rule(s) #1 (zero-based), this would reduce performance.
```

#### Matching access_control rules to a route at compile time

See `Yarhon\RouteGuardBundle\Security\Http\RouteMatcher`.

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
that would allow direct mapping of a rule to a route, without need to use a map of rules.


## Sensio FrameworkExtraBundle details

Sensio FrameworkExtraBundle executes expressions from `@Security` annotation in-place, bypassing standard flow 
(passing authorization test arguments to security voters via "isGranted" call). 
See `Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener`.

To be consistent in its flow, RouteGuard wraps those expressions into `Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator`
instances, and registers security voter `Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter` to handle them.


# Adding your own authorization rules

At first, read the [Under the hood](#under-the-hood) section.

To add support for your authorization rules you have to implement 2 classes:
* Test provider (`Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface`)
* Test resolver (`Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface`)

and register them as services. 

If you are not using services autoconfiguration, you need to add
`yarhon_route_guard.test_provider` tag to test provider service, and `yarhon_route_guard.test_resolver` tag to test resolver service.


















