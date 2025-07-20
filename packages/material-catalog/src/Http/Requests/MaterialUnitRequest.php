<?php

namespace Packages\MaterialCatalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $storeId = auth()->user()->current_store_id;
        $unitId = $this->route('unit')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('material_units')
                    ->where('store_id', $storeId)
                    ->ignore($unitId),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('material_units')
                    ->where('store_id', $storeId)
                    ->ignore($unitId),
            ],
            'symbol' => [
                'required',
                'string',
                'max:10',
                Rule::unique('material_units')
                    ->where('store_id', $storeId)
                    ->ignore($unitId),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['weight', 'volume', 'length', 'area', 'count', 'time']),
            ],
            'conversion_factor' => [
                'required',
                'numeric',
                'min:0.001',
                'max:999999.999',
            ],
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'mã đơn vị',
            'name' => 'tên đơn vị',
            'symbol' => 'ký hiệu',
            'type' => 'loại đơn vị',
            'conversion_factor' => 'hệ số chuyển đổi',
            'is_active' => 'trạng thái hoạt động',
            'is_default' => 'đặt làm mặc định',
            'description' => 'mô tả',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã đơn vị là bắt buộc.',
            'code.unique' => 'Mã đơn vị đã tồn tại trong cửa hàng này.',
            'code.regex' => 'Mã đơn vị chỉ được chứa chữ cái in hoa, số, dấu gạch ngang và gạch dưới.',
            'name.required' => 'Tên đơn vị là bắt buộc.',
            'name.unique' => 'Tên đơn vị đã tồn tại trong cửa hàng này.',
            'symbol.required' => 'Ký hiệu là bắt buộc.',
            'symbol.unique' => 'Ký hiệu đã tồn tại trong cửa hàng này.',
            'type.required' => 'Loại đơn vị là bắt buộc.',
            'type.in' => 'Loại đơn vị không hợp lệ.',
            'conversion_factor.required' => 'Hệ số chuyển đổi là bắt buộc.',
            'conversion_factor.numeric' => 'Hệ số chuyển đổi phải là số.',
            'conversion_factor.min' => 'Hệ số chuyển đổi phải lớn hơn 0.',
            'conversion_factor.max' => 'Hệ số chuyển đổi quá lớn.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper($this->code),
            'is_active' => $this->boolean('is_active', true),
            'is_default' => $this->boolean('is_default', false),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateDefaultUnit($validator);
        });
    }

    /**
     * Validate default unit logic.
     */
    private function validateDefaultUnit($validator): void
    {
        if ($this->is_default) {
            $storeId = auth()->user()->current_store_id;
            $unitId = $this->route('unit')?->id;
            $type = $this->type;

            // Check if there's already a default unit of this type
            $existingDefault = \Packages\MaterialCatalog\Models\MaterialUnit::query()
                ->where('store_id', $storeId)
                ->where('type', $type)
                ->where('is_default', true)
                ->when($unitId, fn($q) => $q->where('id', '!=', $unitId))
                ->exists();

            if ($existingDefault) {
                $validator->errors()->add(
                    'is_default',
                    "Đã có đơn vị mặc định cho loại '{$type}'. Vui lòng bỏ chọn đơn vị mặc định hiện tại trước."
                );
            }
        }
    }
}
