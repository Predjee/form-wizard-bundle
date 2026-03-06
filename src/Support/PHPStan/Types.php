<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Support\PHPStan;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

/**
 * @internal Internal utility class for static type helpers.
 *
 * @phpstan-type StepUuid string
 * @phpstan-type FieldName string
 * @phpstan-type SubmittedData array<StepUuid, array<FieldName, mixed>>
 *
 * @phpstan-type Config array<string, mixed>
 *
 * @phpstan-type Receiver array{type: string, email: string, name?: string|null, receiverType?: string}
 * @phpstan-type Receivers array<int, Receiver>
 *
 * @phpstan-type SuccessLink array{route?: string, url?: string, label?: string, target?: string}
 *
 * @phpstan-type MailField array{label: string|null, value: mixed, width: int}
 * @phpstan-type MailFields list<MailField>
 *
 * @phpstan-type MailContext array{
 *   wizard: WizardFormInterface,
 *   submission: WizardSubmissionInterface,
 *   fields: MailFields,
 *   introText: string|null,
 *   showData: bool
 * }
 *
 * @phpstan-type MailPreviewSubmission object{uuid: string, createdAt: \DateTimeImmutable}
 * @phpstan-type MailPreviewContext array{
 *   wizard: WizardFormInterface,
 *   submission: MailPreviewSubmission,
 *   fields: MailFields,
 *   introText: string|null,
 *   showData: bool
 * }
 *
 * @phpstan-type SuluParam array{
 *   name: string,
 *   type?: string,
 *   value?: mixed
 * }
 *
 * @phpstan-type SuluPropertyMeta array{title?: string}
 *
 * @phpstan-type SuluPropertyConfig array{
 *   name?: string,
 *   type?: string,
 *   colspan?: int,
 *   meta?: SuluPropertyMeta,
 *   visibleCondition?: string,
 *   disabledCondition?: string,
 *   params?: list<SuluParam>,
 *   types?: array<string, array{
 *     meta?: SuluPropertyMeta,
 *     properties?: list<mixed>
 *   }>
 * }
 */
final class Types
{
}
