<?php

namespace TugboatConnect\Environment;

use TugboatConnect\Service\Github;
use TugboatConnect\Service\Jira;

class Tugboat {

	protected $env = [];

	public function __construct( $env = [] ) {
		$this->env = $env;
	}

	public function has( $key ) {
		return isset( $this->env[ $key ] );
	}

	public function get( $key, $default = null ) {
		if ( $this->has( $key ) ) {
			return $this->env[ $key ];
		}

		return $default;
	}

	/**
	 * Get the tugboat preview url.
	 *
	 * @return string
	 */
	public function previewUrl() {
		$url = rtrim( $this->get( 'TUGBOAT_SERVICE_URL' ), '/' );

		if ( $this->has( 'TUGBOAT_BASIC_AUTH_CREDS' ) ) {
			$url = str_replace('https://', "https://{$this->get( 'TUGBOAT_BASIC_AUTH_CREDS' )}@", $url);
		}

		return $url;
	}

	/**
	 * Get the tugboat preview id.
	 *
	 * @return string
	 */
	public function previewId() {
		return $this->get( 'TUGBOAT_PREVIEW_ID' );
	}

	/**
	 * @return \TugboatConnect\Service\Github
	 */
	public function createGithub() {
		return new Github(
			$this->get( 'TUGBOAT_GITHUB_OWNER' ),
			$this->get( 'TUGBOAT_GITHUB_REPO' ),
			$this->get( 'GITHUB_CI_BOT_USERNAME' ),
			$this->get( 'GITHUB_CI_BOT_PASSWORD' ),
			$this->get( 'TUGBOAT_GITHUB_PR' ),
			$this->get( 'TUGBOAT_GITHUB_TITLE')
		);
	}

	/**
	 * @return \TugboatConnect\Service\Jira
	 */
	public function createJira() {
		return new Jira(
			$this->get( 'JIRA_API_HOST' ),
			$this->get( 'JIRA_API_USERNAME' ),
			$this->get( 'JIRA_API_PASSWORD' )
		);
	}
}
