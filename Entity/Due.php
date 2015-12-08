<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
}
