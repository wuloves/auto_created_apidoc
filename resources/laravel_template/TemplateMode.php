<?php

namespace App\Model\Common;

use Hyperf\Database\Model\SoftDeletes;

class TemplateMode extends Model
{
    use SoftDeletes;
    protected $fillable = ['{$fillable}'
    ];
    protected $datas = ['deleted_at'];
    public $seAttribute;

    public $casts = ['{$castsText}'
    ];

}
