<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Modules;

class LinkedinInsightModule extends AbstractModule
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

        $loader = "_linkedin_partner_id='{$idJs}';"
            .'window._linkedin_data_partner_ids=window._linkedin_data_partner_ids||[];'
            ."window._linkedin_data_partner_ids.push('{$idJs}');"
            .'(function(l){if(!l){window.lintrk=function(a,b){window.lintrk.q.push([a,b])};'
            .'window.lintrk.q=[]}var s=document.getElementsByTagName("script")[0];'
            .'var b=document.createElement("script");b.type="text/javascript";b.async=true;'
            .'b.src="https://snap.licdn.com/li.lms-analytics/insight.min.js";'
            .'s.parentNode.insertBefore(b,s);})(window.lintrk);';

        if ($this->consentRequired()) {
            $event = esc_js($this->consentEvent());
            $deferred = "window.addEventListener('{$event}', function(){{$loader}}, { once: true });";
            echo "<!-- LinkedIn Insight (deferred) -->\n<script>{$deferred}</script>\n";
        } else {
            echo "<!-- LinkedIn Insight -->\n<script>{$loader}</script>\n";
        }

        // The conversion router is safe to attach unconditionally — its `lintrk`
        // typeof check no-ops cleanly until the SDK loads (whether immediately
        // or after consent fires).
        $this->printConversionRouter();
    }

    protected function printConversionRouter(): void
    {
        $conversions = (array) $this->config->get('conversions', []);
        if ($conversions === []) {
            return;
        }

        $mapJs = (string) wp_json_encode($conversions);

        $router = "(function(){var map={$mapJs};window.addEventListener('analytics:event',"
            .'function(e){var d=e&&e.detail||{};if(!d.name||!map[d.name])return;'
            ."if(typeof window.lintrk==='function'){window.lintrk('track',{conversion_id:map[d.name]});}});})();";

        echo "<script>{$router}</script>\n";
    }

    protected function id(): string
    {
        return (string) $this->config->get('id', '');
    }
}
