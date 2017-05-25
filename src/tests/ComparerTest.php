<?php

use PHPUnit\Framework\TestCase;
use SchibstedApp\GitHubApiClient;
use SchibstedApp\Comparer;

class ComparerTest extends TestCase
{
    /**
     * @var Comparer
     */
    private $Comparer;

    public function setUp()
    {
        $this->Comparer = new Comparer();
    }

    public function testBuildRepoObject()
    {
        $repoObject = $this->Comparer->buildRepoObject('schibsted/sdk-ios');
        $this->assertInternalType('object',$repoObject);
        $this->assertObjectHasAttribute('forks',$repoObject);
        $this->assertObjectHasAttribute('lastMerge',$repoObject);
        $this->assertInternalType('integer',$repoObject->forks);
        $this->assertInternalType('string',$repoObject->latestRelease);
    }

    public function testCompareStatistics()
    {
        $obj1 = new \stdClass();
        $obj1->forks = 5;
        $obj1->stars = 5;
        $obj1->watchers = 5;
        $obj1->latestRelease = '2017-04-11T08:12:05Z';
        $obj1->pullRequestOpen = 5;
        $obj1->pullRequestOpen = 5;
        $obj1->pullRequestClosed = 5;
        $obj1->lastMerge = '2017-04-11T08:12:05Z';
        $obj1->points = 0;

        $obj2 = new \stdClass();
        $obj2->forks = 1;
        $obj2->stars = 1;
        $obj2->watchers = 1;
        $obj2->latestRelease = '2015-04-11T08:12:05Z';
        $obj2->pullRequestOpen = 1;
        $obj2->pullRequestOpen = 1;
        $obj2->pullRequestClosed = 1;
        $obj2->lastMerge = '2017-05-11T08:12:05Z'; //let's make this one bigger in second object
        $obj2->points = 0;

        $this->Comparer->CompareStatistics($obj1,$obj2);

        $this->assertEquals(17, $obj1->points);
        $this->assertEquals(Comparer::POINTS_LASTMERGE, $obj2->points);
    }
}