<?php

namespace App\Models;

class LinkHistory extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['link_id'];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
