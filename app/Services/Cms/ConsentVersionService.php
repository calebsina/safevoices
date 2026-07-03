<?php

namespace App\Services\Cms;

use App\Models\Consent\ConsentVersion;
use App\Services\TranslatableCrudService;
use Illuminate\Database\Eloquent\Model;

class ConsentVersionService extends TranslatableCrudService
{
    protected string $model = ConsentVersion::class;

    public function update(Model|string|int $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data) {
            $version = parent::update($model, $data);

            // Only one active consent version at a time.
            if ($version->is_active) {
                ConsentVersion::where('id', '!=', $version->id)->update(['is_active' => false]);
            }

            return $version;
        });
    }
}
