<?php
/**
 * Server for Serialized RPC
 */
class Vps_Srpc_Server
{
    protected $_handler;

    /**
     * Whether {@link handle} should return or output the response. false means it will be echo'ed.
     */
    protected $_returnResponse = false;

    public function __construct(array $config = array())
    {
        if (isset($config['handler'])) $this->setHandler($config['handler']);
        if (isset($config['returnResponse'])) $this->_returnResponse = $config['returnResponse'];
    }

    /**
     * Link for {@link setHandler}, to act the same way as Zend_Rest_Server
     */
    public function setClass($handler)
    {
        $this->setHandler($handler);
    }

    public function setHandler($handler)
    {
        if (is_string($handler)) {
            $this->_handler = new $handler();
        } else {
            $this->_handler = $handler;
        }
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    public function handle($method = null, $arguments = null, $extraParams = null)
    {
        try {
            if (is_null($method) && isset($_REQUEST['method']) && !is_null($_REQUEST['method'])) {
                $method = $_REQUEST['method'];
            }
            if (is_null($arguments) && isset($_REQUEST['arguments']) && !is_null($_REQUEST['arguments'])) {
                $arguments = unserialize($_REQUEST['arguments']);
            }
            if (is_null($extraParams) && isset($_REQUEST['extraParams']) && !is_null($_REQUEST['extraParams'])) {
                $extraParams = unserialize($_REQUEST['extraParams']);
            }
            if (is_null($arguments)) {
                $arguments = array();
            }

            // throw some exceptions
            if (!$this->_handler) {
                throw new Vps_Srpc_Exception("A handler has to be set when using 'Vpc_Srpc_Server'");
            }
            if (is_null($method)) {
                throw new Vps_Srpc_Exception("'method' must be set as first argument, or exists as key in ".'$_REQUEST');
            }
            if (!is_null($method) && !is_string($method)) {
                throw new Vps_Srpc_Exception("'method' is expected to be a string");
            }
            if (!is_null($arguments) && !is_array($arguments)) {
                throw new Vps_Srpc_Exception("'arguments' is expected to be an array");
            }

            $handler = $this->getHandler();
            if ($extraParams) $handler->setExtraParams($extraParams);

            $result = call_user_func_array(array($handler, $method), $arguments);
            $result = serialize($result);

            if (strpos($result, 'Vps_') !== false) {
                throw new Vps_Exception("a class name with 'Vps_' must not be sent through srpc server");
            }
        } catch (Exception $e) {
            $result = "An exception has been caught occured in Srpc_Server:\n\n"
                ."Message: ".$e->getMessage()."\n"
                ."File: ".$e->getFile().':'.$e->getLine()."\n"
                ."Trace: \n---=== Trace start ===---\n".$e->getTraceAsString()."\n---=== Trace end ===---\n";
        }

        if (!$this->_returnResponse) {
            echo $result;
            return;
        } else {
            return $result;
        }
    }
}
