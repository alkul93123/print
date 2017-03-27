<?php

namespace backend\components\document\classes;

use backend\components\document\engine\BaseTemplate;
use Yii;
use yii\helpers\Url;

/**
 *  @inheritdoc
 */
class InvoiceTemplate extends BaseTemplate {

    function __construct()
    {
        parent::__construct();
    }

    public function beforeRender($object)
    {
        $this->nds = $this->calculateNds($object);
        return $object;
    }

}
