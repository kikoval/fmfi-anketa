<?php
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
class appProdProjectContainer extends Container
{
    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();
        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();
        $this->set('service_container', $this);
        $this->scopes = array('request' => 'container');
        $this->scopeChildren = array('request' => array());
    }
    protected function getAnnotations_Cache_ArrayService()
    {
        return new \Doctrine\Common\Cache\ArrayCache();
    }
    protected function getAnnotations_ConfigurationReaderService()
    {
        return $this->services['annotations.configuration_reader'] = new \Sensio\Bundle\FrameworkExtraBundle\Configuration\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache(), $this->get('annotations.parser'));
    }
    protected function getAnnotations_ControllerParserService()
    {
        return $this->services['annotations.controller_parser'] = new \Sensio\Bundle\FrameworkExtraBundle\Controller\ControllerAnnotationParser($this->get('annotations.configuration_reader'));
    }
    protected function getAnnotations_ParserService()
    {
        $this->services['annotations.parser'] = $instance = new \Doctrine\Common\Annotations\Parser();
        $instance->setAutoloadAnnotations(true);
        $instance->setAnnotationNamespaceAlias('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\', 'extra');
        return $instance;
    }
    protected function getAnnotations_ReaderService()
    {
        return $this->services['annotations.reader'] = new \Doctrine\Common\Annotations\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache(), $this->get('annotations.parser'));
    }
    protected function getAssetic_AssetManagerService()
    {
        $a = $this->get('templating.loader');
        $this->services['assetic.asset_manager'] = $instance = new \Assetic\Factory\LazyAssetManager($this->get('assetic.asset_factory'), array('twig' => new \Assetic\Factory\Loader\CachedFormulaLoader(new \Assetic\Extension\Twig\TwigFormulaLoader($this->get('twig')), new \Assetic\Cache\ConfigCache('/Users/ivan/Sites/Symfony2/app/cache/prod/assetic/config'), false)));
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FrameworkBundle', '/Users/ivan/Sites/Symfony2/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views', '/^[^.]+\\.[^.]+\\.twig$/'), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'TwigBundle', '/Users/ivan/Sites/Symfony2/vendor/symfony/src/Symfony/Bundle/TwigBundle/Resources/views', '/^[^.]+\\.[^.]+\\.twig$/'), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DoctrineBundle', '/Users/ivan/Sites/Symfony2/vendor/symfony/src/Symfony/Bundle/DoctrineBundle/Resources/views', '/^[^.]+\\.[^.]+\\.twig$/'), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FMFIAnketaBundle', '/Users/ivan/Sites/Symfony2/src/FMFI/AnketaBundle/Resources/views', '/^[^.]+\\.[^.]+\\.twig$/'), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, '', '/Users/ivan/Sites/Symfony2/app/views', '/^[^.]+\\.[^.]+\\.twig$/'), 'twig');
        return $instance;
    }
    protected function getAssetic_Filter_CoffeeService()
    {
        return $this->services['assetic.filter.coffee'] = new \Assetic\Filter\CoffeeScriptFilter('/usr/bin/coffee', '/usr/bin/node');
    }
    protected function getAssetic_Filter_CssrewriteService()
    {
        return $this->services['assetic.filter.cssrewrite'] = new \Assetic\Filter\CssRewriteFilter();
    }
    protected function getAssetic_Filter_GoogleClosureCompilerService()
    {
        return $this->services['assetic.filter.google_closure_compiler'] = new \Assetic\Filter\GoogleClosure\CompilerApiFilter();
    }
    protected function getAssetic_Filter_LessService()
    {
        return $this->services['assetic.filter.less'] = new \Assetic\Filter\LessFilter('/Users/ivan/Sites/Symfony2/app/../web', '/usr/bin/node', array());
    }
    protected function getAssetic_Filter_SassService()
    {
        return $this->services['assetic.filter.sass'] = new \Assetic\Filter\Sass\SassFilter('/usr/bin/sass');
    }
    protected function getAssetic_Filter_ScssService()
    {
        return $this->services['assetic.filter.scss'] = new \Assetic\Filter\Sass\ScssFilter('/usr/bin/sass');
    }
    protected function getAssetic_Filter_SprocketsService()
    {
        return $this->services['assetic.filter.sprockets'] = new \Assetic\Filter\SprocketsFilter('/Users/ivan/Sites/Symfony2/app/../web', '/usr/bin/sprocketize');
    }
    protected function getAssetic_Filter_StylusService()
    {
        return $this->services['assetic.filter.stylus'] = new \Assetic\Filter\StylusFilter('/Users/ivan/Sites/Symfony2/app/../web', '/usr/bin/node', array());
    }
    protected function getAssetic_FilterManagerService()
    {
        return $this->services['assetic.filter_manager'] = new \Symfony\Bundle\AsseticBundle\FilterManager($this, array('cssrewrite' => 'assetic.filter.cssrewrite', 'less' => 'assetic.filter.less', 'sass' => 'assetic.filter.sass', 'scss' => 'assetic.filter.scss', 'closure' => 'assetic.filter.google_closure_compiler', 'sprockets' => 'assetic.filter.sprockets', 'coffee' => 'assetic.filter.coffee', 'stylus' => 'assetic.filter.stylus'));
    }
    protected function getCache_AnnotationService()
    {
        return $this->services['cache.annotation'] = new \Sensio\Bundle\FrameworkExtraBundle\Cache\AnnotationCacheListener();
    }
    protected function getCacheWarmerService()
    {
        $a = $this->get('assetic.asset_manager');
        return $this->services['cache_warmer'] = new \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate(array(0 => new \Symfony\Bundle\AsseticBundle\CacheWarmer\AssetManagerCacheWarmer($a), 1 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassMapCacheWarmer($this), 2 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\RouterCacheWarmer($this->get('router.real')), 3 => new \Symfony\Bundle\DoctrineBundle\CacheWarmer\ProxyCacheWarmer($this), 4 => new \Symfony\Bundle\AsseticBundle\CacheWarmer\AssetWriterCacheWarmer($a, new \Assetic\AssetWriter('/Users/ivan/Sites/Symfony2/app/../web'), $this->get('event_dispatcher'))));
    }
    protected function getConverter_DoctrineService()
    {
        return $this->services['converter.doctrine'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter($this->get('doctrine.orm.default_entity_manager'));
    }
    protected function getConverter_ListenerService()
    {
        return $this->services['converter.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\Controller\ParamConverterListener($this->get('converter.manager'));
    }
    protected function getConverter_ManagerService()
    {
        $this->services['converter.manager'] = $instance = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager(array());
        $instance->add($this->get('converter.doctrine'), 0);
        return $instance;
    }
    protected function getDoctrine_Dbal_ConnectionFactoryService()
    {
        return $this->services['doctrine.dbal.connection_factory'] = new \Symfony\Bundle\DoctrineBundle\ConnectionFactory(array());
    }
    protected function getDoctrine_Dbal_DefaultConnectionService()
    {
        return $this->services['doctrine.dbal.default_connection'] = $this->get('doctrine.dbal.connection_factory')->createConnection(array('host' => 'localhost', 'driver' => 'pdo_sqlite', 'driverOptions' => array(), 'user' => 'root', 'password' => '', 'port' => NULL, 'dbname' => 'symfony'), new \Doctrine\DBAL\Configuration(), new \Doctrine\Common\EventManager());
    }
    protected function getDoctrine_Orm_DefaultEntityManagerService()
    {
        $a = new \Doctrine\Common\Cache\ArrayCache();
        $a->setNamespace('sf2orm_default');
        $b = new \Doctrine\Common\Cache\ArrayCache();
        $b->setNamespace('sf2orm_default');
        $c = new \Doctrine\Common\Cache\ArrayCache();
        $c->setNamespace('sf2orm_default');
        $d = new \Doctrine\ORM\Configuration();
        $d->setEntityNamespaces(array());
        $d->setMetadataCacheImpl($a);
        $d->setQueryCacheImpl($b);
        $d->setResultCacheImpl($c);
        $d->setMetadataDriverImpl(new \Doctrine\ORM\Mapping\Driver\DriverChain());
        $d->setProxyDir('/Users/ivan/Sites/Symfony2/app/cache/prod/doctrine/orm/Proxies');
        $d->setProxyNamespace('Proxies');
        $d->setAutoGenerateProxyClasses(false);
        $d->setClassMetadataFactoryName('Doctrine\\ORM\\Mapping\\ClassMetadataFactory');
        return $this->services['doctrine.orm.default_entity_manager'] = call_user_func(array('Doctrine\\ORM\\EntityManager', 'create'), $this->get('doctrine.dbal.default_connection'), $d);
    }
    protected function getEventDispatcherService()
    {
        $this->services['event_dispatcher'] = $instance = new \Symfony\Bundle\FrameworkBundle\EventDispatcher($this);
        $instance->registerKernelListeners(array('core.request' => array(0 => array(0 => array(0 => 'request_listener', 1 => 'handle'))), 'core.response' => array(0 => array(0 => array(0 => 'response_listener', 1 => 'filter'), 1 => array(0 => 'cache.annotation', 1 => 'filter'))), 'core.exception' => array(-128 => array(0 => array(0 => 'exception_listener', 1 => 'handle'))), 'core.controller' => array(0 => array(0 => array(0 => 'annotations.controller_parser', 1 => 'filter'), 1 => array(0 => 'converter.listener', 1 => 'filter'), 2 => array(0 => 'view.template_annotation', 1 => 'filterController'))), 'core.view' => array(0 => array(0 => array(0 => 'view.template_annotation', 1 => 'filterView')))));
        return $instance;
    }
    protected function getExceptionListenerService()
    {
        return $this->services['exception_listener'] = new \Symfony\Component\HttpKernel\Debug\ExceptionListener('Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController::showAction', NULL);
    }
    protected function getFileLocatorService()
    {
        return $this->services['file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator($this->get('kernel'));
    }
    protected function getFilesystemService()
    {
        return $this->services['filesystem'] = new \Symfony\Bundle\FrameworkBundle\Util\Filesystem();
    }
    protected function getForm_ContextService()
    {
        return $this->services['form.context'] = new \Symfony\Component\Form\FormContext(array('validator' => $this->get('validator'), 'validation_groups' => 'Default', 'field_factory' => $this->get('form.field_factory'), 'csrf_protection' => true, 'csrf_field_name' => '_token', 'csrf_provider' => $this->get('form.csrf_provider')));
    }
    protected function getForm_CsrfProviderService()
    {
        return $this->services['form.csrf_provider'] = new \Symfony\Component\Form\CsrfProvider\SessionCsrfProvider($this->get('session'), '54b25c1d37479717f2f4bbdb5103f421d');
    }
    protected function getForm_FieldFactoryService()
    {
        return $this->services['form.field_factory'] = new \Symfony\Component\Form\FieldFactory\FieldFactory(array(0 => new \Symfony\Component\Form\FieldFactory\ValidatorFieldFactoryGuesser($this->get('validator.mapping.class_metadata_factory')), 1 => new \Symfony\Component\Form\FieldFactory\EntityFieldFactoryGuesser($this->get('doctrine.orm.default_entity_manager'))));
    }
    protected function getHttpKernelService()
    {
        $this->services['http_kernel'] = $instance = new \Symfony\Bundle\FrameworkBundle\HttpKernel($this, new \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver($this, $this->get('controller_name_converter'), NULL));
        $instance->setEventDispatcher($this->get('event_dispatcher'));
        return $instance;
    }
    protected function getMailerService()
    {
        require_once '/Users/ivan/Sites/Symfony2/vendor/swiftmailer/lib/swift_init.php';
        return $this->services['mailer'] = new \Swift_Mailer($this->get('swiftmailer.transport'));
    }
    protected function getRequestService()
    {
        if (!isset($this->scopedServices['request'])) {
            throw new \RuntimeException('You cannot create a service ("request") of an inactive scope ("request").');
        }
        throw new \RuntimeException('You have requested a synthetic service ("request"). The DIC does not know how to construct this service.');
    }
    protected function getRequestListenerService()
    {
        return $this->services['request_listener'] = new \Symfony\Bundle\FrameworkBundle\RequestListener($this, $this->get('router.real'), NULL);
    }
    protected function getResponseListenerService()
    {
        return $this->services['response_listener'] = new \Symfony\Component\HttpKernel\ResponseListener('UTF-8');
    }
    protected function getRouter_CachedService()
    {
        return $this->services['router.cached'] = new \Symfony\Bundle\FrameworkBundle\Routing\CachedRouter('/Users/ivan/Sites/Symfony2/app/cache/prod', 'appprodUrlMatcher', 'appprodUrlGenerator');
    }
    protected function getRouter_RealService()
    {
        return $this->services['router.real'] = new \Symfony\Component\Routing\Router(new \Symfony\Bundle\FrameworkBundle\Routing\LazyLoader($this, 'routing.loader.real'), '/Users/ivan/Sites/Symfony2/app/config/routing.yml', array('cache_dir' => '/Users/ivan/Sites/Symfony2/app/cache/prod', 'debug' => false, 'generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper', 'generator_cache_class' => 'appprodUrlGenerator', 'matcher_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher', 'matcher_base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher', 'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper', 'matcher_cache_class' => 'appprodUrlMatcher', 'resource_type' => ''));
    }
    protected function getRouting_Loader_AnnotClassService()
    {
        $this->services['routing.loader.annot_class'] = $instance = new \Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader($this->get('annotations.reader'), $this->get('annotations.configuration_reader'));
        $instance->setAnnotationClass('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route');
        return $instance;
    }
    protected function getRouting_Loader_AnnotDirService()
    {
        return $this->services['routing.loader.annot_dir'] = new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($this->get('routing.file_locator'), $this->get('routing.loader.annot_class'));
    }
    protected function getRouting_Loader_AnnotFileService()
    {
        return $this->services['routing.loader.annot_file'] = new \Symfony\Component\Routing\Loader\AnnotationFileLoader($this->get('routing.file_locator'), $this->get('routing.loader.annot_class'));
    }
    protected function getRouting_Loader_AnnotGlobService()
    {
        return $this->services['routing.loader.annot_glob'] = new \Symfony\Component\Routing\Loader\AnnotationGlobLoader($this->get('routing.file_locator'), $this->get('routing.loader.annot_class'));
    }
    protected function getRouting_Loader_RealService()
    {
        $a = $this->get('routing.file_locator');
        $b = new \Symfony\Component\Config\Loader\LoaderResolver();
        $b->addLoader(new \Symfony\Component\Routing\Loader\XmlFileLoader($a));
        $b->addLoader(new \Symfony\Component\Routing\Loader\YamlFileLoader($a));
        $b->addLoader(new \Symfony\Component\Routing\Loader\PhpFileLoader($a));
        $b->addLoader($this->get('routing.loader.annot_glob'));
        $b->addLoader($this->get('routing.loader.annot_dir'));
        $b->addLoader($this->get('routing.loader.annot_file'));
        $b->addLoader($this->get('routing.loader.annot_class'));
        return $this->services['routing.loader.real'] = new \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader($this->get('controller_name_converter'), NULL, $b);
    }
    protected function getSessionService()
    {
        $this->services['session'] = $instance = new \Symfony\Component\HttpFoundation\Session($this->get('session.storage'), array('default_locale' => 'en'));
        $instance->start();
        return $instance;
    }
    protected function getSession_StorageService()
    {
        return $this->services['session.storage'] = new \Symfony\Component\HttpFoundation\SessionStorage\NativeSessionStorage(array('lifetime' => 3600));
    }
    protected function getSwiftmailer_TransportService()
    {
        $this->services['swiftmailer.transport'] = $instance = new \Swift_Transport_EsmtpTransport(new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()), array(0 => new \Swift_Transport_Esmtp_AuthHandler(array(0 => new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(), 1 => new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(), 2 => new \Swift_Transport_Esmtp_Auth_PlainAuthenticator()))), new \Swift_Events_SimpleEventDispatcher());
        $instance->setHost('localhost');
        $instance->setPort(25);
        $instance->setEncryption(NULL);
        $instance->setUsername('');
        $instance->setPassword('');
        $instance->setAuthMode(NULL);
        return $instance;
    }
    protected function getTemplatingService()
    {
        return $this->services['templating'] = new \Symfony\Bundle\TwigBundle\TwigEngine($this->get('twig'), $this->get('templating.name_parser'), $this->get('twig.globals'));
    }
    protected function getTemplating_Helper_ActionsService()
    {
        return $this->services['templating.helper.actions'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\ActionsHelper($this->get('http_kernel'));
    }
    protected function getTemplating_Helper_AssetsService()
    {
        return $this->services['templating.helper.assets'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper($this->get('request'), array(), NULL);
    }
    protected function getTemplating_Helper_CodeService()
    {
        return $this->services['templating.helper.code'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\CodeHelper(NULL, '/Users/ivan/Sites/Symfony2/app');
    }
    protected function getTemplating_Helper_FormService()
    {
        $a = new \Symfony\Bundle\FrameworkBundle\Templating\PhpEngine($this->get('templating.name_parser'), $this, $this->get('templating.loader'));
        $a->setCharset('UTF-8');
        $a->setHelpers(array('slots' => 'templating.helper.slots', 'assets' => 'templating.helper.assets', 'request' => 'templating.helper.request', 'session' => 'templating.helper.session', 'router' => 'templating.helper.router', 'actions' => 'templating.helper.actions', 'code' => 'templating.helper.code', 'translator' => 'templating.helper.translator', 'form' => 'templating.helper.form', 'assetic' => 'assetic.helper'));
        return $this->services['templating.helper.form'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper($a);
    }
    protected function getTemplating_Helper_RequestService()
    {
        return $this->services['templating.helper.request'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\RequestHelper($this->get('request'));
    }
    protected function getTemplating_Helper_RouterService()
    {
        return $this->services['templating.helper.router'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper($this->get('router.real'));
    }
    protected function getTemplating_Helper_SessionService()
    {
        return $this->services['templating.helper.session'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\SessionHelper($this->get('request'));
    }
    protected function getTemplating_Helper_SlotsService()
    {
        return $this->services['templating.helper.slots'] = new \Symfony\Component\Templating\Helper\SlotsHelper();
    }
    protected function getTemplating_Helper_TranslatorService()
    {
        return $this->services['templating.helper.translator'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\TranslatorHelper($this->get('translator'));
    }
    protected function getTemplating_LoaderService()
    {
        return $this->services['templating.loader'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader($this->get('templating.locator'));
    }
    protected function getTemplating_NameParserService()
    {
        return $this->services['templating.name_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser($this->get('kernel'));
    }
    protected function getTranslation_Loader_PhpService()
    {
        return $this->services['translation.loader.php'] = new \Symfony\Component\Translation\Loader\PhpFileLoader();
    }
    protected function getTranslation_Loader_XliffService()
    {
        return $this->services['translation.loader.xliff'] = new \Symfony\Component\Translation\Loader\XliffFileLoader();
    }
    protected function getTranslation_Loader_YmlService()
    {
        return $this->services['translation.loader.yml'] = new \Symfony\Component\Translation\Loader\YamlFileLoader();
    }
    protected function getTranslatorService()
    {
        return $this->services['translator'] = new \Symfony\Component\Translation\IdentityTranslator($this->get('translator.selector'));
    }
    protected function getTranslator_RealService()
    {
        $this->services['translator.real'] = $instance = new \Symfony\Bundle\FrameworkBundle\Translation\Translator($this, $this->get('translator.selector'), array('cache_dir' => '/Users/ivan/Sites/Symfony2/app/cache/prod/translations', 'debug' => false), $this->get('session'));
        $instance->setFallbackLocale('en');
        return $instance;
    }
    protected function getTwigService()
    {
        $this->services['twig'] = $instance = new \Twig_Environment($this->get('twig.loader'), array('debug' => false, 'strict_variables' => false, 'cache' => '/Users/ivan/Sites/Symfony2/app/cache/prod/twig', 'charset' => 'UTF-8'));
        $instance->addExtension(new \Symfony\Bundle\TwigBundle\Extension\TransExtension($this->get('translator')));
        $instance->addExtension(new \Symfony\Bundle\TwigBundle\Extension\TemplatingExtension($this));
        $instance->addExtension(new \Symfony\Bundle\TwigBundle\Extension\FormExtension(array(0 => 'TwigBundle::form.html.twig')));
        $instance->addExtension(new \Symfony\Bundle\AsseticBundle\Twig\StaticExtension($this->get('assetic.asset_factory'), false, 'js/*.js', 'css/*.css'));
        return $instance;
    }
    protected function getTwig_GlobalsService()
    {
        return $this->services['twig.globals'] = new \Symfony\Bundle\TwigBundle\GlobalVariables($this);
    }
    protected function getTwig_LoaderService()
    {
        return $this->services['twig.loader'] = new \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader($this->get('templating.locator'), $this->get('templating.name_parser'));
    }
    protected function getValidatorService()
    {
        return $this->services['validator'] = new \Symfony\Component\Validator\Validator($this->get('validator.mapping.class_metadata_factory'), new \Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory($this, array()));
    }
    protected function getView_TemplateAnnotationService()
    {
        return $this->services['view.template_annotation'] = new \Sensio\Bundle\FrameworkExtraBundle\View\AnnotationTemplateListener($this);
    }
    protected function getAnnotations_CacheService()
    {
        return $this->get('annotations.cache.array');
    }
    protected function getDatabaseConnectionService()
    {
        return $this->get('doctrine.dbal.default_connection');
    }
    protected function getDoctrine_Orm_EntityManagerService()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }
    protected function getRouterService()
    {
        return $this->get('router.real');
    }
    protected function getAssetic_AssetFactoryService()
    {
        $this->services['assetic.asset_factory'] = $instance = new \Symfony\Bundle\AsseticBundle\Factory\AssetFactory($this->get('kernel'), '/Users/ivan/Sites/Symfony2/app/../web', false);
        $instance->setFilterManager($this->get('assetic.filter_manager'));
        return $instance;
    }
    protected function getControllerNameConverterService()
    {
        return $this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser($this->get('kernel'), NULL);
    }
    protected function getRouting_FileLocatorService()
    {
        return $this->services['routing.file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator($this->get('kernel'));
    }
    protected function getTemplating_LocatorService()
    {
        return $this->services['templating.locator'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator($this->get('file_locator'), '/Users/ivan/Sites/Symfony2/app');
    }
    protected function getTranslator_SelectorService()
    {
        return $this->services['translator.selector'] = new \Symfony\Component\Translation\MessageSelector();
    }
    protected function getValidator_Mapping_ClassMetadataFactoryService()
    {
        return $this->services['validator.mapping.class_metadata_factory'] = new \Symfony\Component\Validator\Mapping\ClassMetadataFactory(new \Symfony\Component\Validator\Mapping\Loader\LoaderChain(array(0 => new \Symfony\Component\Validator\Mapping\Loader\AnnotationLoader(array('validation' => 'Symfony\\Component\\Validator\\Constraints\\')), 1 => new \Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader('loadValidatorMetadata'), 2 => new \Symfony\Component\Validator\Mapping\Loader\XmlFilesLoader(array(0 => '/Users/ivan/Sites/Symfony2/vendor/symfony/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/../../../Component/Form/Resources/config/validation.xml')), 3 => new \Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader(array()))));
    }
    public function getParameter($name)
    {
        $name = strtolower($name);
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }
        return $this->parameters[$name];
    }
    public function hasParameter($name)
    {
        return array_key_exists(strtolower($name), $this->parameters);
    }
    public function setParameter($name, $value)
    {
        throw new \LogicException('Impossible to call set() on a frozen ParameterBag.');
    }
    protected function getDefaultParameters()
    {
        return array(
            'kernel.root_dir' => '/Users/ivan/Sites/Symfony2/app',
            'kernel.environment' => 'prod',
            'kernel.debug' => false,
            'kernel.name' => 'app',
            'kernel.cache_dir' => '/Users/ivan/Sites/Symfony2/app/cache/prod',
            'kernel.logs_dir' => '/Users/ivan/Sites/Symfony2/app/logs',
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'ZendBundle' => 'Symfony\\Bundle\\ZendBundle\\ZendBundle',
                'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle',
                'DoctrineBundle' => 'Symfony\\Bundle\\DoctrineBundle\\DoctrineBundle',
                'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle',
                'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle',
                'FMFIAnketaBundle' => 'FMFI\\AnketaBundle\\FMFIAnketaBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'database_driver' => 'pdo_sqlite',
            'database_host' => 'localhost',
            'database_name' => 'symfony',
            'database_user' => 'root',
            'database_password' => '',
            'mailer_transport' => 'smtp',
            'mailer_host' => 'localhost',
            'mailer_user' => '',
            'mailer_password' => '',
            'locale' => 'en',
            'csrf_secret' => '54b25c1d37479717f2f4bbdb5103f421d',
            'request_listener.class' => 'Symfony\\Bundle\\FrameworkBundle\\RequestListener',
            'controller_resolver.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
            'controller_name_converter.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameParser',
            'response_listener.class' => 'Symfony\\Component\\HttpKernel\\ResponseListener',
            'exception_listener.class' => 'Symfony\\Component\\HttpKernel\\Debug\\ExceptionListener',
            'exception_listener.controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ExceptionController::showAction',
            'form.field_factory.class' => 'Symfony\\Component\\Form\\FieldFactory\\FieldFactory',
            'form.field_factory.validator_guesser.class' => 'Symfony\\Component\\Form\\FieldFactory\\ValidatorFieldFactoryGuesser',
            'form.csrf_provider.class' => 'Symfony\\Component\\Form\\CsrfProvider\\SessionCsrfProvider',
            'form.context.class' => 'Symfony\\Component\\Form\\FormContext',
            'form.csrf_protection.enabled' => true,
            'form.csrf_protection.field_name' => '_token',
            'form.csrf_protection.secret' => '54b25c1d37479717f2f4bbdb5103f421d',
            'form.validation_groups' => 'Default',
            'event_dispatcher.class' => 'Symfony\\Bundle\\FrameworkBundle\\EventDispatcher',
            'http_kernel.class' => 'Symfony\\Bundle\\FrameworkBundle\\HttpKernel',
            'error_handler.class' => 'Symfony\\Component\\HttpKernel\\Debug\\ErrorHandler',
            'error_handler.level' => NULL,
            'filesystem.class' => 'Symfony\\Bundle\\FrameworkBundle\\Util\\Filesystem',
            'cache_warmer.class' => 'Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerAggregate',
            'cache_warmer.autoloader_map.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\ClassMapCacheWarmer',
            'file_locator.class' => 'Symfony\\Component\\HttpKernel\\Config\\FileLocator',
            'translator.class' => 'Symfony\\Bundle\\FrameworkBundle\\Translation\\Translator',
            'translator.identity.class' => 'Symfony\\Component\\Translation\\IdentityTranslator',
            'translator.selector.class' => 'Symfony\\Component\\Translation\\MessageSelector',
            'translation.loader.php.class' => 'Symfony\\Component\\Translation\\Loader\\PhpFileLoader',
            'translation.loader.yml.class' => 'Symfony\\Component\\Translation\\Loader\\YamlFileLoader',
            'translation.loader.xliff.class' => 'Symfony\\Component\\Translation\\Loader\\XliffFileLoader',
            'translator.fallback_locale' => 'en',
            'kernel.cache_warmup' => true,
            'router.class' => 'Symfony\\Component\\Routing\\Router',
            'router.cached.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\CachedRouter',
            'routing.loader.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader',
            'routing.file_locator.class' => 'Symfony\\Component\\HttpKernel\\Config\\FileLocator',
            'routing.resolver.class' => 'Symfony\\Component\\Config\\Loader\\LoaderResolver',
            'routing.loader.xml.class' => 'Symfony\\Component\\Routing\\Loader\\XmlFileLoader',
            'routing.loader.yml.class' => 'Symfony\\Component\\Routing\\Loader\\YamlFileLoader',
            'routing.loader.php.class' => 'Symfony\\Component\\Routing\\Loader\\PhpFileLoader',
            'router.options.generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'router.options.matcher_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'router.options.matcher_base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'router.options.matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'router.cache_warmer.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\RouterCacheWarmer',
            'router.options.matcher.cache_class' => 'appprodUrlMatcher',
            'router.options.generator.cache_class' => 'appprodUrlGenerator',
            'router.options.resource_type' => '',
            'routing.resource' => '/Users/ivan/Sites/Symfony2/app/config/routing.yml',
            'session.class' => 'Symfony\\Component\\HttpFoundation\\Session',
            'session.default_locale' => 'en',
            'session.storage.native.class' => 'Symfony\\Component\\HttpFoundation\\SessionStorage\\NativeSessionStorage',
            'session.storage.native.options' => array(
                'lifetime' => 3600,
            ),
            'session.storage.pdo.class' => 'Symfony\\Component\\HttpFoundation\\SessionStorage\\PdoSessionStorage',
            'session.storage.pdo.options' => array(
            ),
            'session.storage.array.class' => 'Symfony\\Component\\HttpFoundation\\SessionStorage\\ArraySessionStorage',
            'session.storage.array.options' => array(
            ),
            'templating.engine.delegating.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\DelegatingEngine',
            'templating.name_parser.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateNameParser',
            'templating.cache_warmer.template_paths.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\TemplatePathsCacheWarmer',
            'templating.locator.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\TemplateLocator',
            'templating.locator.cached.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\CachedTemplateLocator',
            'templating.loader.filesystem.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\FilesystemLoader',
            'templating.loader.cache.class' => 'Symfony\\Component\\Templating\\Loader\\CacheLoader',
            'templating.loader.chain.class' => 'Symfony\\Component\\Templating\\Loader\\ChainLoader',
            'templating.assets.version' => NULL,
            'templating.assets.base_urls' => array(
            ),
            'debug.file_link_format' => NULL,
            'templating.engine.php.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\PhpEngine',
            'templating.helper.slots.class' => 'Symfony\\Component\\Templating\\Helper\\SlotsHelper',
            'templating.helper.assets.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper',
            'templating.helper.actions.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\ActionsHelper',
            'templating.helper.router.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper',
            'templating.helper.request.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RequestHelper',
            'templating.helper.session.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\SessionHelper',
            'templating.helper.code.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\CodeHelper',
            'templating.helper.translator.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\TranslatorHelper',
            'templating.helper.form.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\FormHelper',
            'templating.loader.cache.path' => NULL,
            'templating.engines' => array(
                0 => 'twig',
            ),
            'validator.class' => 'Symfony\\Component\\Validator\\Validator',
            'validator.mapping.class_metadata_factory.class' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory',
            'validator.mapping.loader.loader_chain.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\LoaderChain',
            'validator.mapping.loader.static_method_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\StaticMethodLoader',
            'validator.mapping.loader.annotation_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\AnnotationLoader',
            'validator.mapping.loader.xml_file_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\XmlFileLoader',
            'validator.mapping.loader.yaml_file_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\YamlFileLoader',
            'validator.mapping.loader.xml_files_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\XmlFilesLoader',
            'validator.mapping.loader.yaml_files_loader.class' => 'Symfony\\Component\\Validator\\Mapping\\Loader\\YamlFilesLoader',
            'validator.mapping.loader.static_method_loader.method_name' => 'loadValidatorMetadata',
            'validator.validator_factory.class' => 'Symfony\\Bundle\\FrameworkBundle\\Validator\\ConstraintValidatorFactory',
            'validator.annotations.namespaces' => array(
                'validation' => 'Symfony\\Component\\Validator\\Constraints\\',
            ),
            'twig.class' => 'Twig_Environment',
            'twig.loader.class' => 'Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader',
            'twig.globals.class' => 'Symfony\\Bundle\\TwigBundle\\GlobalVariables',
            'templating.engine.twig.class' => 'Symfony\\Bundle\\TwigBundle\\TwigEngine',
            'templating.cache_warmer.templates_cache.class' => 'Symfony\\Bundle\\TwigBundle\\CacheWarmer\\TemplateCacheCacheWarmer',
            'twig.form.resources' => array(
                0 => 'TwigBundle::form.html.twig',
            ),
            'twig.options' => array(
                'debug' => false,
                'strict_variables' => false,
                'cache' => '/Users/ivan/Sites/Symfony2/app/cache/prod/twig',
                'charset' => 'UTF-8',
            ),
            'swiftmailer.class' => 'Swift_Mailer',
            'swiftmailer.transport.sendmail.class' => 'Swift_Transport_SendmailTransport',
            'swiftmailer.transport.mail.class' => 'Swift_Transport_MailTransport',
            'swiftmailer.transport.failover.class' => 'Swift_Transport_FailoverTransport',
            'swiftmailer.init_file' => '/Users/ivan/Sites/Symfony2/vendor/swiftmailer/lib/swift_init.php',
            'swiftmailer.plugin.redirecting.class' => 'Swift_Plugins_RedirectingPlugin',
            'swiftmailer.plugin.blackhole.class' => 'Swift_Plugins_BlackholePlugin',
            'swiftmailer.base_dir' => '/Users/ivan/Sites/Symfony2/vendor/swiftmailer/lib',
            'swiftmailer.transport.smtp.class' => 'Swift_Transport_EsmtpTransport',
            'swiftmailer.transport.name' => 'smtp',
            'swiftmailer.transport.smtp.encryption' => NULL,
            'swiftmailer.transport.smtp.port' => 25,
            'swiftmailer.transport.smtp.host' => 'localhost',
            'swiftmailer.transport.smtp.username' => '',
            'swiftmailer.transport.smtp.password' => '',
            'swiftmailer.transport.smtp.auth_mode' => NULL,
            'swiftmailer.single_address' => NULL,
            'doctrine.dbal.default_connection' => 'default',
            'doctrine.dbal.logger.debug_class' => 'Doctrine\\DBAL\\Logging\\DebugStack',
            'doctrine.dbal.logger_class' => 'Symfony\\Bundle\\DoctrineBundle\\Logger\\DbalLogger',
            'doctrine.dbal.configuration_class' => 'Doctrine\\DBAL\\Configuration',
            'doctrine.data_collector.class' => 'Symfony\\Bundle\\DoctrineBundle\\DataCollector\\DoctrineDataCollector',
            'doctrine.dbal.event_manager_class' => 'Doctrine\\Common\\EventManager',
            'doctrine.dbal.connection_factory_class' => 'Symfony\\Bundle\\DoctrineBundle\\ConnectionFactory',
            'doctrine.dbal.events.mysql_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\MysqlSessionInit',
            'doctrine.dbal.events.oracle_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\OracleSessionInit',
            'doctrine.dbal.logging' => 0,
            'doctrine.dbal.types' => array(
            ),
            'doctrine.orm.default_entity_manager' => 'default',
            'doctrine.orm.metadata_cache_driver' => 'array',
            'doctrine.orm.query_cache_driver' => 'array',
            'doctrine.orm.result_cache_driver' => 'array',
            'doctrine.orm.configuration_class' => 'Doctrine\\ORM\\Configuration',
            'doctrine.orm.entity_manager_class' => 'Doctrine\\ORM\\EntityManager',
            'doctrine.orm.auto_generate_proxy_classes' => false,
            'doctrine.orm.entity_managers' => array(
                0 => 'default',
            ),
            'doctrine.orm.proxy_namespace' => 'Proxies',
            'doctrine.orm.proxy_dir' => '/Users/ivan/Sites/Symfony2/app/cache/prod/doctrine/orm/Proxies',
            'doctrine.orm.cache.array_class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine.orm.cache.apc_class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine.orm.cache.memcache_class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine.orm.cache.memcache_host' => 'localhost',
            'doctrine.orm.cache.memcache_port' => 11211,
            'doctrine.orm.cache.memcache_instance_class' => 'Memcache',
            'doctrine.orm.cache.xcache_class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine.orm.metadata.driver_chain_class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
            'doctrine.orm.metadata.annotation_class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
            'doctrine.orm.metadata.annotation_reader_class' => 'Doctrine\\Common\\Annotations\\AnnotationReader',
            'doctrine.orm.metadata.xml_class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
            'doctrine.orm.metadata.yml_class' => 'Doctrine\\ORM\\Mapping\\Driver\\YamlDriver',
            'doctrine.orm.metadata.php_class' => 'Doctrine\\ORM\\Mapping\\Driver\\PHPDriver',
            'doctrine.orm.metadata.staticphp_class' => 'Doctrine\\ORM\\Mapping\\Driver\\StaticPHPDriver',
            'doctrine.orm.class_metadata_factory_name' => 'Doctrine\\ORM\\Mapping\\ClassMetadataFactory',
            'doctrine.orm.proxy_cache_warmer.class' => 'Symfony\\Bundle\\DoctrineBundle\\CacheWarmer\\ProxyCacheWarmer',
            'form.field_factory.doctrine_guesser.class' => 'Symfony\\Component\\Form\\FieldFactory\\EntityFieldFactoryGuesser',
            'doctrine.orm.default_connection' => 'default',
            'assetic.asset_factory.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\AssetFactory',
            'assetic.asset_manager.class' => 'Assetic\\Factory\\LazyAssetManager',
            'assetic.filter_manager.class' => 'Symfony\\Bundle\\AsseticBundle\\FilterManager',
            'assetic.cached_formula_loader.class' => 'Assetic\\Factory\\Loader\\CachedFormulaLoader',
            'assetic.config_cache.class' => 'Assetic\\Cache\\ConfigCache',
            'assetic.asset_manager_cache_warmer.class' => 'Symfony\\Bundle\\AsseticBundle\\CacheWarmer\\AssetManagerCacheWarmer',
            'assetic.directory_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\DirectoryResource',
            'assetic.filter.coffee.class' => 'Assetic\\Filter\\CoffeeScriptFilter',
            'assetic.filter.cssrewrite.class' => 'Assetic\\Filter\\CssRewriteFilter',
            'assetic.filter.google_closure_compiler_api.class' => 'Assetic\\Filter\\GoogleClosure\\CompilerApiFilter',
            'assetic.filter.google_closure_compiler_jar.class' => 'Assetic\\Filter\\GoogleClosure\\CompilerJarFilter',
            'assetic.filter.less.class' => 'Assetic\\Filter\\LessFilter',
            'assetic.filter.sass.class' => 'Assetic\\Filter\\Sass\\SassFilter',
            'assetic.filter.scss.class' => 'Assetic\\Filter\\Sass\\ScssFilter',
            'assetic.filter.sprockets.class' => 'Assetic\\Filter\\SprocketsFilter',
            'assetic.filter.stylus.class' => 'Assetic\\Filter\\StylusFilter',
            'assetic.java_bin' => '/usr/bin/java',
            'assetic.sass_bin' => '/usr/bin/sass',
            'assetic.node_bin' => '/usr/bin/node',
            'assetic.node_paths' => array(
            ),
            'assetic.sprocketize_bin' => '/usr/bin/sprocketize',
            'assetic.coffee_bin' => '/usr/bin/coffee',
            'assetic.cache_dir' => '/Users/ivan/Sites/Symfony2/app/cache/prod/assetic',
            'assetic.twig_extension.dynamic.class' => 'Symfony\\Bundle\\AsseticBundle\\Twig\\DynamicExtension',
            'assetic.twig_extension.static.class' => 'Symfony\\Bundle\\AsseticBundle\\Twig\\StaticExtension',
            'assetic.twig_formula_loader.class' => 'Assetic\\Extension\\Twig\\TwigFormulaLoader',
            'assetic.helper.class' => 'Symfony\\Bundle\\AsseticBundle\\Templating\\AsseticHelper',
            'assetic.php_formula_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Loader\\AsseticHelperFormulaLoader',
            'assetic.debug' => false,
            'assetic.use_controller' => false,
            'assetic.read_from' => '/Users/ivan/Sites/Symfony2/app/../web',
            'assetic.write_to' => '/Users/ivan/Sites/Symfony2/app/../web',
            'assetic.default_javascripts_output' => 'js/*.js',
            'assetic.default_stylesheets_output' => 'css/*.css',
            'assetic.asset_writer_cache_warmer.class' => 'Symfony\\Bundle\\AsseticBundle\\CacheWarmer\\AssetWriterCacheWarmer',
            'assetic.asset_writer.class' => 'Assetic\\AssetWriter',
            'assetic.twig_extension.class' => 'Symfony\\Bundle\\AsseticBundle\\Twig\\StaticExtension',
            'annotations.reader.class' => 'Doctrine\\Common\\Annotations\\AnnotationReader',
            'annotations.configuration_reader.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\AnnotationReader',
            'annotations.parser.class' => 'Doctrine\\Common\\Annotations\\Parser',
            'annotations.controller_parser.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Controller\\ControllerAnnotationParser',
            'routing.loader.annot_glob.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationGlobLoader',
            'routing.loader.annot_dir.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader',
            'routing.loader.annot_file.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader',
            'routing.loader.annot_class.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Routing\\AnnotatedRouteControllerLoader',
            'converter.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Controller\\ParamConverterListener',
            'converter.manager.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            'converter.doctrine.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
            'view.template_annotation.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\View\\AnnotationTemplateListener',
            'kernel.compiled_classes' => array(
                0 => 'Symfony\\Component\\Routing\\RouterInterface',
                1 => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface',
                2 => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
                3 => 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface',
                4 => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
                5 => 'Symfony\\Component\\Routing\\Router',
                6 => 'Symfony\\Component\\HttpFoundation\\Session',
                7 => 'Symfony\\Component\\HttpFoundation\\SessionStorage\\SessionStorageInterface',
                9 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface',
                10 => 'Symfony\\Component\\Templating\\TemplateNameParserInterface',
                11 => 'Symfony\\Component\\Templating\\TemplateNameParser',
                12 => 'Symfony\\Component\\Templating\\EngineInterface',
                13 => 'Symfony\\Component\\Config\\FileLocatorInterface',
                14 => 'Symfony\\Component\\Templating\\TemplateReferenceInterface',
                15 => 'Symfony\\Component\\Templating\\TemplateReference',
                16 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateReference',
                17 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateNameParser',
                18 => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\TemplateLocator',
                19 => 'Symfony\\Component\\HttpFoundation\\ParameterBag',
                20 => 'Symfony\\Component\\HttpFoundation\\HeaderBag',
                21 => 'Symfony\\Component\\HttpFoundation\\Request',
                22 => 'Symfony\\Component\\HttpFoundation\\Response',
                23 => 'Symfony\\Component\\HttpFoundation\\ResponseHeaderBag',
                24 => 'Symfony\\Component\\HttpKernel\\HttpKernel',
                25 => 'Symfony\\Component\\HttpKernel\\ResponseListener',
                26 => 'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver',
                27 => 'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface',
                28 => 'Symfony\\Bundle\\FrameworkBundle\\RequestListener',
                29 => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameParser',
                30 => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
                31 => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller',
                32 => 'Symfony\\Component\\EventDispatcher\\EventInterface',
                33 => 'Symfony\\Component\\EventDispatcher\\Event',
                34 => 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
                35 => 'Symfony\\Component\\EventDispatcher\\EventDispatcher',
                36 => 'Symfony\\Bundle\\FrameworkBundle\\EventDispatcher',
                37 => 'Twig_Environment',
                38 => 'Twig_ExtensionInterface',
                39 => 'Twig_Extension',
                40 => 'Twig_Extension_Core',
                41 => 'Twig_Extension_Escaper',
                42 => 'Twig_Extension_Optimizer',
                43 => 'Twig_LoaderInterface',
                44 => 'Twig_Markup',
                45 => 'Twig_TemplateInterface',
                46 => 'Twig_Template',
            ),
            'kernel.autoload_classes' => array(
            ),
            'translation.loaders' => array(
                'translation.loader.php' => 'php',
                'translation.loader.yml' => 'yml',
                'translation.loader.xliff' => 'xliff',
            ),
        );
    }
}
