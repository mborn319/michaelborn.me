

<?php
/***********************************
 * Recent Blog Posts feed
 * the Blog class manages settings for the blog and handles lists of articles.
 * the blog class works by reading all files from the blog article directory,
 * then getting the top five (sorted by date, which results in getting the newest five),
 * then parsing those 5 YAML files with a tool called FrontMatter.
    * FrontMatter separates the content of the files
    * into an array of key/value pairs and a main body, which is written in Markdown.
 * @cite: https://github.com/Modularr/YAML-FrontMatter/
 * @author Michael Born
*/
class Blog {
    //settings. Later, we may wish to put these in a YAML file.
    protected $articleDirectory;

    //commonly used variables
    protected $articleName;
    protected $articleList = array();
    protected $articleListData = array();
    protected $articleData = array();

    function __construct($articleDir) {
        $this->articleDirectory = $articleDir ? $articleDir : "/content/blog/articles/";
        $this->load_articles();
    }

    /**
     * call the funcs to scan the article directory for articles and clean the list of filenames.
     * @return array list of filenames in blog article directory.
    */
    private function load_articles() {
        $this->articleList = $this->clean_file_list($this->scan_articles());
        return $this->articleList;
    }

    /**
     * read blog articles as a list of filenames from the filesystem.
     * @param string sortDirection get by filename a-z or z-a
     * @return array list of filenames in blog article directory
    */
    private function scan_articles($sortDirection = SCANDIR_SORT_DESCENDING) {
        return scandir($_SERVER["DOCUMENT_ROOT"] . $this->articleDirectory,$sortDirection);
    }

    /**
     * remove linux ./ and ../ directories from the list of files in a directory.
     * Thanks to http://php.net/manual/en/function.scandir.php#107215
     * @param array of filenames
     * @return array of filenames, minus the "./" and "../" directories
    */
    private function clean_file_list(array $arr) {
        $removeDirs = array(".","..");
        return array_diff($arr, $removeDirs);
    }

    /**
     * Get n number of articles starting at offset.
     * @param int N number of articles to retrieve
     * @param int offset get articles starting at this 0-based number
     * @return array of objects, parsed from YAML files using FrontMatter.
    */
    public function get_n_articles($N = 3, $offset = 0) {
        $arr = $this->get_n_article_files($N, $offset);

        //Loop through the list of recent articles.
        //append each article as an array to the array of Twig template data
        foreach($arr as $article) {
            $data = $this->parse_article($this->get_full_filename($article));
            array_push($this->articleListData,$data);
        }
        return $this->articleListData;
    }

    /**
     * Get n number of filenames starting at offset
     * @param int N number of articles to retrieve
     * @param int offset get articles starting at this 0-based number
     * @return array of objects, parsed from YAML file using FrontMatter
    */
    private function get_n_article_files($N = 3, $offset = 0) {
        return array_slice($this->articleList,$offset,$N);
    }

    /**
     * parse a YAML file using FrontMatter
     * @param string the FULL filename of the .yaml file. Include '.yaml'.
     * @return object FrontMatter data.
    */
    public function parse_article($filename = "") {
        if ($filename == "") { $filename = $this->get_full_filename();}
        $this->articleData = new FrontMatter($filename);
        return $this->articleData->data;
    }

    /**
     * Get the full url, including the blog article directory setting, of the blog article identified by param url.
     * @param string url the permalink of the blog article. Does not include any path.
     * @return string a full path (used for file read or include) of the article file.
    */
    public function get_full_filename($url = "") {
        if ($url == "") {$url = $this->articleName;}
        return $_SERVER["DOCUMENT_ROOT"] . $this->articleDirectory . $this->get_filename_with_ext($url);
    }

    /**
     * Get the filename of the blog article identified by param url. Does not include path.
     * @param string url the permalink of the blog article. Should not include any path.
     * @return string $url plus file extension IF file extension not added. Else return $url.
    */
    private function get_filename_with_ext($url) {
        if (!$url) {$url = $this->articleName;}
        return (strpos($url,".yaml") === FALSE) ? $url . ".yaml" : $url;
    }

    /**
     * Get the full url, including the blog article directory setting, of the blog article identified by param url.
     * @param string url the permalink of the blog article. Does not include any path.
     * @return int the index of the article, OR false if not found.
    */
    public function exists() {
        return array_search($this->get_filename_with_ext($this->articleName),$this->articleList) > -1;
    }

    /**
     * set the name of the article to load or parse.
     * @param string the basename of the blog article file. Basename as in, no .yaml, no path.
     * @return string same thing;
    */
    public function set_article_name($articleName) {
        $this->articleName = $articleName;
        return $this->articleName;
    }
}

?>
