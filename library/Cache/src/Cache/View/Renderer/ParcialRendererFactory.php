<?php
namespace Cache\View\Renderer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\StorageFactory;

class ParcialRendererFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$viewRenderer = $serviceLocator->get('ViewRenderer');
		$renderer = new ParcialRenderer();
		// Set cache storage
		$cacheStorage = StorageFactory::factory( $serviceLocator->get('config')['parcial-cache'] );
		$renderer->setCacheStorage( $cacheStorage );
		// Copy resolver
		$renderer->setResolver( $viewRenderer->resolver() );
		// Copy helper plugin manager
		$renderer->setHelperPluginManager( $viewRenderer->getHelperPluginManager() );
		// Copy filter chain
		$renderer->setFilterChain( $viewRenderer->getFilterChain() );
		return $renderer;
	}
}