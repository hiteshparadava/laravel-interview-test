<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Prize;
use Illuminate\Support\Facades\Route;
use App\Http\Requests;

class Totalprobability implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param $param
     */
    public function __construct($param)
    {
        $this->prizesId = $param;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($this->prizesId != null)
        {
            $prize = Prize::where('id','!=',$this->prizesId)->get();
            $total = $prize->sum('probability');
            $remain_total=100-$total;
            if($total+$value>100)
            {
                $fail('You have yet to add '.$remain_total.'% to the prize' );
            }
        }
        else
        {
            $prize = Prize::get();
            $total = $prize->sum('probability');
            $remain_total=100-$total;
            if($total+$value>100)
            {
                $fail('You have yet to add '.$remain_total.'% to the prize' );
            }
        }
        
    }
}
