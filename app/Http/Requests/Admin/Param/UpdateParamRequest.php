<?php


namespace App\Http\Requests\Admin\Param;

use App\Models\IntegrationServiceInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateParamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'     => ['sometimes', 'in:static,user_integration,params'],
            'value'    => ['sometimes', 'nullable', 'string', 'max:500'],
            'input_id' => ['sometimes', 'nullable', 'integer', 'exists:integration_service_inputs,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('type') !== 'params') {
                return;
            }

            $inputId = $this->input('input_id');

            if (empty($inputId)) {
                $v->errors()->add('input_id', 'input_id is required when type is params.');
                return;
            }

            $serviceId = (int) $this->route('serviceId');

            $belongsToService = IntegrationServiceInput::where('id', $inputId)
                ->where('integration_service_id', $serviceId)
                ->exists();

            if (! $belongsToService) {
                $v->errors()->add('input_id', 'input_id must reference an input belonging to this service.');
            }
        });
    }
}
