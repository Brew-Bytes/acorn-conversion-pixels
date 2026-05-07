<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class RedditPixelModule extends AbstractModule
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

        $loader = '!function(w,d){if(!w.rdt){var p=w.rdt=function(){'
            .'p.sendEvent?p.sendEvent.apply(p,arguments):p.callQueue.push(arguments)};'
            .'p.callQueue=[];var t=d.createElement("script");'
            .'t.src="https://www.redditstatic.com/ads/pixel.js";t.async=!0;'
            .'var s=d.getElementsByTagName("script")[0];'
            .'s.parentNode.insertBefore(t,s)}}(window,document);'
            ."rdt('init','{$idJs}');rdt('track','PageVisit');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- Reddit Pixel (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- Reddit Pixel -->\n<script>{$loader}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
