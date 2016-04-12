<?php
require_once 'inc/errHandler.php';

//composer load project stuff, like Twig and parsedown-extra
require_once 'vendor/autoload.php';

//setup Twig Markdown extension AND PHP-markdown parser.
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\TokenParser\MarkdownTokenParser;

//add the Markdown parser engine - https://github.com/michelf/php-markdown
$engine = new MarkdownEngine\MichelfMarkdownEngine();

//setup Twig
$loader = new Twig_Loader_Filesystem(array('templates','content'));
$twig = new Twig_Environment($loader, array("cache"=>false,"auto_reload"=>true,"debug"=>true));

//add the twig extension and token parser - https://github.com/aptoma/twig-markdown
$twig->addExtension(new MarkdownExtension($engine));
$twig->addTokenParser(new MarkdownTokenParser($engine));


//render HTML
echo $twig->render('about.twig',array(
  "class"=>"home"
));


?>
