<?php

namespace App\Modules\Api\V1\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpectedSalary extends Model
{
    use HasFactory;

    protected $table = 'expected_salary';

    protected $fillable = ['name'];
}
