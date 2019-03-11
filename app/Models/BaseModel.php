<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;


class BaseModel extends Model
{
    /**
     * The rules that are mass assignable.
     * 
     * @var array
     */
    protected $rules = [];
    /**
     * The errors.
     * 
     * @var array
     */
    protected $errors = [];

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if ( $this->validate() )
        {
            return parent::save($options);
        }

        return false;
    }

    /**
     * Validation Method
     * 
     * @param array $data
     * @return bool
     */
    public function validate($data = [])
    {
        if ( !empty($data) )
            $this->fill($data);

        if ( empty($this->rules) )
            return true;

        // make a new validator object
        $validator = Validator::make($this->attributes, $this->rules);
        // check for failure
        if ( $validator->fails() ) {
            // set errors and return false
            $this->errors = $validator->errors();
            return false; 
        }

        // validation pass
        return true;
    }

    /**
     * Method to return the errors array.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}
