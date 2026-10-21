<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Dados do Cliente
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'string', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
            'customer.document' => ['nullable', 'string', 'max:30'],
            'customer.password' => ['nullable', 'string', 'min:6'],

            // Endereço de Entrega
            'shipping_address' => ['required', 'array'],
            'shipping_address.recipient_name' => ['required', 'string', 'max:255'],
            'shipping_address.recipient_phone' => ['nullable', 'string', 'max:50'],
            'shipping_address.postal_code' => ['required', 'string', 'max:20'],
            'shipping_address.street' => ['required', 'string', 'max:255'],
            'shipping_address.number' => ['required', 'string', 'max:50'],
            'shipping_address.complement' => ['nullable', 'string', 'max:255'],
            'shipping_address.neighborhood' => ['required', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:255'],
            'shipping_address.state' => ['required', 'string', 'max:10'],
            'shipping_address.country' => ['nullable', 'string', 'max:10'],

            // Itens do Carrinho
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_uuid' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            // Pagamento e Observações
            'payment_method' => ['required', 'string', Rule::in(['pix', 'card', 'boleto', 'cash_on_delivery', 'manual'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer.name.required' => 'O nome do cliente é obrigatório.',
            'customer.email.required' => 'O e-mail do cliente é obrigatório.',
            'customer.email.email' => 'Informe um e-mail válido.',
            'shipping_address.postal_code.required' => 'O CEP de entrega é obrigatório.',
            'shipping_address.street.required' => 'A rua/logradouro é obrigatória.',
            'shipping_address.number.required' => 'O número do endereço é obrigatório.',
            'shipping_address.neighborhood.required' => 'O bairro é obrigatório.',
            'shipping_address.city.required' => 'A cidade é obrigatória.',
            'shipping_address.state.required' => 'O estado (UF) é obrigatório.',
            'items.required' => 'O carrinho não pode estar vazio.',
            'items.min' => 'Adicione pelo menos um produto ao pedido.',
            'items.*.product_uuid.required' => 'O identificador do produto é obrigatório.',
            'items.*.quantity.min' => 'A quantidade mínima por produto é 1.',
            'payment_method.required' => 'Selecione uma forma de pagamento.',
            'payment_method.in' => 'Forma de pagamento inválida.',
        ];
    }
}
