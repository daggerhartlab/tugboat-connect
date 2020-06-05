<?php

namespace TugboatConnect;

use JiraRestApi\JiraException;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\Comment;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\RemoteIssueLink;

/**
 * @link https://github.com/lesstif/php-jira-rest-client
 */
class Jira {

	private $host;

	private $user;

	private $pass;

	/**
	 * @var \JiraRestApi\Issue\IssueService
	 */
	protected $issueService;

	public function __construct($host, $user, $pass) {
		$this->host = rtrim($host, '/');
		$this->user = $user;
		$this->pass = $pass;
	}

  /**
   * @return \JiraRestApi\Issue\IssueService
   * @throws \JiraRestApi\JiraException
   */
	public function issueService() {
		if (!$this->issueService) {
			$this->issueService = new IssueService(new ArrayConfiguration([
				'jiraHost' => $this->host,
				// for basic authorization:
				'jiraUser' => $this->user,
				'jiraPassword' => $this->pass,
			]));
		}

		return $this->issueService;
	}

  /**
   * Add a comment to a Jira issue.
   *
   * @param string $issueKey - Like DAGLAB-123
   * @param string $body - Comment body markdown
   *
   * @throws \JiraRestApi\JiraException
   * @throws \JsonMapper_Exception
   */
	public function addComment($issueKey, $body) {
		$comment = new Comment();
		$comment->setBody($body);

		$this->issueService()->addComment($issueKey, $comment);
	}

  /**
   * Clean up markdown content and perform simple replacements for Textile.
   *
   * @link https://textile-lang.com/
   *
   * @param string $text
   *
   * @return string
   */
	public function markdownToTextile($text) {
	  $text = preg_replace('/<!--(.*)-->/', '', $text);
	  $text = "\n".$text;
	  $text = strtr($text, [
	    "\n# " => "\nh1. ",
	    "\n## " => "\nh2. ",
	    "\n### " => "\nh3. ",
	    "\n#### " => "\nh4. ",
	    "\n* " => "\n* ",
	    "\n    * " => "\n** ",
	    "\n1. " => "\n# ",
	    "\n    1. " => "\n## ",
	    "\n        1. " => "\n### ",
    ]);
	  return $text;
  }

  /**
   * Add a link to a Jira issue.
   *
   * @param string $issueKey - Like DAGLAB-123
   * @param string $title - Title for the link to add or update
   * @param string $url - URL for the link to add or update
   *
   * @throws \JiraRestApi\JiraException
   * @throws \JsonMapper_Exception
   */
	public function addRemoteLink($issueKey, $title, $url) {
		$link = new RemoteIssueLink();
		$link
			->setUrl($url)
			->setTitle($title);

		$this->issueService()->createOrUpdateRemoteIssueLink($issueKey, $link);
	}

  /**
   * Get all links on a Jira issue.
   *
   * @param string $issueKey - Like DAGLAB-123
   *
   * @return array
   * @throws \JiraRestApi\JiraException
   */
	public function getRemoteLinks($issueKey) {
		return $this->issueService()->getRemoteIssueLink($issueKey);
	}

  /**
   * Search for a specific link in all links on a Jira issue.
   *
   * @param string $issueKey - Like DAGLAB-123
   * @param string $url - Url to find on the ticket
   *
   * @return bool
   * @throws \JiraRestApi\JiraException
   */
	public function remoteLinkExists($issueKey, $url) {
		$links = $this->getRemoteLinks($issueKey);
		$found = FALSE;
		foreach ($links as $link) {
			/** @var $link \JiraRestApi\Issue\RemoteIssueLink */
			print_r($link->object);
			var_dump($link->object->url === $url);
			if ($link->object->url === $url) {
        $found = TRUE;
        break;
      }
		}

		return $found;
	}

	/**
	 * Search a string for an issue key by looking for the hostname
	 *
	 * @param string $text - Text to search
   * @return string|false
	 */
	public function findIssueKey($text) {
		$issueKey = false;
		$matches = [];
		preg_match("|{$this->host}([^\s]+)|", $text, $matches);

		if (!empty($matches[1])) {
			$query = parse_url($matches[0], PHP_URL_QUERY);
			if ($query && stripos($query, 'selectedIssue') !== FALSE) {
				$values = [];
				parse_str($query, $values);
				$issueKey = $values['selectedIssue'];
			}
			else if (stripos($matches[0], $this->host.'/browse/') !== FALSE) {
				$url = explode('?', $matches[0])[0];
				$issueKey = str_replace($this->host.'/browse/', '', $url);
			}
		}

		return $issueKey;
	}

}
