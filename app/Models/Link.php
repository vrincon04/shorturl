<?php

namespace App\Models;

class Link extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url', 'code'];

    /**
     * The rules that are mass assignable.
     * 
     * @var array
     */
    protected $rules = [
        'url' => 'required|url|max:255'
    ];

    public function histories()
    {
        return $this->hasMany(LinkHistory::class);
    }
}
