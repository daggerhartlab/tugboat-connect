<?php

namespace TugboatConnect\Service;

use Github\Client;

/**
 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/customize.md
 */
class Github implements RepoService {

	protected $owner;

	protected $repo;

	protected $user;

	private $pass;

	protected $prId;

	protected $prTitle;

	/**
	 * @var \Github\Client
	 */
	protected $client;

	/**
	 * @param string $owner - organization or user name
	 * @param string $repo - repository name
	 * @param string $user - api user name
	 * @param string $pass - api token
	 * @param int $pr_id - pull request ID
	 * @param string $pr_title - pull request title
	 */
	public function __construct( $owner, $repo, $user, $pass, $pr_id, $pr_title ) {
		$this->owner = $owner;
		$this->repo = $repo;
		$this->user = $user;
		$this->pass = $pass;
		$this->prId = $pr_id;
		$this->prTitle;
	}

	/**
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md
	 *
	 * @return \Github\Client
	 */
	public function client() {
		if ( ! $this->client ) {
			$this->client = Client::createWithHttpClient( new \Http\Adapter\Guzzle6\Client() );
			$this->client->authenticate( $this->user, $this->pass, Client::AUTH_HTTP_PASSWORD );
		}

		return $this->client;
	}

	/**
	 * Create the likely pull request url.
	 *
	 * @return string
	 */
	public function getPrUrl() {
		return "https://github.com/{$this->owner}/{$this->repo}/pull/{$this->prId}";
	}

	/**
	 * @return string
	 */
	public function getPrTitle() {
		return $this->prTitle;
	}

	/**
	 * @return int|string
	 */
	public function getPrId() {
		return $this->prId;
	}

	/**
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/pull_requests.md
	 *
	 * @param array $replacements Optional array of string replacements
	 *
	 * @return string
	 */
	public function getPrBody( $replacements = [] ) {
		$response = $this->client()
		                 ->api( 'pull_request' )
		                 ->show( $this->owner, $this->repo, $this->prId );

		$body = strtr( $response['body'], $replacements );

		return $body;
	}

	/**
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/issue/comments.md
	 *
	 * @param string $body - Comment body
	 *
	 * @throws \Github\Exception\MissingArgumentException
	 */
	public function addComment( $body ) {
		/** @var \Github\Api\Issue\Comments $comments */
		$comments = $this->client()->api( 'issue' )->comments();
		$comments->create( $this->owner, $this->repo, $this->prId, [
			'body' => $body,
		] );
	}

	/**
	 * Update the PR body text.
	 *
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/issues.md#close-an-issue
	 * @link https://developer.github.com/v3/issues/#update-an-issue
	 *
	 * @param string $body
	 */
	public function updateIssueBody( $body ) {
		$this->client()
		     ->api( 'issue' )
		     ->update( $this->owner, $this->repo, $this->prId, [
			     'body' => $body,
		     ] );
	}

}
