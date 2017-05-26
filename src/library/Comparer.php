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
    const POINTS_UPDATE = 1;

    public function __construct()
    {
        $root = realpath(dirname(__FILE__) . '/../');
        $this->logger = new \Monolog\Logger('Comparer');
        $file_handler = new \Monolog\Handler\StreamHandler($root."/logs/app.log");
        $this->logger->pushHandler($file_handler);
    }

    /**
     *  @SWG\Definition(
     *   definition="Stats",
     *   type="object",
     *       @SWG\Schema(
     *           required={"name"},
     *           @SWG\Property(property="name", type="string")
     *           @SWG\Property(property="forks", format="int64", type="integer")
     *           @SWG\Property(property="stars", format="int64", type="integer")
     *           @SWG\Property(property="watchers", format="int64", type="integer")
     *           @SWG\Property(property="latestRelease", type="string")
     *           @SWG\Property(property="pullRequestOpen", type="string")
     *           @SWG\Property(property="pullRequestClosed", type="string")
     *           @SWG\Property(property="lastMerge", type="string")
     *           @SWG\Property(property="updateDate", type="string")
     *           @SWG\Property(property="points", format="int64", type="integer")
     *           @SWG\Property(property="percent", format="int64", type="integer")
     *       )
     * )
     */
    public function buildRepoObject($repoName)
    {
        $GitHubClient = new GitHubApiClient();
        $Cache = new Cache();

        list($owner, $repo) = explode("/", $repoName, 2);
        $repoObject = new \stdClass();

        if($Cache->isCache($owner, $repo))
        {
            $data = $Cache->readCache($owner, $repo);
            $repoObject = json_decode($data);
        }
        else
        {
            if(!$GitHubClient->isRepo($owner, $repo))
            {
                return false;
            }

            $repoObject->name = $repoName;
            $repoObject->forks = $GitHubClient->getForksCount($owner, $repo);
            $repoObject->stars = $GitHubClient->getStarsCount($owner, $repo);
            $repoObject->watchers = $GitHubClient->getWatchersCount($owner, $repo);
            $repoObject->latestRelease = $GitHubClient->getLatestReleaseDate($owner, $repo);
            $repoObject->pullRequestOpen = $GitHubClient->getPullsCount($owner, $repo, $GitHubClient::PULL_STATE_OPEN);
            $repoObject->pullRequestClosed = $GitHubClient->getPullsCount($owner, $repo, $GitHubClient::PULL_STATE_CLOSED);
            $repoObject->lastMerge = $GitHubClient->getLastMergeDate($owner, $repo);
            $repoObject->updateDate = $GitHubClient->getUpdateDate($owner, $repo);
            $repoObject->points = 0;

            $Cache->saveCache($owner, $repo,json_encode($repoObject));
        }

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
        $this->logger->addInfo("compareStatistics");

        $this->compareValue($obj1, $obj2, 'forks', Comparer::POINTS_FORKS);

        $this->compareValue($obj1, $obj2, 'stars', Comparer::POINTS_STARS);

        $this->compareValue($obj1, $obj2, 'watchers', Comparer::POINTS_WATCHERS);

        $this->compareValue($obj1, $obj2, 'latestRelease', Comparer::POINTS_RELEASE);

        $this->compareValue($obj1, $obj2, 'pullRequestOpen', Comparer::POINTS_PULLSOPEN);

        $this->compareValue($obj1, $obj2, 'pullRequestClosed', Comparer::POINTS_PULLCLOSED);

        $this->compareValue($obj1, $obj2, 'lastMerge', Comparer::POINTS_LASTMERGE);

        $this->compareValue($obj1, $obj2, 'updateDate', Comparer::POINTS_UPDATE);

        $sumPoints = $obj1->points + $obj2->points;
        $obj1->percent = round(($obj1->points/$sumPoints)*100);
        $obj2->percent = 100-$obj1->percent;

        $winner = $obj1->percent == $obj2->percent ? "draw" : ($obj1->percent > $obj2->percent ? $obj1->name : $obj2->name);

        return array('comparison' => array('repo1' => $obj1, 'repo2' => $obj2), 'winner' => $winner);
    }
}