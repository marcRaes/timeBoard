<?php

namespace App\Service\TimeTracking;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Exception\WorkMonthAlreadySentException;
use App\Form\WorkDayType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class WorkDayFormHandler
{
    private bool $success = false;
    private FormInterface $form;

    public function __construct(
        private readonly WorkDayFactory $factory,
        private readonly FormFactoryInterface $formFactory,
        private readonly WorkMonthResolver $workMonthResolver,
        private readonly ValidatorInterface $validator,
        private readonly WorkMonthGuard $workMonthGuard,
        private readonly WorkPeriodValidator $workPeriodValidator,
        private readonly WorkDayPersister $persister,
    ) {}

    public function handle(Request $request, User $user, ?WorkDay $workDay = null): FormInterface
    {
        if ($this->factory->isNew($workDay)) {
            $workDay = $this->factory->create();
        }

        $this->form = $this->formFactory->create(WorkDayType::class, $workDay);
        $this->form->handleRequest($request);

        if ($this->form->isSubmitted()) {
            $workMonth = $this->workMonthResolver->resolve($user, $workDay);
            $workDay->setWorkMonth($workMonth);

            $violations = $this->validator->validate($workDay);

            if (count($violations) === 0 && $this->form->isValid()) {
                try {
                    if ($this->factory->isNew($workDay)) {
                        $this->workMonthGuard->ensureNotSent($workMonth);
                    }

                    $this->workPeriodValidator->removeInvalidPeriods($workDay);
                    $this->persister->save($workDay, $workMonth);
                    $this->success = true;
                } catch (WorkMonthAlreadySentException $e) {
                    $this->form->addError(new FormError($e->getMessage()));
                }
            } else {
                foreach ($violations as $violation) {
                    $this->form->get($violation->getPropertyPath())?->addError(new FormError($violation->getMessage()));
                }
            }
        }

        return $this->form;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
