<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\view;
use aura\di\Container;

/**
 * 
 * Provides an abstract TemplateView pattern implementation. We use an 
 * abstract so that the extended "real" Template class does not have access
 * to the private support properties herein.
 * 
 * @package aura.view
 * 
 */
abstract class AbstractTemplate
{
    /**
     * 
     * View "finder" (to find views in a path stack).
     * 
     * @var Finder
     * 
     */
    private $_finder;
    
    /**
     * 
     * Data assigned to the template.
     * 
     * @var array
     * 
     */
    private $_data = array();
    
    /**
     * 
     * A Container for helper objects, so that repeated calls to the same 
     * helper use the same object.
     * 
     * @var aura\di\Container
     * 
     */
    private $_helper_container;
    
    /**
     * 
     * Constructor.
     * 
     * @param Finder $finder A template finder.
     * 
     * @param HelperRegistry $helper_container A container for helpers attached
     * to this template.
     * 
     */
    public function __construct(
        Finder $finder,
        Container $helper_container
    ) {
        $this->_finder = $finder;
        $this->_helper_container = $helper_container;
    }
    
    /**
     * 
     * Magic read access to template data.
     * 
     * @param string $key The template variable name.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        return $this->_data[$key];
    }
    
    /**
     * 
     * Magic write access to template data.
     * 
     * @param string $key The template variable name.
     * 
     * @param string $val The template variable value.
     * 
     * @return mixed
     * 
     */
    public function __set($key, $val)
    {
        $this->_data[$key] = $val;
    }
    
    /**
     * 
     * Magic isset() checks on template data.
     * 
     * @param string $key The template variable name.
     * 
     * @return bool
     * 
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }
    
    /**
     * 
     * Magic unset() for template data.
     * 
     * @param string $key The template variable name.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        unset($this->_data[$key]);
    }
    
    /**
     * 
     * Magic call to provide shared helpers as template methods.
     * 
     * @param string $name The helper name.
     * 
     * @param array $args The arguments to pass to the helper.
     * 
     * @return void
     * 
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);
        return call_user_func_array(array($helper, '__invoke'), $args);
    }
    
    /**
     * 
     * Sets the search paths for templates; paths are searched in FIFO order.
     * 
     * @param array $paths An array of directory paths where templates are.
     * 
     * @return void
     * 
     */
    public function setPaths(array $paths = array())
    {
        $this->_finder->setPaths($paths);
    }
    
    /**
     * 
     * Sets all template variables at once.
     * 
     * @param array $data An array of key-value pairs where the keys are 
     * template variable names, and the values are the variable values.
     * 
     * @return void
     * 
     */
    public function setData(array $data = array())
    {
        $this->_data = $data;
    }
    
    /**
     * 
     * Gets all template variables.
     * 
     * @return array An array of key-value pairs where the keys are 
     * template variable names, and the values are the variable values.
     * 
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * 
     * Returns the path to the requested template script; searches through
     * $this->paths to find the first matching template.
     * 
     * @param string $name The template name to look for in the template path.
     * 
     * @return string The full path to the template script.
     * 
     */
    public function find($name)
    {
        // append ".php" if needed
        if (substr($name, -4) != '.php') {
            $name .= '.php';
        }
        
        // find the path to the template
        $file = $this->_finder->find($name);
        if (! $file) {
            throw new Exception_TemplateNotFound($name);
        }
        
        // done!
        return $file;
    }
    
    /**
     * 
     * Retrieves a shared helper from the helper container.
     * 
     * @param string $name The helper to retrieve.
     * 
     * @return mixed
     * 
     */
    public function getHelper($name)
    {
        return $this->_helper_container->get($name);
    }
    
    /**
     * 
     * Fetches the output from a template.
     * 
     * @param string $name The template name to use.
     * 
     * @param array $vars Variables to extract into the local scope.
     * 
     * @return string
     * 
     */
    abstract public function fetch($name, array $vars = array());
}
