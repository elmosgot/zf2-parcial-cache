<?php
namespace Cache\View\Renderer;

use Zend\View\Exception;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ModelInterface as Model;
use Cache\View\Model\ParcialModel;
use Zend\Cache\Storage\Adapter\Redis;
use Zend\Cache\Storage\StorageInterface;

class ParcialRenderer extends PhpRenderer {
	private $__varsCache;
	private $__template;
	private $__templates = array();
	private $__file;
	private $__content;
	private $__cacheStorage;
	
	public function init() {
		// Do something :-D
	}
	/**
	 * Add a template to the stack
	 *
	 * @param  string $template
	 * @return PhpRenderer
	 */
	public function addTemplate($template)
	{
		$this->__templates[] = $template;
		return $this;
	}
	/**
	 * Processes a view script and returns the output.
	 *
	 * @param  string|Model $nameOrModel Either the template to use, or a
	 *                                   ViewModel. The ViewModel must have the
	 *                                   template as an option in order to be
	 *                                   valid.
	 * @param  null|array|Traversable $values Values to use when rendering. If none
	 *                                provided, uses those in the composed
	 *                                variables container.
	 * @return string The script output.
	 * @throws Exception\DomainException if a ViewModel is passed, but does not
	 *                                   contain a template option.
	 * @throws Exception\InvalidArgumentException if the values passed are not
	 *                                            an array or ArrayAccess object
	 * @throws Exception\RuntimeException if the template cannot be rendered
	 */
	public function render($nameOrModel, $values = null)
	{
		$cachable = false;
		if ($nameOrModel instanceof Model) {
			$model       = $nameOrModel;
			$nameOrModel = $model->getTemplate();
			if (empty($nameOrModel)) {
				throw new Exception\DomainException(sprintf(
					'%s: received View Model argument, but template is empty',
					__METHOD__
				));
			}
			$options = $model->getOptions();
			foreach ($options as $setting => $value) {
				$method = 'set' . $setting;
				if (method_exists($this, $method)) {
					$this->$method($value);
				}
				unset($method, $setting, $value);
			}
			unset($options);
	
			// Give view model awareness via ViewModel helper
			$helper = $this->plugin('view_model');
			$helper->setCurrent($model);
	
			$values = $model->getVariables();
			$cachable = $model instanceof ParcialModel ? true : false;
			unset($model);
		}
	
		// find the script file name using the parent private method
		$this->addTemplate($nameOrModel);
		unset($nameOrModel); // remove $name from local scope
	
		$this->__varsCache[] = $this->vars();
	
		if (null !== $values) {
			$this->setVars($values);
		}
		unset($values);
		
		// extract all assigned vars (pre-escaped), but not 'this'.
		// assigns to a double-underscored variable, to prevent naming collisions
		$__vars = $this->vars()->getArrayCopy();
		if (array_key_exists('this', $__vars)) {
			unset($__vars['this']);
		}
		extract($__vars);
		unset($__vars); // remove $__vars from local scope
		
		$cacheKey = preg_replace( '/[\/\?]/',  '-', filter_input( INPUT_SERVER, 'HTTP_HOST' ) . filter_input( INPUT_SERVER, 'REQUEST_URI' ) );
		while ($this->__template = array_pop($this->__templates)) {
			set_time_limit( 30 );
			if( !$cachable || ( $cachable && ( $content = $this->getItem( $cacheKey ) ) === null ) ) {
				$this->__file = $this->resolver($this->__template);
				if (!$this->__file) {
					throw new Exception\RuntimeException(sprintf(
						'%s: Unable to render template "%s"; resolver could not resolve to a file',
						__METHOD__,
						$this->__template
					));
				}
				try {
					ob_start();
					$includeReturn = include $this->__file;
					$content = ob_get_clean();
					if( $cachable ) {
						$this->setItem( $cacheKey, $content );
					}
					$this->__content = $content;
				} catch (\Exception $ex) {
					ob_end_clean();
					throw $ex;
				}
				if ($includeReturn === false && empty($this->__content)) {
					throw new Exception\UnexpectedValueException(sprintf(
						'%s: Unable to render template "%s"; file include failed',
						__METHOD__,
						$this->__file
					));
				}
			}
		}
	
		$this->setVars(array_pop($this->__varsCache));
	
		return $this->getFilterChain()->filter($this->__content); // filter output
	}
	public function getItem( $key ) {
		return $this->getCacheStorage()->getItem( $key );
	}
	public function setItem( $key, $value ) {
		return $this->getCacheStorage()->setItem( $key, $value );
	} 
	/**
     * Set script cache storage
     *
     * @param  StorageInterface $cacheStorage
     * @return PhpRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setCacheStorage(StorageInterface $cacheStorage)
    {
        $this->__cacheStorage = $cacheStorage;
        return $this;
    }
	/**
	 * @return Redis
	 */
	public function getCacheStorage() {
		return $this->__cacheStorage;
	}
}