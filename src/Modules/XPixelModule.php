<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class XPixelModule extends AbstractModule
{
    public function handle(): void
    {
        if (! $this->enabled() || empty($this->id())) {
            return;
        }

        $this->action('wp_head', 'printHead', 5);
    }

    public function printHead(): void
    {
        $id = $this->id();
        $idJs = esc_js($id);

        $loader = '!function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){'
            .'s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);},'
            ."s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,"
            ."u.src='https://static.ads-twitter.com/uwt.js',"
            .'a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))'
            ."}(window,document,'script');twq('config','{$idJs}');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- X Pixel (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- X Pixel -->\n<script>{$loader}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
