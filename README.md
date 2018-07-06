Yarhon LinkGuard Bundle
==========================

Limitations:
- URL generation process is completely independent of HTTP method that will be used with the generated URL.
  So, "methods" option of access_control rules is not taken into account when making decision on URL access.
- 
   
   
If someone redefined router service completely, and it's not extends from the basic one,
(\Symfony\Component\Routing\Router), he has to set it's own UrlGeneratorConfigurator 
(see \Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator) for
this router service.
