<?php
  
  class Templates
  {
    static $content;
    static $layout;
    static $templateRendered;
    static $pagetitle;
    
    static function yield($var = NULL)
    {
      // Default to yielding the main content
      if(!isset($var)) $var = 'content';
      
      if(!isset(self::$content[$var])) return;
      
      return self::$content[$var];
    }
    
    static function ActionTemplate($vars = array(), $template = NULL)
    {
      global $routes;
      extract($vars);
      
      ob_start();
      if(empty($template))
      {
        include "./app/views/".Params::controller()."/".Params::action(). "." . Params::format();
      }
      else
      {
        include "./app/views/".Params::controller()."/".$template. "." . Params::format();
      }
      
      
      self::$content['content'] = ob_get_contents();
      ob_end_clean();
      
      if(isset($_GET['ajax']) || Params::format() != 'html')
      {
          print self::$content['content'];
      }
      else
      {
          if(empty(self::$layout)) {
              include "./app/views/layout/application.html";
          } else {
              include "./app/views/layout/" . self::$layout . ".html";
          }
      }
      
      self::$templateRendered = true;
    }
    
    static function BaseURL()
    {
        global $config;
        return $config['path_prefix'] . dirname($_SERVER['SCRIPT_NAME']);
    }
    
    static function StartBlock()
    {
        ob_start();
    }
    
    static function EndBlock($name)
    {
        self::$content[$name] = ob_get_contents();
        ob_end_clean();
    }
    
    static function Fragment($fragment)
    {
        if(strpos($fragment, '/') !== false) {
            $parts = explode("/", $fragment);
            $file = "./app/views/{$parts[0]}/_{$parts[1]}.html";
        } else {
            $file = "./app/views/".Params::controller()."/_{$fragment}.html";
        }

        
        
        if(file_exists($file)) {
            include($file);
        }
    }

    static function EmitErrorPage($error)
    {
      switch($error) {
        case '404':
          header("HTTP/1.0 404 Not Found");
          break;
      }

       ob_start();
       include "./app/views/errors/{$error}.html";

       self::$content['content'] = ob_get_contents();
       ob_end_clean();
      
      if(isset($_GET['ajax']) || Params::format() != 'html')
      {
          print self::$content['content'];
      }
      else
      {
          if(empty(self::$layout)) {
              include "./app/views/layout/404.html";
          } else {
              include "./app/views/layout/" . self::$layout . ".html";
          }
      }
      
      self::$templateRendered = true;

    }
    
    static function RenderTemplateFallback()
    {
        if(!self::$templateRendered) {
            Templates::ActionTemplate();
        }
    }

    static function PageTitle($title = NULL)
    {
        if(isset($title)) {
            self::$pagetitle = $title;
        }

        return self::$pagetitle;
    }
  }
  
?>
