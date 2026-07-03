<?php

namespace App\Services\Reference;

use App\Models\Reference\AffectedPersonType;
use App\Models\Reference\Channel;
use App\Models\Reference\ReferralPartnerType;
use App\Models\Reference\Relationship;
use App\Services\TranslatableCrudService;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * One service for the four small lookup lists. The route parameter
 * {type} selects the model, so a single controller serves all of them:
 * /reference/lookups/relationships, /reference/lookups/channels, ...
 */
class LookupService extends TranslatableCrudService
{
    /** Route slug -> model map. */
    public const TYPES = [
        'affected-person-types'  => AffectedPersonType::class,
        'relationships'          => Relationship::class,
        'referral-partner-types' => ReferralPartnerType::class,
        'channels'               => Channel::class,
    ];

    protected string $model = Relationship::class; // replaced by forType()

    public function forType(string $type): static
    {
        if (! isset(self::TYPES[$type])) {
            throw new NotFoundHttpException;
        }

        $this->model = self::TYPES[$type];

        return $this;
    }

    public function activeOrdered(): Builder
    {
        return $this->query()->where('is_active', true)->orderBy('sort_order');
    }
}
