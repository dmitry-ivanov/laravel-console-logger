<?php

use Illuminate\Database\Eloquent\Model;

class MyCustomNotification extends Model
{
    protected $fillable = [
        'level', 'level_name', 'message', 'context', 'custom-field-1', 'custom-field-2', 'custom-field-foo',
    ];
}
