<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateStatusInput implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $possibleInputs;

    public function __construct($possibleInputs)
    {
        $this->possibleInputs = $possibleInputs;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach($this->possibleInputs as $possibleInput){
            if(strpos($value,$possibleInput)!== false){
                return true;
            }
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $possibleInputs = implode(", ",$this->possibleInputs);     
        return 'Status value must be positif, sembuh, or meninggal';
    }
}
