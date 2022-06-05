<?php

namespace Illuminated\Console\Tests\App;

use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'level',
        'level_name',
        'message',
        'context',
        'custom-field-1',
        'custom-field-2',
        'custom-field-foo',
    ];
}
