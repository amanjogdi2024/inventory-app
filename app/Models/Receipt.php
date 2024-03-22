<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Receipt extends Authenticatable
{
    use HasFactory, HasApiTokens;
  
    
    protected $table = 'receipt';
}
