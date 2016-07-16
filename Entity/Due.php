<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\Hook\DueBundle\Entity;

class Due
{
    protected $date;

    protected $days;

    protected $time;

    protected $timezone = 'UTC';

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Due
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set days
     *
     * @param \int $days
     * @return Due
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return \int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set time
     *
     * @param \string $time
     * @return Due
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return Due
     */
    public function setTimezone($timezone)
    {
        // Keep 'UTC' as default value if $timezone is empty string.
        if(!empty($timezone)) {
            $this->timezone = $timezone;
        }

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set start date
     *
     * @param \DateTime $date
     * @return Due
     */
    public function setStartDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get start date
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->date;
    }

    /**
     * Set end date
     *
     * @param \DateTime $date
     * @return NULL
     */
    public function setEndDate($date)
    {
        return null;
    }

    /**
     * Get end date
     *
     * @return NULL
     */
    public function getEndDate()
    {
        return null;
    }

    public function toArray()
    {
        return array(
            'date' => $this->getStartDate()->format(\DateTime::ISO8601),
        );
    }
}
