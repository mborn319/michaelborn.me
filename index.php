<?php
/***********************************
 *  Blog page
 *  Handles individual articles as well as a paginated list of all articles.
***********************************/
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


/***********************************
 * Here's the blog-specific stuff.
 * tell the blog class to look for article files in the /content/blog/articles/ directory.
 *
 * if ?permalink= is defined
 * then we show the individual blog post.
 * else we show the 10 most recent blog articles. (only the summary)
***********************************/
$blogArticleDir = "/content/blog/articles/";


if (isset($_GET["permalink"])) {
    //load the blog class
    require_once 'Blogly/blog-class.php';

    //Init the blog class, and specify where the blog articles are stored.
    $article = new Blog($blogArticleDir);

    //Tell the blog which article we're interested in.
    $article->set_article_name($_GET["permalink"]);

    if ($article->exists()) {
        //parse the article, separating parameters (date, title, etc.) from body.
        $curArticle = $article->parse_article();

        //send the array of articles to Twig.
        echo $twig->render('article.html.twig',["article"=>$curArticle]);
    } else {
        //Show 404 message.
        echo "Blog article could not be found.";
    }

} else {
    //else standard blog list page.
    //load the blog class
    require_once 'Blogly/blog-class.php';

    //Init the blog class, and specify where the blog articles are stored.
    $blog = new Blog($blogArticleDir);

    //get the last 5 articles. Note the articles are automatically parsed.
    $recentArticles = $blog->get_n_articles(10);//last 10

    //send the array of articles to Twig.
    echo $twig->render('blog.html.twig',["articles"=>$recentArticles]);
}

?>
