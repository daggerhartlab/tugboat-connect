<?php

namespace TugboatConnect;

use TugboatConnect\Environment\Tugboat;
use TugboatConnect\Service\IssueService;
use TugboatConnect\Service\RepoService;

class Worker {

	protected $tugboat;

	public function __construct( Tugboat $tugboat ) {
		$this->tugboat = $tugboat;
	}

	/**
	 *
	 */
	public function connectGitHubJira() {
		$tugboat = $this->tugboat;
		$github = $tugboat->createGithub();
		$jira = $tugboat->createJira();
		$issue_body = $github->getPrBody( [
			'PREVIEW_URL' => $tugboat->previewUrl()
		] );
		$issue_key = $jira->findIssueKey( $issue_body );
		$jira->setIssueKey( $issue_key );
		$comment_body = $this->makeRepoCommentBody("{$jira->getHost()}/browse/{$issue_key}");

		$this->connect( $github, $jira, $issue_body, $comment_body );
	}

	/**
	 * @param string $issue_url
	 *
	 * @return string
	 */
	protected function makeRepoCommentBody( $issue_url ) {
		return str_replace("\t", "", "
			Links:

			* [Tugboat Preview]({$this->tugboat->previewUrl()})
			* [Tugboat Dashboard](https://dashboard.tugboat.qa/{$this->tugboat->previewId()})
			* [Issue]({$issue_url})
		");
	}

	/**
	 * @param \TugboatConnect\Service\RepoService $repo
	 * @param \TugboatConnect\Service\IssueService $issue
	 * @param $issue_body
	 * @param $comment_body
	 */
	protected function connect( RepoService $repo, IssueService $issue, $issue_body, $comment_body ) {
		try {
			if (!$issue->remoteLinkExists($this->tugboat->previewUrl())) {
				$issue->addRemoteLink( "GitHub PR: {$repo->getPrTitle()}", $repo->getPrUrl() );
				$issue->addRemoteLink( 'Tugboat QA Preview', $this->tugboat->previewUrl() );

				// Add the entire repo issue body as a comment in the issue.
				$issue->addComment( $issue->formatComment( $issue_body ) );

				// Add the comment to the repo and update the repo's issue.
				$repo->addComment( $comment_body );
				$repo->updateIssueBody( $issue_body );
			}
		}
		catch (\Exception $exception) {
			echo "
				---------- TUGBOAT CONNECT ERROR ----------
				--
				-- Code: {$exception->getCode()}
				-- Message: {$exception->getMessage()}
				-- File: {$exception->getFile()}
				-- Line: {$exception->getLine()}
				---- Trace ----
				{$exception->getTraceAsString()}
				--
				-------------------------------------------
			";
		}
	}


}

