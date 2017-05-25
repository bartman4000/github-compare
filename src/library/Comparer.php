<?php
/**
 * Created by PhpStorm.
 * User: Bartek
 * Date: 2017-05-25
 * Time: 17:06
 */

namespace SchibstedApp;

use SchibstedApp\GitHubApiClient;

class Comparer
{
    const POINTS_FORKS = 2;
    const POINTS_STARS = 5;
    const POINTS_WATCHERS = 5;
    const POINTS_RELEASE = 3;
    const POINTS_PULLSOPEN = 1;
    const POINTS_PULLCLOSED = 1;
    const POINTS_LASTMERGE = 2;

    public function buildRepoObject($repoName)
    {
        $GitHubClient = new GitHubApiClient();

        list($owner, $repo) = explode("/", $repoName, 2);

        $repoObject = new \stdClass();
        $repoObject->name = $repoName;
        $repoObject->forks = $GitHubClient->getForksCount($owner, $repo);
        $repoObject->stars = $GitHubClient->getStarsCount($owner, $repo);
        $repoObject->watchers = $GitHubClient->getWatchersCount($owner, $repo);
        $repoObject->latestRelease = $GitHubClient->getLatestReleaseDate($owner, $repo);
        $repoObject->pullRequestOpen = $GitHubClient->getPullsCount($owner, $repo, $GitHubClient::PULL_STATE_OPEN);
        $repoObject->pullRequestClosed = $GitHubClient->getPullsCount($owner, $repo, $GitHubClient::PULL_STATE_CLOSED);
        $repoObject->lastMerge = $GitHubClient->getLastMergeDate($owner, $repo);
        $repoObject->points = 0;
        return $repoObject;
    }

    protected function compareValue(&$obj1, &$obj2, $value, $scoring)
    {
        $result = strnatcmp($obj1->{$value}, $obj2->{$value});
        if($result == 1)
        {
            $obj1->points += $scoring;
        }
        elseif($result == -1)
        {
            $obj2->points += $scoring;
        }
    }

    public function compareStatistics(&$obj1, &$obj2)
    {
        $this->compareValue($obj1, $obj2, 'forks', Comparer::POINTS_FORKS);

        $this->compareValue($obj1, $obj2, 'stars', Comparer::POINTS_STARS);

        $this->compareValue($obj1, $obj2, 'watchers', Comparer::POINTS_WATCHERS);

        $this->compareValue($obj1, $obj2, 'latestRelease', Comparer::POINTS_RELEASE);

        $this->compareValue($obj1, $obj2, 'pullRequestOpen', Comparer::POINTS_PULLSOPEN);

        $this->compareValue($obj1, $obj2, 'pullRequestClosed', Comparer::POINTS_PULLCLOSED);

        $this->compareValue($obj1, $obj2, 'lastMerge', Comparer::POINTS_LASTMERGE);
    }
}