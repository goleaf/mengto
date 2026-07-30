<?php

namespace App\Http\Requests;

class UpdateExpertProfileRequest extends StoreExpertProfileRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset(
            $rules['credential_type'],
            $rules['credential_title'],
            $rules['credential_issuer'],
            $rules['credential_file'],
        );

        return $rules;
    }
}
