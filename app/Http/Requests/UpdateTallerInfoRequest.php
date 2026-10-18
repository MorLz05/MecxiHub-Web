<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTallerInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'min:2', 'max:120',
                // Solo letras, números, espacios y algunos signos seguros
                'regex:/^[\pL\pN\s\.\,\-\&\'\"\(\)\#\/\+]+$/u',
            ],
            'descripcion' => [
                'nullable', 'string', 'max:2000',
                // Bloquear etiquetas HTML y caracteres peligrosos
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'direccion' => [
                'required', 'string', 'max:300',
                'regex:/^[^\<\>\{\}]*$/u',
            ],
            'telefono' => [
                'required', 'string', 'max:20',
                // Solo dígitos, +, -, espacio, paréntesis
                'regex:/^[\d\s\+\-\(\)]{7,20}$/',
            ],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre contiene caracteres no permitidos (<, >, {, }).',
            'descripcion.regex' => 'La descripción contiene etiquetas o caracteres no permitidos.',
            'direccion.regex' => 'La dirección contiene caracteres no permitidos.',
            'telefono.regex' => 'El teléfono solo puede contener números, +, -, ( ) y espacios.',
        ];
    }

    /**
     * Sanitiza antes de validar (defensa en profundidad).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => $this->limpiarTexto($this->input('nombre')),
            'descripcion' => $this->limpiarTexto($this->input('descripcion')),
            'direccion' => $this->limpiarTexto($this->input('direccion')),
            'telefono' => preg_replace('/[^\d\s\+\-\(\)]/', '', (string) $this->input('telefono')),
        ]);
    }

    private function limpiarTexto($valor): string
    {
        if (!is_string($valor)) return '';

        // 1) Quitar etiquetas HTML y PHP
        $valor = strip_tags($valor);

        // 2) Quitar null bytes y caracteres de control
        $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $valor);

        // 3) Decodificar entidades y volver a escapar < > para que no lleguen como tags
        $valor = html_entity_decode($valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $valor = str_replace(['<', '>', '{', '}'], '', $valor);

        // 4) Normalizar espacios
        $valor = preg_replace('/\s+/u', ' ', trim($valor));

        return $valor;
    }
}

