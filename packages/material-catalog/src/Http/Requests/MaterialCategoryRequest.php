<?php

namespace Packages\MaterialCatalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('material_categories', 'id')
                    ->where('store_id', $storeId)
                    ->where('is_active', true),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('material_categories')
                    ->where('store_id', $storeId)
                    ->ignore($categoryId),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'sort_order' => 'integer|min:0|max:999',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'parent_id' => 'danh mục cha',
            'code' => 'mã danh mục',
            'name' => 'tên danh mục',
            'description' => 'mô tả',
            'icon' => 'biểu tượng',
            'color' => 'màu sắc',
            'sort_order' => 'thứ tự sắp xếp',
            'is_active' => 'trạng thái hoạt động',
            'meta_title' => 'tiêu đề SEO',
            'meta_description' => 'mô tả SEO',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Danh mục cha không tồn tại hoặc không hoạt động.',
            'code.required' => 'Mã danh mục là bắt buộc.',
            'code.unique' => 'Mã danh mục đã tồn tại trong cửa hàng này.',
            'code.regex' => 'Mã danh mục chỉ được chứa chữ cái thường, số, dấu gạch ngang và gạch dưới.',
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
            'icon.max' => 'Biểu tượng không được vượt quá 50 ký tự.',
            'color.regex' => 'Màu sắc phải có định dạng hex (#FFFFFF hoặc #FFF).',
            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min' => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 0.',
            'sort_order.max' => 'Thứ tự sắp xếp không được vượt quá 999.',
            'meta_title.max' => 'Tiêu đề SEO không được vượt quá 200 ký tự.',
            'meta_description.max' => 'Mô tả SEO không được vượt quá 500 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower($this->code),
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->integer('sort_order', 0),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateHierarchy($validator);
            $this->validateNameUniqueness($validator);
        });
    }

    /**
     * Validate category hierarchy.
     */
    private function validateHierarchy($validator): void
    {
        if ($this->parent_id) {
            $categoryId = $this->route('category')?->id;
            
            // Prevent self-reference
            if ($categoryId && $this->parent_id == $categoryId) {
                $validator->errors()->add('parent_id', 'Danh mục không thể là cha của chính nó.');
                return;
            }

            // Check maximum depth (e.g., 3 levels)
            $parent = \Packages\MaterialCatalog\Models\MaterialCategory::find($this->parent_id);
            if ($parent && $parent->level >= 2) {
                $validator->errors()->add('parent_id', 'Không thể tạo danh mục con quá 3 cấp.');
                return;
            }

            // Prevent circular reference
            if ($categoryId && $this->wouldCreateCircularReference($categoryId, $this->parent_id)) {
                $validator->errors()->add('parent_id', 'Không thể tạo tham chiếu vòng trong cây danh mục.');
            }
        }
    }

    /**
     * Validate name uniqueness within same parent.
     */
    private function validateNameUniqueness($validator): void
    {
        $storeId = auth()->user()->current_store_id;
        $categoryId = $this->route('category')?->id;

        $exists = \Packages\MaterialCatalog\Models\MaterialCategory::query()
            ->where('store_id', $storeId)
            ->where('name', $this->name)
            ->where('parent_id', $this->parent_id)
            ->when($categoryId, fn($q) => $q->where('id', '!=', $categoryId))
            ->exists();

        if ($exists) {
            $parentName = $this->parent_id 
                ? \Packages\MaterialCatalog\Models\MaterialCategory::find($this->parent_id)?->name 
                : 'danh mục gốc';
            
            $validator->errors()->add(
                'name',
                "Tên danh mục đã tồn tại trong {$parentName}."
            );
        }
    }

    /**
     * Check if setting parent would create circular reference.
     */
    private function wouldCreateCircularReference(int $categoryId, int $parentId): bool
    {
        $current = \Packages\MaterialCatalog\Models\MaterialCategory::find($parentId);
        
        while ($current) {
            if ($current->id == $categoryId) {
                return true;
            }
            $current = $current->parent;
        }
        
        return false;
    }
}
