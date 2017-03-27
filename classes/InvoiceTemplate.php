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
        $this->totalPriceStr = $this->num2str($object->total_price);

        $this->totalPriceStr = $this->num2str($object->total_price);
        if ($object->organization->getFiles()->where(['=', 'type', 1])->one()) {
            $this->pathStamp = stristr($object->organization->getFiles()
                            ->where(['=', 'type', 1])->one()->location, '/uploads');
        }
        if ($object->organization->getFiles()->where(['=', 'type', 2])->one()) {
            $this->pathSignature = stristr($object->organization->getFiles()
                            ->where(['=', 'type', 2])->one()->location, '/uploads');
        }

        $this->nds = $this->calculateNds($object);
        return $object;
    }

}
