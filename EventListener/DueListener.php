<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DueBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DueListener implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        );
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        // Intercept if due date is supposed to be "now".
        if(
            isset($data['campaignchain_hook_campaignchain_due']['execution_choice']) &&
            $data['campaignchain_hook_campaignchain_due']['execution_choice'] == 'now'
        ){
            unset($data['campaignchain_hook_campaignchain_due']['date']);
            // TODO: Remove this hack for demo.
//            $nowDate = new \DateTime('now');
//
//            $datetimeUtil = $this->container->get('campaignchain.core.util.datetime');
//            $data['campaignchain_hook_campaignchain_due']['date'] = $datetimeUtil->formatLocale($nowDate->modify('+1 day'));
        }

        $event->setData($data);
    }
}