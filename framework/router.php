<?php
  class Params
  {
    public static function __callStatic($name, $args)
    {
      global $map;
      if($name == 'all') {
        return $map->_route_args;
      }
      if(isset($map->_route_args[$name])) {
        return $map->_route_args[$name];
      }
      return NULL;
    }
    
    public static function Display() {
      print "<pre>".print_r(Params::all(), true)."</pre>";
    }
  }


	class PathRouter
	{
		private $_routes = array();
		public $_route_args = array();
		private $_root_action;
		private $_root_controller;
		function connect($route, $args = array())
		{
			// Take the route and split it into parts
			$route_bits = explode("/", trim($route, "/"));
			
			$new_route['path'] = $route_bits;
			$new_route['args'] = $args;
			
			array_push($this->_routes, $new_route);
		}
		
		function root($args = array()) {
		  $this->_root_controller = $args['controller'];
		  $this->_root_action = $args['action'];
		}
		
		function match($path)
		{
			foreach($this->_routes as $_route)
			{
				$route = &$_route['path'];
				
				// Compare lengths
				if(count($route) < count($path)) {
					continue;
				}

				// Transpose variables from path to route and compare
				$transposed_route = array_map(function($var, $arg) {
					// Check to see if element is a variable
					// Variables are route chunks that begin with a colon
					// like :controller, :action, :id, or even :foo
					
					// Check to see if path blob is a regular expression
					//if(preg_match('/^\[?\{:(.*)\}\]?$/', $var, $parts)) {
					
					if(preg_match('/^\[?:(.*)\((.*)\)\]?$/', $var, $parts)) {  
					  // $arg contains the regular value to compare agains the regular
					  // expression stored in $parts[2] for parameter in $parts[1]

					  $regex = "/^" . $parts[2] . "$/";
					  if(preg_match($regex, $arg)) {
					    return $arg;
					  } else {
					    return 'NULL';
					  }
					}
					
					if(preg_match('/^\[?:.*\]?$/', $var)) {
						return $arg;
					}
					return $var;
				}, $route, $path);
				
				$comparison = array_diff($transposed_route, $path);
				
				if(count($comparison) == 0) {
					return $_route;
				} else {
					// Check to see if the array difference is entirely made up of 
					// optional variables
					$non_optionals = array_filter($comparison, function($var) {
						if(!preg_match('/^\[.*\]$/', $var)) {
							return $var;
						} else {
						  if($var == NULL) return 'a';
						}
					});
					
					if(count($non_optionals) == 0)
					{
					    //print "<pre>" . print_r($_route, true) . "</pre>";
						return $_route;
					}
				}
			}
			return false;
		}
		
		function parse($url)
		{
			$url_bits = explode(".", trim($url, "/"));

			$this->_route_args['format'] = isset($url_bits[1])? $url_bits[1] : 'html';
			
			$url_bits = explode("/", trim($url_bits[0], "/"));
			
			$route = $this->match($url_bits);
			if($route)
			{
			  
			  // Combine the user provided and route provided arguments
				$user_arguments = array_map(function($route, $path) {
				  if(preg_match('/^\[?:(.*)\((.*)\)\]?$/', $route, $parts)) {  
				    // $arg contains the regular value to compare agains the regular
					  // expression stored in $parts[2] for parameter in $parts[1]
					  $regex = "/^" . $parts[2] . "$/";
					  if(preg_match($regex, $path)) {
					    return array($parts[1] => $path);
					  }
			    }
			    
					if(preg_match('/^\[?:([^\[\]]*)\]?$/', $route, $m)) {
						return array($m[1] => $path);
					}
				}, $route['path'], $url_bits);
				
				foreach($user_arguments as $arg) {
				  if(!isset($arg)) continue;
				  if(isset($arg[key($arg)])) {
				    $this->_route_args[key($arg)] = $arg[key($arg)];
				  }
				}
				if(isset($route['args'])) {
				  $this->_route_args = array_merge($this->_route_args, $route['args']);
				}
				
				// Set the action to index if none is defined
				if(!isset($this->_route_args['action'])) {
				  $this->_route_args['action'] = 'index';
				}
				
			}
			
			// Set the controller to the root controller if none is defined
			if(!isset($this->_route_args['controller']) || $this->_route_args['controller'] == '') {
			  $this->_route_args['controller'] = $this->_root_controller;
			  $this->_route_args['action'] = $this->_root_action;
			  
			}			
		}
	}
?>
