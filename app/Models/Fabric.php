<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Fabric extends Authenticatable
{
    use HasFactory, HasApiTokens;
  
    
    protected $table = 'fabric';
}
