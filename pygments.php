<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class PygmentsPlugin extends Plugin
{
    private static $pattern = '#<pre><code>---(?<head>.*?)\n---(?<body>.*?)</code></pre>#s';

    public static function getSubscribedEvents()
    {
        return [
            'onPageContentProcessed' => ['onPageContentProcessed', 0],
        ];
    }

    public function onPageContentProcessed(Event $event)
    {
        $page = $event['page'];
        $content = $page->getRawContent();

        if (!preg_match_all(self::$pattern, $content, $matches, PREG_SET_ORDER)) {
            return;
        }

        $yaml = new Parser();

        foreach ($matches as $match) {
            $full = $match[0];

            /* Get and parse headers */
            try {
                $head = $yaml->parse(trim($match['head']));
            } catch (ParseException $e) {
                continue;
            }

            /* Get and decode body. This isn't a problem since we stay between
               <code> tags. */
            $body = html_entity_decode(trim($match['body']));

            if (isset($head['file'])) {
                try {
                    $file = $this->grav['locator']->findResource($head['file']);
                } catch (\InvalidArgumentException $e) {
                    if (substr($head['file'], 0, 1) == DIRECTORY_SEPARATOR) {
                        $file = $head['file'];
                    } else {
                        $file = $page->path().DIRECTORY_SEPARATOR.$head['file'];
                    }
                }

                if (is_file($file)) {
                    $body = file_get_contents($file);
                } else {
                    $body = "File '{$head['file']}' does not exist";
                }
            }

            /* Craft command line */

            // Send body via command line
            $cmd = 'echo '.escapeshellarg($body);

            // Base command
            $cmd .= ' | '.$this->config->get('plugins.pygments.pygmentize.command', 'pygmentize');

            // Find the language if given
            if (isset($head['language'])) {
                if ($head['language'] == 'guess') {
                    $cmd .= ' -g';
                } else {
                    $cmd .= ' -l '.escapeshellarg($head['language']);
                }
            }

            // Guess or not (depending on the config) otherwise
            else {
                if ($this->config->get('plugins.pygments.guess_by_default', true)) {
                    $cmd .= ' -g';
                } else {
                    $cmd .= ' -l text';
                }
            }

            // Set formatter to HTML
            $cmd .= ' -f html';

            // Avoid wrapping
            $cmd .= ' -O nowrap';

            // Don't use CSS classes but inline style
            if ($this->config->get('plugins.pygments.pygmentize.formatter.noclasses', true)) {
                $cmd .= ' -O noclasses';
            }

            // Highlight lines if asked
            if (isset($head['highlights'])) {
                $cmd .= ' -P hl_lines='.escapeshellarg(implode(' ', $head['highlights']));
            }

            // Set style
            $cmd .= ' -P style='.$this->config->get('plugins.pygments.pygmentize.style', 'default');

            /* Execute the command */
            $body = trim(shell_exec($cmd));

            if (isset($head['title'])) {
                $title = '<pre class="code-title"><code>'.$head['title'].'</code></pre>';
            } else {
                $title = '';
            }

            /* Update content. */
            $content = str_replace($full, $title.'<pre class="code-body"><code>'.$body.'</code></pre>', $content);
        }

        $page->setRawContent($content);
    }
}
