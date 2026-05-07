<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class PinterestTagModule extends AbstractModule
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

        $loader = '!function(e){if(!window.pintrk){window.pintrk=function(){'
            .'window.pintrk.queue.push(Array.prototype.slice.call(arguments))};'
            .'var n=window.pintrk;n.queue=[];n.version="3.0";'
            .'var t=document.createElement("script");t.async=!0;t.src=e;'
            .'var r=document.getElementsByTagName("script")[0];'
            .'r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");'
            ."pintrk('load','{$idJs}');pintrk('page');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- Pinterest Tag (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- Pinterest Tag -->\n<script>{$loader}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
