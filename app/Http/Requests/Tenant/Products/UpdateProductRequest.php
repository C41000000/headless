<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Products;

use App\Models\Tenant\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('slug') && empty($this->slug) && ! empty($this->name)) {
            $merge['slug'] = Str::slug($this->name);
        }

        if ($this->has('category_uuid')) {
            $categoryId = null;
            if (! empty($this->category_uuid)) {
                $categoryId = Category::where('uuid', $this->category_uuid)->value('id');
            }
            $merge['category_id'] = $categoryId;
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $id = is_object($product) ? $product->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($id)->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'promotional_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_uuid' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->has('promotional_price') && ! empty($this->input('promotional_price'))) {
                    $product = $this->route('product');
                    $price = $this->input('price') ?? ($product?->price);

                    if ($price !== null && (float) $this->input('promotional_price') >= (float) $price) {
                        $validator->errors()->add(
                            'promotional_price',
                            'O preço promocional deve ser menor que o preço original.'
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'slug.required' => 'O slug é obrigatório.',
            'slug.unique' => 'Já existe um produto com este slug.',
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um valor numérico válido.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'image_url.url' => 'A URL da imagem deve ser um endereço válido.',
            'category_id.exists' => 'A categoria selecionada não foi encontrada.',
        ];
    }
}
