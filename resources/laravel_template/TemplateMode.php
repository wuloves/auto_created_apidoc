<?php

namespace App\Model\Common;

use Hyperf\Database\Model\SoftDeletes;

class TemplateMode extends Model
{

    use SoftDeletes;
    protected $datas = ['deleted_at'];
    protected $fillable = ['{$fillable}'
    ];
    public $seAttribute;

    public $casts = ['{$castsText}'
    ];

}
