<?php
namespace Grav\Plugin;

use Grav\Common\Data;
use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class PygmentsPlugin extends Plugin
{
    private static $pattern = '#<pre><code>---(?<head>.*?)\n---(?<body>.*?)</code></pre>#s';
    private static $pygmentize = 'plugins://pygments/pygmentize.py';
    
    public static function getSubscribedEvents()
    {
        return [
            'onPageContentProcessed' => ['onPageContentProcessed', 0],
        ];
    }

    public function onPageContentProcessed (Event $event)
    {
        $page = $event['page'];
        $content = $page->getRawContent();
        
        if (! preg_match_all(self::$pattern, $content, $matches, PREG_SET_ORDER))
            return;

        $yaml = new Parser();

        foreach ($matches as $match)
        {
            $full = $match[0];

            /* Get and parse headers */
            try
            {
                $head = $yaml->parse(trim($match['head']));
            }
            catch (ParseException $e)
            {
                continue;
            }

            /* Get and decode body. This isn't a problem since we stay between
               <code> tags. */
            $body = html_entity_decode(trim($match['body']));

	    if (isset($head['file']))
	    {
		$file = $this->grav['locator']->findResource($head['file']);
		if ($file)
		    $body = file_get_contents($file);
		else
		    $body = "File '{$head['file']}' does not exist";
	    }
	    
            /* Find the python pygmentize script. */
            $script = $this->grav['locator']->findResource(self::$pygmentize);
            $script = escapeshellarg($script);

	    /* Find the language if given. */
            if (isset($head['language']))
                $language = '--language ' . escapeshellarg($head['language']);
            else
                $language = '';

            $body = escapeshellarg($body);

	    /* Find the highlights if given. */
            if (isset($head['highlights']))
                $highlights = '--highlights ' . escapeshellarg(implode(',', $head['highlights']));
            else
                $highlights = '';

	    if (isset($head['title']))
		$title = '<pre class="code-title"><code>' . $head['title'] . '</code></pre>';
	    else
		$title = '';
	    
	    /* Execute the command. */
            $cmd = "python $script $language $highlights $body 2>&1";            
            $body = trim(shell_exec($cmd));
            
            /* Update content. */
            $content = str_replace($full, $title . '<pre class="code-body"><code>' . $body . '</code></pre>', $content);
        }

        $page->setRawContent($content);
    }
}
