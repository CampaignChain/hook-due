<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) Sandro Groganz <sandro@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DueBundle\Entity;

class Due
{
    protected $date;

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
     * Set timezone
     *
     * @param string $timezone
     * @return Due
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

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
