<?php

namespace RRZE\RSVPNETWORK;

defined('ABSPATH') || exit;


/**
 * [Main description]
 */
class Main
{

	protected $pluginFile;
	private $settings = '';


	/**
	 * [__construct description]
	 */
	public function __construct($pluginFile)
	{
		$this->pluginFile = $pluginFile;
	}

	public function onLoaded()
	{
		// Tracking
		$tracking = new NetworkTracking;
		$tracking->onLoaded();
	}
}
