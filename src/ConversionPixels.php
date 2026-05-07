<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels;

use BrewAndBytes\AcornConversionPixels\Concerns\HasCollection;
use Illuminate\Support\Collection;
use Roots\Acorn\Application;

/**
 * @phpstan-consistent-constructor
 */
class ConversionPixels
{
    use HasCollection;

    protected Application $app;

    protected Collection $config;

    /**
     * @var array<int, class-string<Modules\AbstractModule>>
     */
    protected array $modules = [
        Modules\MetaPixelModule::class,
        Modules\LinkedinInsightModule::class,
        Modules\TiktokPixelModule::class,
        Modules\PinterestTagModule::class,
        Modules\XPixelModule::class,
        Modules\RedditPixelModule::class,
        Modules\SnapPixelModule::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this
            ->collect($this->app->make('config')->get('conversion-pixels', []))
            ->map(fn ($value) => is_array($value) ? $this->collect($value) : $value);

        add_action('init', fn () => $this->bootModules(), 99);

        if ($this->shouldRun()) {
            add_action('wp_head', fn () => $this->printBridge(), 3);
        }
    }

    public function bootModules(): void
    {
        $this->collect($this->modules)->each(
            fn (string $module) => $module::make($this->app, $this->config)
        );
    }

    public static function make(Application $app): self
    {
        return new static($app);
    }

    protected function shouldRun(): bool
    {
        if (! (bool) $this->config->get('enabled', false)) {
            return false;
        }

        $environments = $this->config->get('environments', ['production']);
        if ($environments instanceof Collection) {
            $environments = $environments->all();
        }
        $environments = (array) $environments;
        if ($environments !== [] && ! in_array($this->currentEnvironment(), $environments, true)) {
            return false;
        }

        if ((bool) $this->config->get('exclude_logged_in', true) && is_user_logged_in()) {
            return false;
        }

        if ((bool) $this->config->get('respect_dnt', true) && isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1') {
            return false;
        }

        return true;
    }

    protected function currentEnvironment(): string
    {
        if (defined('WP_ENV')) {
            return (string) constant('WP_ENV');
        }

        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        return 'production';
    }

    /**
     * Bridge that listens for `analytics:event` CustomEvents (defined by acorn-analytics)
     * and fans them out to whichever pixel APIs are loaded on the page. Has its own
     * consent gate that may be stricter than acorn-analytics's: pixels almost always
     * require explicit marketing-bucket opt-in.
     */
    protected function printBridge(): void
    {
        $consent = (array) $this->config->get('consent', []);
        $consentRequired = ! empty($consent['required']);
        $consentEvent = (string) ($consent['event'] ?? 'cookie-consent:granted');

        $script = <<<'JS'
(function(){
  var queue = [];
  var ready = !__CONSENT_REQUIRED__;

  function dispatch(name, data){
    if (typeof window.fbq === 'function') {
      window.fbq('trackCustom', name, data);
    }
    if (window.ttq && typeof window.ttq.track === 'function') {
      window.ttq.track(name, data);
    }
    if (typeof window.pintrk === 'function') {
      window.pintrk('track', name, data);
    }
    if (typeof window.twq === 'function') {
      window.twq('event', name, data);
    }
    if (typeof window.rdt === 'function') {
      window.rdt('track', name, data);
    }
    if (typeof window.snaptr === 'function') {
      window.snaptr('track', name, data);
    }
    // LinkedIn's Insight Tag has no generic event API — conversions are configured
    // by ID in LinkedIn Campaign Manager and fired via window.lintrk('track', { conversion_id }).
    // Use the conversionId mapping in config to route events to specific conversion IDs.
  }

  function flush(){
    while (queue.length) {
      var ev = queue.shift();
      dispatch(ev.name, ev.data);
    }
  }

  window.addEventListener('analytics:event', function(e){
    var d = e && e.detail || {};
    if (!d.name) return;
    var data = Object.assign({}, d);
    delete data.name;
    if (!ready) { queue.push({ name: d.name, data: data }); return; }
    dispatch(d.name, data);
  });

  if (!ready) {
    window.addEventListener('__CONSENT_EVENT__', function(){
      ready = true;
      flush();
    }, { once: true });
  }
})();
JS;

        $script = str_replace(
            ['__CONSENT_REQUIRED__', '__CONSENT_EVENT__'],
            [$consentRequired ? 'true' : 'false', esc_js($consentEvent)],
            $script
        );

        echo "<script id=\"acorn-conversion-pixels-bridge\">\n{$script}\n</script>\n";
    }
}
