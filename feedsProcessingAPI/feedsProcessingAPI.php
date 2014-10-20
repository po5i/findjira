<?php

/**
 *  Zebra PHP Framework
 *
 *  RSS/ATOM Feeds Processing API
 *
 *  A PHP class providing functionality for fetching RSS/ATOM feeds and displaying them using templates.
 *  It uses SimplePie {@link http://simplepie.org} to fetch the feeds and the XTemplate templating engine
 *  {@link http://www.phpxtemplate.org} for output. Thanks to SimplePie it can autodetect and correctly
 *  interpret RSS 0.91, RSS 1.0, RSS 2.0 and both ATOM 0.3 and ATOM 1.0
 *
 *  Read the manual for more information and check out the example to see it at work!
 *
 *  This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivs 2.5 License.
 *  To view a copy of this license, visit {@link http://creativecommons.org/licenses/by-nc-nd/2.5/} or send a letter to
 *  Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @version    1.0 (last revision: February 05, 2007)
 *  @copyright  (c) 2007 Stefan Gabos
 *  @package    feedsProcessingAPI
 *  @example    example.php
 */

error_reporting(E_ALL);


class feedsProcessingAPI
{

    /**
     *  Pointer to the SimplePie object through which you can access SimplePie's properties
     *
     *  <i>Note that this class, by default, only addresses very few properties of SimplePie:
     *  <b>cache_location</b>, <b>cache_max_minutes</b> and <b>set_timeout</b>
     *
     *  If you want to set more properties of SimplePie you can do it through this property
     *
     *  The documentation for SimplePie can be found at {@link http://simplepie.org/docs/reference/}</i>
     */
    var $simplePie;
    
    /**
     *  Number of seconds that the script should keep trying to fetch a feed.
     *
     *  Default is 20
     *
     *  @var    integer
     */
    var $timeout;
    
    /**
     *  Location of the cache files
     *
     *  <i>Note that this folder must exist and be writable!</i>
     *
     *  Default is "./cache/"
     *
     *  @var string
     */
    var $cacheLocation;

    /**
     *  The number of minutes after a cache file expires and feed will be read again
     *
     *  Default is 30
     *
     *  @var integer
     */
    var $cacheMinutes;
    
    /**
     *  Format in which dates should be presented
     *
     *  Any combination allowed by PHP's date() function can be used
     *
     *  Default is M d Y H:i
     *
     *  @var string
     */
    var $dateFormat;
    
    /**
     *  Default template folder to use
     *
     *  Note that only the folder of the template you wish to use needs to be specified. Inside the folder
     *  you <b>must</b> have the <b>template.xtpl</b> file which will be automatically used
     *
     *  Default is "default"
     *
     *  @var    string
     */
    var $template;

    /**
     *  In case of an error read this property's value to find out what went wrong
     *
     *  Possible error values are:
     *
     *      - 1:  unable to fetch feed
     *      - 2:  caching folder is not writable
     *      - 3:  caching folder does not exists
     *      - 4:  error loading template
     *      - 5:  function specified as callback function does not exists
     *
     *  Default is 0
     *
     *  @var integer
     */
    var $error;

    /**
     *  Holds global information about the fetched feed and can be used in a template file
     *
     *  It's entries are:
     *
     *      -   copyright
     *      -   description
     *      -   language
     *      -   link
     *      -   title
     *
     *  Reffer to them in the template file as <i>{feed.<entryname>}</i>
     *
     *  @var array
     */
    var $feed;

    /**
     *  Holds information about particular items (entries) in the fetched feed and can be used in a template file
     *
     *  The script tries to automatically feed each of it's entries.
     *
     *  It's entries are:
     *
     *      -   author (which is an array and therefore only it's sub-items are available):
     *
     *      = email<br />
     *      = link<br />
     *      = name<br />
     *
     *      -   category
     *      -   date
     *      -   description
     *      -   id
     *      -   link
     *      -   title
     *
     *  Reffer to them in the template file as <i>{item.<entryname>}</i> and <i>{item.author.<entryname>} (in case of authors related
     *  information)</i>
     *
     *  @var array
     */
    var $autodiscover;

    /**
     *  Holds information about ALL AVAILABLE entries in particular items (entries) in the fetched feed and can be used in a template file
     *
     *  The content of this property may vary from feed to feed.
     *
     *  It is reccomended that you always use the information provided by the {@link autodiscover} property instead.
     *
     *  @var array
     */
    var $items;
    
    /**
     *  Holds information about assigned callback functions
     *
     *  @access private
     *
     *  @var array
     */
    var $callbackFunctions;
    
