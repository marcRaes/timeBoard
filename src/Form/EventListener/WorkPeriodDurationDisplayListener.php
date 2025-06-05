<?php

namespace App\Form\EventListener;

use App\Service\WorkDurationFormatter;
use App\Entity\WorkPeriod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class WorkPeriodDurationDisplayListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly WorkDurationFormatter $formatter
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data instanceof WorkPeriod || null === $data->getDuration()) {
            return;
        }

        $form->get('durationDisplay')->setData(
            $this->formatter->format($data->getDuration())
        );
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (empty($data['duration'])) {
            return;
        }

        $data['durationDisplay'] = $this->formatter->format((int) $data['duration']);
        $event->setData($data);
    }
}
