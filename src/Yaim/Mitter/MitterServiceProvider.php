<?php namespace Yaim\Mitter;

use Illuminate\Support\ServiceProvider;

class MitterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	* Bootstrap the application events.
	*
	* @return void
	*/
	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/resources/views', 'mitter');

		$this->publishes([
			__DIR__.'/resources/assets' => public_path('packages/yaim/mitter'),
		], 'public');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		require_once 'functions.php';
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
