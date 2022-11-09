<?php
namespace Coral\Base\Controller\Plugin;

require_once 'Zend/Controller/Request/Abstract.php';
require_once 'Zend/Controller/Plugin/Abstract.php';

class BaseControllerPluginCallback extends Zend_Controller_Plugin_Abstract {
	const ROUTE_STARTUP = 'routeStartup';
	const ROUTE_SHUTDOWN = 'routeShutdown';
	const DISPATCH_LOOP_STARTUP = 'dispatchLoopStartup';
	const PRE_DISPATCH = 'preDispatch';
	const POST_DISPATCH = 'postDispatch';
	const DISPATCH_LOOP_SHUTDOWN = 'dispatchLoopShutdown';
	
	protected $_handlers = array();
	
	public function __construct() {
		require_once 'NetB/Delegate.php';
		$this->initHandlers();
	}
	
	protected function initHandlers() {
		$this->_handlers = array(
			self::ROUTE_STARTUP => array(),
			self::ROUTE_SHUTDOWN => array(),
			self::DISPATCH_LOOP_STARTUP => array(),
			self::PRE_DISPATCH => array(),
			self::POST_DISPATCH => array(),
			self::DISPATCH_LOOP_SHUTDOWN => array()
		);
	}
	
	public function add($event, NetB_Delegate $callback) {
		$event = (string)$event;
		if( array_key_exists($event, $this->_handlers) && $callback != null ) {
			$this->_handlers[$event][] = $callback;
		}
		
		return $this;
	}
	
	public function addRouteStartup(NetB_Delegate $callback) {
		return $this->add( self::ROUTE_STARTUP, $callback );
	}
	
	public function addRouteShutdown(NetB_Delegate $callback) {
		return $this->add( self::ROUTE_SHUTDOWN, $callback );
	}
	
	public function addDispatchLoopStartup(NetB_Delegate $callback) {
		return $this->add( self::DISPATCH_LOOP_STARTUP, $callback );
	}
	
	public function addPreDispatch(NetB_Delegate $callback) {
		return $this->add( self::PRE_DISPATCH, $callback );
	}
	
	public function addPostDispatch(NetB_Delegate $callback) {
		return $this->add( self::POST_DISPATCH, $callback );
	}
	
	public function addDispatchLoopShutdown(NetB_Delegate $callback) {
		return $this->add( self::DISPATCH_LOOP_SHUTDOWN, $callback );
	}
	
    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request) {
		foreach($this->_handlers[self::ROUTE_STARTUP] as $callback) {
			$callback->invoke($request, self::ROUTE_STARTUP);
		}
	}

    /**
     * Called after Zend_Controller_Router exits.
     *
     * Called after Zend_Controller_Front exits from the router.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		foreach($this->_handlers[self::ROUTE_SHUTDOWN] as $callback) {
			$callback->invoke($request, self::ROUTE_SHUTDOWN);
		}
	}

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		foreach($this->_handlers[self::DISPATCH_LOOP_STARTUP] as $callback) {
			$callback->invoke($request, self::DISPATCH_LOOP_STARTUP);
		}
	}

    /**
     * Called before an action is dispatched by Zend_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior.  By altering the
     * request and resetting its dispatched flag (via
     * {@link Zend_Controller_Request_Abstract::setDispatched() setDispatched(false)}),
     * the current action may be skipped.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
		foreach($this->_handlers[self::PRE_DISPATCH] as $callback) {
			$callback->invoke($request, self::PRE_DISPATCH);
		}
	}

    /**
     * Called after an action is dispatched by Zend_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior. By altering the
     * request and resetting its dispatched flag (via
     * {@link Zend_Controller_Request_Abstract::setDispatched() setDispatched(false)}),
     * a new action may be specified for dispatching.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {
		foreach($this->_handlers[self::POST_DISPATCH] as $callback) {
			$callback->invoke($request, self::POST_DISPATCH);
		}
	}

    /**
     * Called before Zend_Controller_Front exits its dispatch loop.
     *
     * @return void
     */
    public function dispatchLoopShutdown() {
		foreach($this->_handlers[self::DISPATCH_LOOP_SHUTDOWN] as $callback) {
			$callback->invoke(self::DISPATCH_LOOP_SHUTDOWN);
		}
	}
}
