<?php
/*
This script looks for a jira card link on a github PR,
then updates the jira card with attachments for the PR and tugboat preview.

## Requirements:

1. Update Tugboat repository settings. Add the following environment variables:

- GITHUB_CI_BOT_USERNAME
- GITHUB_CI_BOT_PASSWORD
- JIRA_API_HOST            - jira host name, like: https://***.atlassian.net
- JIRA_API_USERNAME        - jira user email
- JIRA_API_PASSWORD        - jira user api key
- TUGBOAT_BASIC_AUTH_CREDS - (optional) "username:password"
*/

require __DIR__ . "/vendor/autoload.php";

use TugboatConnect\Github;
use TugboatConnect\Jira;
use League\CommonMark\GithubFlavoredMarkdownConverter;

if (empty($_ENV['TUGBOAT_GITHUB_PR'])) {
	return;
}

$gh_owner = $_ENV['TUGBOAT_GITHUB_OWNER'];
$gh_repo = $_ENV['TUGBOAT_GITHUB_REPO'];
$pr_id = $_ENV['TUGBOAT_GITHUB_PR'];
$pr_title = $_ENV['TUGBOAT_GITHUB_TITLE'];
$pr_url = "https://github.com/{$gh_owner}/{$gh_repo}/pull/{$pr_id}";
$jira_host = rtrim($_ENV['JIRA_API_HOST'], '/');
$tugboat_service_url = rtrim($_ENV['TUGBOAT_SERVICE_URL'], '/');

if (!empty($_ENV['TUGBOAT_BASIC_AUTH_CREDS'])) {
	$tugboat_service_url = str_replace('https://', "https://{$_ENV['TUGBOAT_BASIC_AUTH_CREDS']}@", $tugboat_service_url);
}

$github = new Github($gh_owner, $gh_repo, $_ENV['GITHUB_CI_BOT_USERNAME'], $_ENV['GITHUB_CI_BOT_PASSWORD']);
$jira = new Jira($jira_host, $_ENV['JIRA_API_USERNAME'], $_ENV['JIRA_API_PASSWORD']);
$converter = new GithubFlavoredMarkdownConverter([
    'html_input' => 'allow',
]);

$pr_body = $github->getPrBody($pr_id);
$pr_body = str_replace('PREVIEW_URL', $tugboat_service_url, $pr_body);
$issue_key = $jira->findIssueKey($pr_body);
$comment_body = "
Links:

* [Tugboat Preview]({$tugboat_service_url})
* [Tugboat Dashboard](https://dashboard.tugboat.qa/{$_ENV['TUGBOAT_PREVIEW_ID']})
* [Jira Issue]({$jira_host}/browse/{$issue_key})
";

try {
  if ($issue_key && !$jira->remoteLinkExists($issue_key, $tugboat_service_url)) {
    $jira->addRemoteLink($issue_key, "GitHub PR: {$pr_title}", $pr_url);
    $jira->addRemoteLink($issue_key, 'Tugboat QA Preview', $tugboat_service_url);
    $jira->addComment($issue_key, $jira->markdownToTextile($pr_body));

    $github->addComment($pr_id, $comment_body);
    $github->updateIssueBody($pr_id, $pr_body);
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
