<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WishlistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id|unique:wishlists,product_id,NULL,id,user_id,'.auth()->id()
        ];
    }
}