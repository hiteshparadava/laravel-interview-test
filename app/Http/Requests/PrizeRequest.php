<?php

namespace App\Http\Requests;
use App\Http\Requests\Request;

use App\Models\Prize;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Totalprobability;

class PrizeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $prizeid = $this->route()->parameter('prize');
        return
            [
                'title' => 'required',
                'probability' => ['required','numeric','min:1','max:100',new Totalprobability($prizeid)],
            ];
    }
}
