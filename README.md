# Tugboat Connect

This script looks for a jira card link on a github PR,
then updates the jira card with attachments for the PR and tugboat preview.

Very much a work in progress.

Supports:

* Github
* Jira

## Install and example use

**composer.json**
```
{
    repositories: [
        {
            "type": "vcs",
            "url": "https://github.com/daggerhartlab/tugboat-connect.git"
        }
    ],
    "require": {
        "daggerhartlab/tugboat-connect": "dev-master"
    },
}
```

**index.php**
```php
<?php

require __DIR__ . "/vendor/autoload.php";

if (empty($_ENV['TUGBOAT_GITHUB_PR'])) {
	return;
}

$tugboat = new \TugboatConnect\Environment\Tugboat( $_ENV );
$worker = new \TugboatConnect\Worker( $tugboat );
$worker->connectGitHubJira();

```

## Tugboat config 

Update Tugboat repository settings. Add the following environment variables:

- GITHUB_CI_BOT_USERNAME
- GITHUB_CI_BOT_PASSWORD
- JIRA_API_HOST            - jira host name, like: https://***.atlassian.net
- JIRA_API_USERNAME        - jira user email
- JIRA_API_PASSWORD        - jira user api key
- TUGBOAT_BASIC_AUTH_CREDS - (optional) "username:password"
