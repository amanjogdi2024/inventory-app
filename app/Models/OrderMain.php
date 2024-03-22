<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class OrderMain extends Authenticatable
{
    use HasFactory, HasApiTokens;
  
    protected $connection = 'second_mysql';
    
    protected $table = 'order_main';
}
