<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class SnapPixelModule extends AbstractModule
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

        $loader = '(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){'
            .'a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};'
            ."a.queue=[];var s='script';r=t.createElement(s);r.async=!0;r.src=n;"
            .'var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u);'
            ."})(window,document,'https://sc-static.net/scevent.min.js');"
            ."snaptr('init','{$idJs}');snaptr('track','PAGE_VIEW');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- Snap Pixel (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- Snap Pixel -->\n<script>{$loader}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
