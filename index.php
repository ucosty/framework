<?php
    session_start();
    
    // Load ALL of the framework files
    //
    foreach (glob("framework/*.php") as $filename)
    {
        require_once $filename;
    }

    // PathRouter is used for parsing URL strings and turning them
    // into parameters which can be used to direct the application
    //
    // At a minimum the Controller and Action parameter will always
    // contain values.
    //
    $map = new PathRouter();

    // Load the Application and Routing configuration
    //
	require_once('config/application.php');
  	require_once('config/routes.php');

    // Set the default timezone
    //
	date_default_timezone_set($_CONFIG['timezone']);

	// Parse the URL variable or use the default '/' path
	//
	$url = isset($_GET['url'])? $_GET['url'] : '/';
	$map->parse($url);
  	    
    
    // Load the view helpers
    // 
  	foreach (glob("app/helpers/*.php") as $filename)
    {
        require_once $filename;
    }

    // Load the controller if it exists
    //
    if(file_exists("app/controllers/" . Params::controller() . ".php"))
    {
        require_once("app/controllers/" . Params::controller() . ".php");
    }

    // Instance and run the controller
    //
    $controller_class = ucfirst(Params::controller()) . "Controller";
    
    if(!class_exists($controller_class)) {
        
        // Class does *not* exist, die
        //
        if($_CONFIG['debug']) {
            // Debugging message - probably should do something more useful
            die("{$controller_class} does not exist.");
        } else {
            
            // Display a nice 404 error message, for the masses
            die(Templates::EmitErrorPage('404'));
        }
        
    }
    
    // Instance the controller class
    $controller = new $controller_class();
    $action = Params::action();
    
    // Run the controller action, if it exists
    //
    if(!method_exists($controller, $action)) {
        if(method_exists($controller, "_{$action}")) {
            $action = "_{$action}";
        } else {
            if($_CONFIG['debug']) {
                die("No such action '{$action}' in {$controller_class}");        
            } else {
                die(Templates::EmitErrorPage('404'));
            }
        }
    }
    
    if(method_exists($controller, '__before_filter')) {
        $controller->__before_filter();
    }
    
    $controller->$action();

    if(method_exists($controller, '__after_filter')) {
        $controller->__after_filter();
    }
    
    Templates::RenderTemplateFallback();
?>