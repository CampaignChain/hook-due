<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) Sandro Groganz <sandro@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DueBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DueType extends AbstractType
{
    private $campaign;

    protected $container;
    protected $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->datetime = $this->container->get('campaignchain.core.util.datetime');
    }

    public function setCampaign($campaign){
        $this->campaign = $campaign;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!$this->campaign->getHasRelativeDates()){
            // By default, start date is today
            $now = $this->datetime->setUserTimezone(new \DateTime('now'));
            $endDatePicker = null;
            $helpText = null;

            if($this->campaign){
                $startDateCampaign = $this->datetime->formatLocale($this->campaign->getStartDate());

                // If the campaign's start date is after now, then use that as the start and initial date.
                if($this->campaign->getStartDate() > $now){
                    $startDatePicker = $this->datetime->formatLocale($this->campaign->getStartDate());
                } else {
                    $startDatePicker = $this->datetime->formatLocale($now);
                }

                $endDateCampaign = $endDatePicker = $this->datetime->formatLocale($this->campaign->getEndDate());

                $helpText = 'Campaign starts '.$startDateCampaign.' and ends '.$endDateCampaign.'.';
            } else {
                $startDatePicker = $this->datetime->formatLocale($now);
            }

            if($this->campaign){
                // If the campaign is running right now, then offer the
                // option to execute now or schedule execution.
                if(DateTimeUtil::isWithinDuration($this->campaign->getStartDate(), new \DateTime('now'), $this->campaign->getEndDate())){
                    $builder
                        ->add('execution_choice', 'choice', array(
                            'label'     => false,
                            'choices' => array(
                                'now' => 'Now',
                                'schedule' => 'Schedule'
                            ),
                            'mapped' => false,
                            'expanded' => true,
                            'multiple' => false,
                            'required' => true,
                            'data' => 'schedule',
                        ));
                }
            }

            $builder
                ->add('date', 'collot_datetime', array(
    //                'mapped' => false,
    //                'data' => $dataDue,
                    'label' => false,
                    'required' => false,
                    'constraints' => array(),
                    'model_timezone' => 'UTC',
                    'view_timezone' => $this->datetime->getUserTimezone(),
                    'pickerOptions' => array(
                        'format' => $this->datetime->getUserDatetimeFormat('datepicker'),
                        'weekStart' => 0,
                        'startDate' => $startDatePicker,
                        'endDate' => $endDatePicker,
                        'autoclose' => true,
                        'startView' => 'month',
                        'minView' => 'hour',
                        'maxView' => 'decade',
                        'todayBtn' => false,
                        'todayHighlight' => true,
                        'keyboardNavigation' => true,
                        'language' => 'en',
                        'forceParse' => true,
                        'minuteStep' => 5,
                        'pickerReferer ' => 'default', //deprecated
                        'pickerPosition' => 'bottom-right',
                        'viewSelect' => 'hour',
                        'showMeridian' => false,
    //                    'initialDate' => $startDatePicker,
                    ),
                    'attr' => array(
                        'help_text' => $helpText,
                        'input_group' => array(
                            'append' => '<span class="fa fa-calendar">',
                        )
                    )
                ));
        } else {
            // TODO: Allow only number of days within campaign duration.
            // TODO: Ensure time is not after or before campaign duration
            $builder
                ->add('days', 'integer', array(
                    'label' => false,
                    'precision' => 0,
                    'attr' => array(
                        'help_text' => 'Days after start of campaign',
                        'input_group' => array(
                            'append' => '<span class="fa fa-calendar">',
                        )
                    )
                ))
                ->add('time', 'text', array(
                    'label' => false,
                    'attr' => array(
                        'help_text' => 'Execution time at that day',
                        'input_group' => array(
                            'append' => '<span class="fa fa-clock-o">',
                        )
                    )
                ));
        }

        $builder
            ->add('timezone', 'hidden', array(
                'data' => $this->datetime->getUserTimezone(),
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'validation_groups' => false,
            ]);
    }

    public function getName()
    {
        return 'campaignchain_hook_campaignchain_due';
    }
}