<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class TiktokPixelModule extends AbstractModule
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

        $loader = '!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];'
            .'ttq.methods=["page","track","identify","instances","debug","on","off","once",'
            .'"ready","alias","group","enableCookie","disableCookie"];'
            .'ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};'
            .'for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);'
            .'ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)'
            .'ttq.setAndDefer(e,ttq.methods[n]);return e};'
            .'ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";'
            .'ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;'
            .'ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");'
            .'o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;'
            .'var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};'
            ."ttq.load('{$idJs}');ttq.page();}(window,document,'ttq');";

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- TikTok Pixel (deferred) -->\n<script>{$deferred}</script>\n";

            return;
        }

        echo "<!-- TikTok Pixel -->\n<script>{$loader}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