    /**
     *  Initialized the API
     *
     *  @return void
     */
    function feedsProcessingAPI()
    {
    
        // get the absolute path of the class. any further includes rely on this
        // and (on a windows machine) replace \ with /
        $this->absolutePath = preg_replace("/\\\/", "/", dirname(__FILE__));

        // remove $_SERVER["DOCUMENT_ROOT"] from the path
        // this path is to be used from within HTML as it is a relative path
        $this->relativePath = preg_replace("/".preg_replace("/\//", "\/", $_SERVER["DOCUMENT_ROOT"])."/i", "", $this->absolutePath);
        
        // Sets default values of the class' properties
        // We need to do it this way for the variables to have default values PHP 4
        $this->timeout = 20;
        $this->cacheLocation = $this->absolutePath . "/cache/";
        $this->cacheMinutes = 30;
        $this->dateFormat = "M d Y H:i";
        $this->template = "default";
        $this->feed = array();
        $this->items = array();
        $this->callbackFunctions = array();

        // load simplePie
        require_once $this->absolutePath . "/includes/simplepie.php";

        // instantiate a new simplePie object
        $this->simplePie = new SimplePie();

    }

    /**
     *  Fetches RSS/ATOM feed from a given URL
     *
     *  @param  string  $url    URL from where to fetch the feed
     *
     *                          <i>Note that if feed was read previously and less than {@link cacheMinutes} minutes passed since,
     *                          the content of the feed will be taken from the cache!</i>
     *
     *  @return boolean         Returns TRUE in success or FALSE otherwise.
     *
     *                          <i>In case of error, read the {@link error} property to find out what went wrong</i>
     */
    function get($url)
    {
    
        // if caching location exists
        if (file_exists($this->cacheLocation)) {
        
            // if caching location is writable
            if (is_writable($this->cacheLocation)) {

                // set SimplePie's fetching time out
                $this->simplePie->set_timeout($this->timeout);

                // set the location of the cache folder
                $this->simplePie->cache_location($this->cacheLocation);

                // set how long (in minutes) a cache file should be considered valid
                $this->simplePie->cache_max_minutes($this->cacheMinutes);

                // set the URL from where to fetch the feed
                $this->simplePie->feed_url($url);

                // if fetching the feed went ok
                if (@$this->simplePie->init()) {
                
                    // assign to the "feed" property some global attributes of the feed
                    // the feed's copyright
                    $this->feed["copyright"] = $this->simplePie->get_feed_copyright();

                    // the feed's description
                    $this->feed["description"] = $this->simplePie->get_feed_description();

                    // the feed's language
                    $this->feed["language"] = $this->simplePie->get_feed_language();

                    // the feed's title
                    $this->feed["title"] = $this->simplePie->get_feed_title();

                    // the feed's link
                    $this->feed["link"] = $this->simplePie->get_feed_link();

                    // ...and get all items in the feed and assign them to the "items" property
                    $this->items = $this->simplePie->get_items();

                // if fetching of the feed was erroneous
                } else {

                    // assign the error level
                    $this->error = 1;
                    
                    // and return FALSE
                    return false;

                }

            // if caching location is not writable
            } else {

                // assign the error level
                $this->error = 2;
                
                // and return FALSE
                return false;

            }

        // if caching location doesn't exists
        } else {
        
            // assign the error level
            $this->error = 3;
            
            // and return FALSE
            return false;

        }
        
        return true;

    }

    /**
     *  Sets a callback function to be run on each value of a specific item entry before displaying it
     *
     *  <i>The user defined function takes as parameter the value of the item entry</i>
     *
     *  <code>
     *      $feed->setCallBackFunction("pubdate")
     *  </code>
     *
     *  @param  string  $depth                  The depth (as entries in feeds as arrays) at which the desired
     *                                          entry can be found <i>Levels are marked with dots (.)!</i>
     *
     *                                          i.e. suppose you have an entry like ["dates"]["published"] than
     *                                          you would reffer to it as dates.published
     *
     *  @param  string  $callbackFunctionName   name of the user defined function that will be run on the specified
     *                                          entry
     *
     *
     *  @return boolean TRUE on success or FALSE otherwise
     */
    function setCallBackFunction($depth, $callbackFunctionName)
    {
    
        // if given function exists
        if (function_exists($callbackFunctionName)) {
        
            // explode by dots
            $depth = explode(".", $depth);
            
            // initiate the string that will eventaully contain an eval()-uable string
            $evaluableString = "";
            
            // iterate trough the items in the array
            foreach ($depth as $level) {

                // construct the string to be evaluated
                $evaluableString .= "[\"" . $level . "\"]";

            }
            
            // save it
            $this->callbackFunctions[$evaluableString] = $callbackFunctionName;
            
            return true;
            
       } else {

            // assign the error level
            $this->error = 5;
            
            // and return FALSE
            return false;

       }
    
    }
    
