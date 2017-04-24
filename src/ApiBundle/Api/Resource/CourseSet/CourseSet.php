<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseSet extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            throw new ResourceNotFoundException('课程不存在');
        }

        $this->getOCUtil()->single($courseSet, array('creator', 'teacherIds'));

        $this->appendMaxOriginPriceAndMinOriginPrice($courseSet);

        return $courseSet;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            $this->getSort($request),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($courseSets, array('creator', 'teacherIds'));

        $total = $this->getCourseSetService()->countCourseSets($conditions);

        return $this->makePagingObject($courseSets, $total, $offset, $limit);
    }

    private function appendMaxOriginPriceAndMinOriginPrice(&$courseSet)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        $maxOriginPrice = 0;
        $minOriginPrice = 0;
        foreach ($courses as $course) {
            if ($course['originPrice'] > $maxOriginPrice) {
                $maxOriginPrice = $course['originPrice'];
            }

            if (!$minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }

            if ($course['originPrice'] < $minOriginPrice) {
                $minOriginPrice = $course['originPrice'];
            }
        }

        $courseSet['maxOriginPrice'] = $maxOriginPrice;
        $courseSet['minOriginPrice'] = $minOriginPrice;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
       return $this->service('Course:CourseSetService');
    }
}