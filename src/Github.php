<?php

namespace TugboatConnect;

use Github\Client;

/**
 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/customize.md
 */
class Github {

	private $owner;

	private $repo;

	private $user;

	private $pass;

	/**
	 * @var \Github\Client
	 */
	protected $client;

	/**
	 * @param string $owner  - owneranization or user name
	 * @param string $repo - repository name
	 * @param string $user - api user name
	 * @param string $pass - api token
	 */
	public function __construct($owner, $repo, $user, $pass) {
		$this->owner  = $owner;
		$this->repo = $repo;
		$this->user = $user;
		$this->pass = $pass;
	}

	/**
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md
	 *
	 * @return \Github\Client
	 */
	public function client() {
		if (!$this->client) {
			$this->client = Client::createWithHttpClient(new \Http\Adapter\Guzzle6\Client());
			$this->client->authenticate($this->user, $this->pass, Client::AUTH_HTTP_PASSWORD);	
		}

		return $this->client;
	}

	/**
	 * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/pull_requests.md
	 *
	 * @param string $prId - Pull request ID
	 * @return string
	 */
	public function getPrBody($prId) {
		$response = $this->client()->api('pull_request')->show($this->owner, $this->repo, $prId);
		
		return $response['body'];
	}

  /**
   * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/issue/comments.md
   *
   * @param string $prId - Pull request ID
   * @param string $body - Comment body
   *
   * @throws \Github\Exception\MissingArgumentException
   */
	public function addComment($prId, $body) {
	  /** @var \Github\Api\Issue\Comments $comments */
		$comments = $this->client()->api('issue')->comments();
    $comments->create($this->owner, $this->repo, $prId, [
			'body' => $body,
		]);
	}

  /**
   * Update the PR body text.
   *
   * @link https://github.com/KnpLabs/php-github-api/blob/master/doc/issues.md#close-an-issue
   * @link https://developer.github.com/v3/issues/#update-an-issue
   *
   * @param string $prId
   * @param string $body
   */
	public function updateIssueBody($prId, $body) {
    $this->client()->api('issue')->update($this->owner, $this->repo, $prId, [
      'body' => $body,
    ]);
  }

}