    /**
     *  Parsed the output
     *
     *  @param  integer $items  How many of the items of the feed should be showed.
     *
     *                          <i>Note that the items in a feed are sorted by date, descending</i>
     *
     *                          Default is 0 meaning that all entries in feed should be shown
     *
     *  @param  boolean $toVar  Should the generated output be sent to the screen or just returned
     *
     *                          Default is FALSE meaning that the output should be printed on the screen
     *
     *  @return boolean         TRUE on success or FALSE on failure
     */
    function parse($items = 0, $toVar = false)
    {

        // include the XTemplate templating engine
        require_once $this->absolutePath . "/includes/class.xtemplate.php";
        
        // check if specified template file exists
        if (file_exists($this->absolutePath . "/templates/" . $this->template . "/template.xtpl")) {
        
            // instantiate a new XTemplate object with the specified template
            $xtpl = new XTemplate($this->absolutePath . "/templates/" . $this->template . "/template.xtpl");

            // assign the "feed" property
            $xtpl->assign("feed", $this->feed);

            $counter = 0;

            // iterate though all the items in the feed
            foreach ($this->items as $item) {

                // create the autodiscover array entry holding commonly found entries
                $item->autodiscover = array();

                // determinte the id of the item
                $item->autodiscover["id"] = $item->get_id();

                // determinte the category of the item
                $item->autodiscover["category"] = $item->get_category();

                // determinte the title of the item
                $item->autodiscover["title"] = $item->get_title();

                // determine the content of the item
                $item->autodiscover["description"] = $item->get_description();

                // determinte the link to the item
                $item->autodiscover["link"] = $item->get_link();

                // determinte the posting date of the item
                $item->autodiscover["date"] = $item->get_date($this->dateFormat);
                
                // get author details (as an object)
                $author = $item->get_author();
                
                if (is_object($author)) {

                    // save author related information as an array
                    $item->autodiscover["author"] = array("email" => $author->get_email(), "link" => $author->get_link(), "name" => $author->get_name()) ;
                
                }

                // if all items are to be displayed
                // or we've not already displayed the maximum numbers of items to be displayed
                if ($items == 0 || ($items != 0 && ++$counter <= $items)) {
                
                    // iterate through the set callback functions
                    foreach ($this->callbackFunctions as $depth=>$functionName) {

                        // apply functions
                        eval("\$item->data" . $depth . "=" . $functionName . "(\$item->data" . $depth . ");");

                    }
                    
                    // assign the autodiscovered elements
                    $xtpl->assign("autodiscover", $item->autodiscover);

                    // assign the data attribute of the "item" property
                    $xtpl->assign("item", $item->data);

                    // parse item
                    $xtpl->parse("main.item");

                }

            }

            // parse the template file
            $xtpl->parse("main");

            // if parsed result is to be returned instead of being sent to the screen
            if ($toVar) {

                return $xtpl->text("main");

            // if parsed result is to be outputted to the screen
            } else {

                $xtpl->out("main");

            }

        // if template could not be loaded
        } else {

            // assign the error level
            $this->error = 4;
            
            // and return false
            return false;

        }

    }
    
    
      /**
     *  Parsed the output as an assoc array, with feed info and feed items // modified by kuroizero
     *
     *  @param  integer $items  How many of the items of the feed should be showed.
     *
     *                          <i>Note that the items in a feed are sorted by date, descending</i>
     *
     *                          Default is 0 meaning that all entries in feed should be shown
     *
     *  @return boolean         TRUE on success or FALSE on failure
     */
    function parse_assoc_array($items = 0)
    {

		$out = array();
             
        $out["feed_info"] = $this->feed;
        

            $counter = 0;

            // iterate though all the items in the feed
            foreach ($this->items as $item) {

                // create the autodiscover array entry holding commonly found entries
                $item->autodiscover = array();

                // determinte the id of the item
                $item->autodiscover["id"] = $item->get_id();

                // determinte the category of the item
                $item->autodiscover["category"] = $item->get_category();

                // determinte the title of the item
                $item->autodiscover["title"] = $item->get_title();

                // determine the content of the item
                $item->autodiscover["description"] = $item->get_description();

                // determinte the link to the item
                $item->autodiscover["link"] = $item->get_link();

                // determinte the posting date of the item
                $item->autodiscover["date"] = $item->get_date($this->dateFormat);
                
                // get author details (as an object)
                $author = $item->get_author();
                
                if (is_object($author)) {

                    // save author related information as an array
                    $item->autodiscover["author"] = array("email" => $author->get_email(), "link" => $author->get_link(), "name" => $author->get_name()) ;
                
                }

                // if all items are to be displayed
                // or we've not already displayed the maximum numbers of items to be displayed
                if ($items == 0 || ($items != 0 && ++$counter <= $items)) {
                
                    // iterate through the set callback functions
                    foreach ($this->callbackFunctions as $depth=>$functionName) {

                        // apply functions
                        eval("\$item->data" . $depth . "=" . $functionName . "(\$item->data" . $depth . ");");

                    }                    
                
                    $out ["feed_items"][] = $item->autodiscover;

                }

            }

            return $out;

    }

    
    
}

?>
