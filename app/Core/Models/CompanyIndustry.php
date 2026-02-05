<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyIndustry extends Model
{
    protected $table = 'company_industries';
    protected $fillable = ['name', 'label', 'sort_order'];
}
