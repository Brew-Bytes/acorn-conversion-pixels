<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class MetaPixelModule extends AbstractModule
{
    public function handle(): void
    {
        if (! $this->enabled() || empty($this->id())) {
            return;
        }

        $this->action('wp_head', 'printHead', 5);
        $this->action('wp_body_open', 'printNoscript', 5);
    }

    public function printHead(): void
    {
        $id = $this->id();
        $idJs = esc_js($id);

        $loader = '!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){'
            .'n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};'
            ."if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];"
            .'t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];'
            ."s.parentNode.insertBefore(t,s)}(window,document,'script',"
            ."'https://connect.facebook.net/en_US/fbevents.js');"
            ."fbq('init','{$idJs}');fbq('track','PageView');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- Meta Pixel (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- Meta Pixel -->\n<script>{$loader}</script>\n";
    }

    public function printNoscript(): void
    {
        // Skip the noscript fallback when consent is required — it'd fire
        // unconditionally and bypass consent for non-JS visitors.
        if ($this->consentRequired()) {
            return;
        }

        $id = esc_attr($this->id());
        echo '<noscript><img height="1" width="1" style="display:none" '
            .'src="https://www.facebook.com/tr?id='.$id.'&ev=PageView&noscript=1"/></noscript>'."\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
