<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class TblRqForm extends Authenticatable
{
    use HasFactory, HasApiTokens;
  
    
    protected $table = 'tbl_rq_form';
}
