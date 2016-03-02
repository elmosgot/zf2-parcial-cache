<?php
namespace Cache\View\Strategy;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ParcialFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$viewRenderer = $serviceLocator->get('ParcialRenderer');
		return new ParcialStrategy($viewRenderer);
	}
}