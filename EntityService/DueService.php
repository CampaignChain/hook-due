<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) Sandro Groganz <sandro@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DueBundle\EntityService;

use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\Hook\DueBundle\Entity\Due;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DueService implements HookServiceTriggerInterface
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
    }

//    public function newObject($entityClass, $entityId, $formData){
//        $due = new Due();
//        $due->setEntityId($entityId);
//        $due->setEntityClass($entityClass);
//        $due->setDate($formData['date']);
//
//        return $due;
//    }

    public function getHook($entity){
        $hook = new Due();

        if(is_object($entity) && $entity->getId() !== null){
            if($entity->getCampaign()->getHasRelativeDates()){
                $interval = $entity->getCampaign()->getStartDate()->diff(
                    $entity->getStartDate()
                );
                $hook->setDays($interval->format("%a"));
                $hook->setTime(
                    $entity->getStartDate()->format('H').':'.$entity->getStartDate()->format('i')
                );
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

        if($entity->getCampaign()->getHasRelativeDates()){
            $campaignStartDate = $entity->getCampaign()->getStartDate();
            $hookStartDate = $campaignStartDate->modify('+'.$hook->getDays().' days');
            $hookStartDate = new \DateTime(
                $hookStartDate->format('Y-M-d').' '.$hook->getTime().':00'
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
            $operation->setStartDate($hook->getStartDate());
            $operation->setEndDate($hook->getEndDate());
            $operation->setTriggerHook($entity->getTriggerHook());
        }

        return $entity;
    }

    /**
     * This method is being called by the scheduler to check whether
     * an entity's trigger hook allows the scheduler to execute
     * the entity's Job.
     *
     * @param $entity
     * @return bool
     */
    public function isExecutable($entity){
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if($entity->getStartDate() <= $now){
            return true;
        }

        return false;
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
            array('hook' => $hook)
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