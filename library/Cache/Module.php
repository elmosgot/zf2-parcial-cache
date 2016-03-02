<?php
namespace Cache;

use Zend\Mvc\MvcEvent;
use Zend\Cache\StorageFactory;
use Cache\Controller\CacheController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class Module {
	public function onBootstrap( MvcEvent $e ) {
		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'preDispatch'),100);
	}
	/**
	 * Check for cached content
	 */
	public function preDispatch( MvcEvent $e ) {
		$sm = $e->getApplication()->getServiceManager();
		$cacheStorage = StorageFactory::factory( $sm->get('config')['parcial-cache'] );
		$key = preg_replace( '/[\/\?]/',  '-', filter_input( INPUT_SERVER, 'HTTP_HOST' ) . filter_input( INPUT_SERVER, 'REQUEST_URI' ) );// . filter_input( INPUT_SERVER, 'QUERY_STRING' );
		if( ( $page = $cacheStorage->getItem( $key ) ) !== null ) {
			$e->stopPropagation(true);
			// Build cached view
			$page = new ViewModel( array( 'page' => $page ) );
			$page->setTemplate( 'cache/cache/index' );
			// Wrap template around
			$layout = new ViewModel();
			$layout->setTemplate('layout/layout');
			$layout->addChild( $page );
			$e->setResponse(new Response());
			$e->setViewModel($layout);
		}
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	public function getServiceConfig() {
		return array(
			'factories' => array(
				'ParcialStrategy' => 'Cache\View\Strategy\ParcialFactory',
				'ParcialRenderer' => 'Cache\View\Renderer\ParcialRendererFactory'
			)
		);
	}
	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				)
			)
		);
	}
}