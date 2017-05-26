<?php

use PHPUnit\Framework\TestCase;
use SchibstedApp\GitHubApiClient;

class GitHubApiClientTest extends TestCase
{
    /**
     * @var GitHubApiClient
     */
    private $GitHubApiClient;

    private $testOwner;
    private $testRepo;

    public function setUp()
    {
        $this->GitHubApiClient = new GitHubApiClient();
        $this->testOwner = 'schibsted';
        $this->testRepo = 'sdk-ios';
    }

    public function testCall()
    {
        $content = $this->GitHubApiClient->call('GET','/user/repos');
        $this->assertInternalType("array", json_decode($content));
    }

    public function testGetStarsCount()
    {
        $stars = $this->GitHubApiClient->getStarsCount($this->testOwner, $this->testRepo);
        $this->assertInternalType('integer', $stars);
    }

    public function testGetWatchersCount()
    {
        $watchers = $this->GitHubApiClient->getWatchersCount($this->testOwner, $this->testRepo);
        $this->assertInternalType('integer', $watchers);
    }

    public function testGetForksCount()
    {
        $forks = $this->GitHubApiClient->getForksCount($this->testOwner, $this->testRepo);
        $this->assertInternalType('integer', $forks);
    }

    public function testGetPullsCount()
    {
        $pullsOpened = $this->GitHubApiClient->getPullsCount($this->testOwner, $this->testRepo, GitHubApiClient::PULL_STATE_OPEN);
        $pullsClosed = $this->GitHubApiClient->getPullsCount($this->testOwner, $this->testRepo, GitHubApiClient::PULL_STATE_CLOSED);
        $pullsAll = $this->GitHubApiClient->getPullsCount($this->testOwner, $this->testRepo, GitHubApiClient::PULL_STATE_ALL);
        $this->assertTrue($pullsOpened + $pullsClosed == $pullsAll);
    }

    public function testGetLatestReleaseDate()
    {
        $published_at = $this->GitHubApiClient->getLatestReleaseDate($this->testOwner, $this->testRepo);

        try {
            $date = new DateTime($published_at);
        } catch (Exception $e) {
            $date = false;
        }

        $this->assertInternalType('object', $date);
    }

    public function testGetUpdateDate()
    {
        $published_at = $this->GitHubApiClient->getUpdateDate($this->testOwner, $this->testRepo);

        try {
            $date = new DateTime($published_at);
        } catch (Exception $e) {
            $date = false;
        }

        $this->assertInternalType('object', $date);
    }

    public function testSortPullsByMergedTimeAsc()
    {
        $mergedPulls = $this->GitHubApiClient->getMergedPulls($this->testOwner, $this->testRepo);
        $sortedPulls = $this->GitHubApiClient->sortPullsByMergedTime($mergedPulls, "ASC");
        $firstPull = array_shift($sortedPulls);
        $lastPull = array_pop($sortedPulls);
        $firstPullDate = new DateTime($firstPull->merged_at);
        $lastPullDate = new DateTime($lastPull->merged_at);
        $interval = $firstPullDate->diff($lastPullDate);

        $this->assertInternalType("object", $interval);
        $this->assertEquals(0,$interval->invert);
    }
}