<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;
	
	protected $fillable = ['guid', 'title', 'link', 'description', 'pub_date', 'author', 'image',];
}
