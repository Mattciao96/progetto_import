<?php

/**
 * @author Stefano Martellos
 * @version 2.0
 * @created 17-Jan-2010
 * @last-modified 03-Mar-2022
*/

class View
{

  public function getPage($title, $content, $javascript_links=array())
  {
    $view = '
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="public/style.css">
        <title>'.$title.'</title>';
    
    foreach ($javascript_links as $javascript_link){
      $view .= '<script src="'.$javascript_link.'" defer></script>';
    } 
    $view .= ' 
      </head>
      <body>
      '.$content.'
      </body>
    </html>
    ';

    echo $view;
  }	

}
