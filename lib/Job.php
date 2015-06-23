<?php
/**
 * Created by PhpStorm.
 * User: cwioro
 * Date: 18.06.15
 * Time: 13:27
 */

namespace bitcodin;

/**
 * Class Job
 * @package bitcodin
 */
class Job extends ApiResource
{

    const URL_CREATE = '/job/create';
    const URL_GET = '/job/{id}';
    const URL_GET_LIST = '/jobs/{page}';

    const STATUS_ENQUEUED = 'Enqueued';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_FINISHED = 'Finished';
    const STATUS_ERROR = 'Error';

    /**
     * @var int
     */
    public $jobId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public $inputId;

    /**
     * @var array
     */
    public $encodingProfiles;

    public function update()
    {
        self::_copy(self::get($this));
    }

    /**
     * @param \stdClass $class
     */
    public function __construct(\stdClass $class)
    {
        parent::__construct($class);
    }

    /**
     * @param JobConfig $jobConfig
     * @return Job
     */
    public static function create(JobConfig $jobConfig)
    {
        $response = self::_postRequest(self::URL_CREATE, $jobConfig->getRequestBody(), 201);
        return new self(json_decode($response->getBody()->getContents()));
    }

    /**
     * @param $id
     * @return Job
     */
    public static function get($id)
    {
        if ($id instanceof \stdClass)
            $id = $id->jobId;

        $response = self::_getRequest(str_replace('{id}', $id, self::URL_GET), 200);

        return new self(json_decode($response->getBody()->getContents()));
    }

    /**
     * @param int $page
     * @return mixed
     */
    public static function getList($page = 1)
    {
        $response = self::_getRequest(str_replace('{page}', $page, self::URL_GET_LIST), 200);
        $responseDecode = json_decode($response->getBody()->getContents());
        $count = 0;
        foreach ($responseDecode->jobs as $job)
            $responseDecode->jobs[$count++] = new self($job);

        return $responseDecode;
    }


    public static function getListAll()
    {
        $jobsTotal = 1;
        $jobs = [];
        for ($page = 1; sizeof($jobs) < $jobsTotal; $page++) {
            $jobResponse = Job::getList($page);
            $jobList = $jobResponse->jobs;

            $jobsTotal = $jobResponse->totalCount;

            foreach ($jobList as $job) {
                $jobs[] = $job;
            }
        }

        return $jobs;
    }
}