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

namespace CampaignChain\Hook\DueBundle\EntityService;

use CampaignChain\CoreBundle\Entity\Hook;
use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\Hook\DueBundle\Entity\Due;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DueService implements HookServiceTriggerInterface
{
    protected $em;
    protected $container;

    public function __construct(ManagerRegistry $managerRegistry, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $managerRegistry->getManager();
    }

//    public function newObject($entityClass, $entityId, $formData){
//        $due = new Due();
//        $due->setEntityId($entityId);
//        $due->setEntityClass($entityClass);
//        $due->setDate($formData['date']);
//
//        return $due;
//    }

    public function getHook($entity, $mode = Hook::MODE_DEFAULT){
        $hook = new Due();

        if(
            is_object($entity) &&
            $entity->getId() !== null
        ){
            if(
                $mode == Hook::MODE_DEFAULT &&
                // Operations have no direct relation to a campaign, so exclude them.
                strpos(get_class($entity), 'CoreBundle\Entity\Operation') === false &&
                // Don't process Campaigns here.
                strpos(get_class($entity), 'CoreBundle\Entity\Campaign') === false &&
                // Check if the Action's campaign has relative dates.
                $entity->getCampaign()->getHasRelativeDates()
            ){
                $interval = $entity->getCampaign()->getStartDate()->diff(
                    $entity->getStartDate()
                );
                $time = $entity->getStartDate()->format('H').':'.$entity->getStartDate()->format('i');
                $days = $interval->format("%a");
                if($time != '00:00'){
                    $days = ++$days;
                }
                $hook->setDays($days);
                $hook->setTime($time);
            }

            $hook->setStartDate($entity->getStartDate());
        }

        return $hook;
    }

    public function processHook($entity, $hook){
        // TODO: Remove this hack which fixes a validation issue.
        if(!$hook->getStartDate()){
            $now = new \DateTime('now', new \DateTimeZone($hook->getTimezone()));
            $hook->setStartDate($now);
        }

        if($hook->getDays()){
            $campaignStartDate = $entity->getCampaign()->getStartDate();
            $days = $hook->getDays();
            if($hook->getTime() != '00:00'){
                $days = $days-1;
            }
            $hookStartDate = $campaignStartDate->modify('+'.$days.' days');
            $hookStartDate = new \DateTime(
                $hookStartDate->format('Y-M-d').' '.$hook->getTime().':00',
                new \DateTimeZone($hook->getTimezone())
            );

            $hook->setStartDate($hookStartDate);
        }

        // Update the dates of the entity.
        $entity->setStartDate($hook->getStartDate());
        $entity->setEndDate($hook->getEndDate());

        // If the entity is an Activity and it equals the Operation, then
        // - the same dates will be set for the Operation
        // - the same trigger Hook will be set for the Operation
        $class = get_class($entity);
        if(strpos($class, 'CoreBundle\Entity\Activity') !== false && $entity->getEqualsOperation() == true){
            $operation = $entity->getOperations()[0];
            if(is_object($operation)){
                $operation->setStartDate($hook->getStartDate());
                $operation->setEndDate($hook->getEndDate());
                $operation->setTriggerHook($entity->getTriggerHook());
            } else {
                throw new \Exception(
                    'The Activity with ID "'.$entity->getId().'" '
                    .'does not have a related Operation, although the module '
                    .'"'.$entity->getActivityModule()->getIdentifier().'" '
                    .'of bundle '
                    .'"'.$entity->getActivityModule()->getBundle()->getName().'" '
                    .'is configured so that the Activity equals the Operation.'
                );
            }
        }

        return $entity;
    }

    public function arrayToObject($hookData){
        if(is_array($hookData) && count($hookData)){
            $datetimeUtil = $this->container->get('campaignchain.core.util.datetime');

            // Intercept if due date is supposed to be "now".
            if(isset($hookData['execution_choice'])){
                if($hookData['execution_choice'] == 'now'){
                    $nowDate = new \DateTime('now');
                    $hookData['date'] = $datetimeUtil->formatLocale($nowDate);
                }
                unset($hookData['execution_choice']);
            }

            $hook = new Due();
            foreach($hookData as $property => $value){
                // TODO: Research whether this is a security risk, e.g. if the property name has been injected via a REST post.
                $method = 'set'.Inflector::classify($property);
                if($method == 'setDate' && !is_object($value) && !$value instanceof \DateTime){
                    // TODO: De-localize the value and change from user format to ISO8601.
                    $value = new \DateTime($value, new \DateTimeZone($hookData['timezone']));
                }
                $hook->$method($value);
            }
        }

        return $hook;
    }

    public function tplInline($entity){
        $hook = $this->getHook($entity);
        return $this->container->get('templating')->render(
            'CampaignChainHookDueBundle::inline.html.twig',
            array(
                'hook' => $hook,
                'campaign_has_relative_dates' => $entity->getCampaign()->getHasRelativeDates(),
            )
        );
    }

    /**
     * Returns the corresponding start date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getStartDateIdentifier(){
        return 'date';
    }

    /**
     * Returns the corresponding end date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getEndDateIdentifier(){
        return 'date';
    }
}