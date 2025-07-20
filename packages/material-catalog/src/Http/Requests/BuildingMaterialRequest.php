<?php

namespace Packages\MaterialCatalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildingMaterialRequest extends FormRequest
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
        $materialId = $this->route('material')?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('material_categories', 'id')
                    ->where('store_id', $storeId)
                    ->where('is_active', true),
            ],
            'primary_unit_id' => [
                'required',
                'integer',
                Rule::exists('material_units', 'id')
                    ->where('store_id', $storeId)
                    ->where('is_active', true),
            ],
            'material_code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('building_materials')
                    ->where('store_id', $storeId)
                    ->ignore($materialId),
            ],
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('building_materials')
                    ->where('store_id', $storeId)
                    ->ignore($materialId),
            ],
            'description' => 'nullable|string|max:1000',
            'short_description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'origin_country' => 'nullable|string|max:100',
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('building_materials')
                    ->where('store_id', $storeId)
                    ->ignore($materialId),
            ],
            'qr_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('building_materials')
                    ->where('store_id', $storeId)
                    ->ignore($materialId),
            ],
            'weight_per_unit' => 'nullable|numeric|min:0|max:999999.999',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'is_hazardous' => 'boolean',
            'storage_requirements' => 'nullable|array',
            'quality_standards' => 'nullable|array',
            'certifications' => 'nullable|array',
            'expiry_date' => 'nullable|date|after:today',
            'shelf_life_days' => 'nullable|integer|min:1|max:36500',
            'requires_quality_check' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_serial_numbers' => 'boolean',
            'track_batch_numbers' => 'boolean',
            'technical_specs' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'internal_notes' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'danh mục',
            'primary_unit_id' => 'đơn vị tính chính',
            'material_code' => 'mã vật liệu',
            'name' => 'tên vật liệu',
            'description' => 'mô tả',
            'short_description' => 'mô tả ngắn',
            'brand' => 'thương hiệu',
            'model' => 'model',
            'origin_country' => 'xuất xứ',
            'barcode' => 'mã vạch',
            'qr_code' => 'mã QR',
            'weight_per_unit' => 'khối lượng/đơn vị',
            'dimensions.length' => 'chiều dài',
            'dimensions.width' => 'chiều rộng',
            'dimensions.height' => 'chiều cao',
            'is_hazardous' => 'vật liệu nguy hiểm',
            'storage_requirements' => 'yêu cầu bảo quản',
            'quality_standards' => 'tiêu chuẩn chất lượng',
            'certifications' => 'chứng nhận',
            'expiry_date' => 'ngày hết hạn',
            'shelf_life_days' => 'hạn sử dụng (ngày)',
            'requires_quality_check' => 'yêu cầu kiểm tra chất lượng',
            'is_active' => 'trạng thái hoạt động',
            'is_featured' => 'nổi bật',
            'track_serial_numbers' => 'theo dõi số serial',
            'track_batch_numbers' => 'theo dõi số lô',
            'technical_specs' => 'thông số kỹ thuật',
            'tags' => 'thẻ tag',
            'internal_notes' => 'ghi chú nội bộ',
            'images' => 'hình ảnh',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.exists' => 'Danh mục không tồn tại hoặc không hoạt động.',
            'primary_unit_id.required' => 'Đơn vị tính chính là bắt buộc.',
            'primary_unit_id.exists' => 'Đơn vị tính không tồn tại hoặc không hoạt động.',
            'material_code.required' => 'Mã vật liệu là bắt buộc.',
            'material_code.unique' => 'Mã vật liệu đã tồn tại trong cửa hàng này.',
            'material_code.regex' => 'Mã vật liệu chỉ được chứa chữ cái in hoa, số, dấu gạch ngang và gạch dưới.',
            'name.required' => 'Tên vật liệu là bắt buộc.',
            'name.unique' => 'Tên vật liệu đã tồn tại trong cửa hàng này.',
            'name.max' => 'Tên vật liệu không được vượt quá 200 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'short_description.max' => 'Mô tả ngắn không được vượt quá 500 ký tự.',
            'brand.max' => 'Thương hiệu không được vượt quá 100 ký tự.',
            'model.max' => 'Model không được vượt quá 100 ký tự.',
            'origin_country.max' => 'Xuất xứ không được vượt quá 100 ký tự.',
            'barcode.unique' => 'Mã vạch đã tồn tại trong cửa hàng này.',
            'qr_code.unique' => 'Mã QR đã tồn tại trong cửa hàng này.',
            'weight_per_unit.numeric' => 'Khối lượng/đơn vị phải là số.',
            'weight_per_unit.min' => 'Khối lượng/đơn vị phải lớn hơn hoặc bằng 0.',
            'weight_per_unit.max' => 'Khối lượng/đơn vị quá lớn.',
            'dimensions.length.numeric' => 'Chiều dài phải là số.',
            'dimensions.width.numeric' => 'Chiều rộng phải là số.',
            'dimensions.height.numeric' => 'Chiều cao phải là số.',
            'expiry_date.date' => 'Ngày hết hạn không hợp lệ.',
            'expiry_date.after' => 'Ngày hết hạn phải sau ngày hôm nay.',
            'shelf_life_days.integer' => 'Hạn sử dụng phải là số nguyên.',
            'shelf_life_days.min' => 'Hạn sử dụng phải ít nhất 1 ngày.',
            'shelf_life_days.max' => 'Hạn sử dụng không được vượt quá 100 năm.',
            'tags.*.max' => 'Mỗi thẻ tag không được vượt quá 50 ký tự.',
            'internal_notes.max' => 'Ghi chú nội bộ không được vượt quá 1000 ký tự.',
            'images.max' => 'Không được tải lên quá 10 hình ảnh.',
            'images.*.image' => 'File phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'material_code' => strtoupper($this->material_code),
            'is_hazardous' => $this->boolean('is_hazardous', false),
            'requires_quality_check' => $this->boolean('requires_quality_check', false),
            'is_active' => $this->boolean('is_active', true),
            'is_featured' => $this->boolean('is_featured', false),
            'track_serial_numbers' => $this->boolean('track_serial_numbers', false),
            'track_batch_numbers' => $this->boolean('track_batch_numbers', false),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateBusinessRules($validator);
        });
    }

    /**
     * Validate business rules.
     */
    private function validateBusinessRules($validator): void
    {
        // If material is hazardous, it should require quality check
        if ($this->is_hazardous && !$this->requires_quality_check) {
            $validator->errors()->add(
                'requires_quality_check',
                'Vật liệu nguy hiểm bắt buộc phải kiểm tra chất lượng.'
            );
        }

        // If tracking serial numbers, should also track batch numbers
        if ($this->track_serial_numbers && !$this->track_batch_numbers) {
            $validator->errors()->add(
                'track_batch_numbers',
                'Khi theo dõi số serial, nên theo dõi cả số lô.'
            );
        }

        // Validate expiry date vs shelf life
        if ($this->expiry_date && $this->shelf_life_days) {
            $calculatedExpiry = now()->addDays($this->shelf_life_days);
            if ($this->expiry_date > $calculatedExpiry) {
                $validator->errors()->add(
                    'expiry_date',
                    'Ngày hết hạn không phù hợp với hạn sử dụng đã nhập.'
                );
            }
        }
    }
}
