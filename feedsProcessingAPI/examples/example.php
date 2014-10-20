<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>
        <meta http-equiv="content-type" content="text/html; charset=windows-1250">
        <meta name="generator" content="PSPad editor, www.pspad.com">
        <title>RSS/ATOM Feeds Processing API</title>
    </head>

    <body>

    <form method="get">
    
        Enter a RSS/ATOM feed URL here:
        <input type="text" name="feed" value="<?=isset($_GET["feed"]) ? $_GET["feed"] : ""?>" />
        <input type="submit" value="fetch!" />

        <br />
        for an instant start, try this ones:<br />
        <a href="?feed=http://www.nytimes.com/services/xml/rss/nyt/Technology.xml">New York Times - Technology</a> |
        <a href="?feed=http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/technology/rss.xml">BBC - Technology</a> |
        <a href="?feed=http://stefangabos.blogspot.com/feeds/posts/default">My blog</a> |
        <br /><br />

    <?php

        require "../api.feedsprocessing.php";

        // instantiate the API
        $feed = new feedsProcessingAPI();

        // timeout after trying to fetch a feed for longer than 10 seconds
        $feed->timeout = 10;

        // if anything submitted
        if (isset($_GET["feed"])) {

            // fetch the feed
            if ($feed->get($_GET["feed"])) {

                // output using default template (and only show the latest 5)
                // and display a message in case of error
                $feed->parse(5);

            } else {

                print_r("Error: " . $feed->error . " (see the manual about what each error number means)");

            }

        }


    ?>

    </form>

    </body>
</html>
