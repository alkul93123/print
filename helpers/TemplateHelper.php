<?php

namespace backend\components\document\helpers;

use yii\base\InvalidParamException;
use backend\components\document\classes\realization\CertificateOfCompliteWork;
use backend\components\document\classes\realization\SalesInvoice;
use backend\components\document\classes\realization\PackingList;
use backend\components\document\classes\realization\SalesReceipt;
use backend\components\document\classes\PkoTemplate;
use backend\components\document\classes\RkoTemplate;
use backend\components\document\classes\BillOfParcels;
use backend\components\document\classes\InvoiceTemplate;
use backend\components\document\classes\ReconciliationAct;
use backend\components\document\classes\DocumentReturn;
use backend\components\document\classes\order\Order;
use backend\components\document\classes\order\ContractOfSale;
use backend\components\document\classes\order\ContractOfInstallation;
use backend\components\document\classes\order\CommercialOffer;

class TemplateHelper {

    /**
     * 	Возвращает шаблон реализации по типу, всего 5 типов:
     * 	"act" - Акт выполненых работ
     * 	"pko" - Приходно кассовый ордер
     * 	"rko" - Расходно кассовый ордер
     * 	"ic"  - Расходная накладная
     * 	"pl"  - Товарная накладная
     * 	"sr"  - Товарный чек
     * 	"bop" - Счет-фактура
     *
     * 	@param $type string Тип шаблона реализации
     * 	@return object Класс реализации или ошибку, что такого класса не существует
     */
    public static function changeRealization($type)
    {
        switch ($type) {
            case 'act':
                return new CertificateOfCompliteWork;

            case 'pko':
                return new PkoTemplate;

            case 'rko':
                return new RkoTemplate;

            case 'ic':
                return new SalesInvoice;

            case 'pl':
                return new PackingList;

            case 'sr':
                return new SalesReceipt;

            case 'bop':
                return new BillOfParcels;

            default:
                $errorMsg = "Ivalid param value. Accepted only 'act', 'pko', 'rko', 'ic', 'pl', and 'sr'";
                throw new InvalidParamException($errorMsg);
        }
    }

    /**
     * 	Возвращает шаблон реализации по типу, пока 1 тип:
     *
     * 	"return" - Счет на оплату
     *
     * 	@param $type string Тип шаблона реализации
     * 	@return object Класс реализации или ошибку, что такого класса не существует
     */
    public static function changeReturn($type)
    {
        switch ($type) {
            case 'return':
                return new DocumentReturn;

            default:
                $errorMsg = "Ivalid param value. Accepted only 'return'";
                throw new InvalidParamException($errorMsg);
        }
    }

    /**
     * 	Возвращает шаблон реализации по типу, пока 1 тип:
     *
     * 	"reconciliation" - Акт сверки взаиморасчетов
     *
     * 	@param $type string Тип шаблона реализации
     * 	@return object Класс реализации или ошибку, что такого класса не существует
     */
    public static function changeReconciliation($type)
    {
        switch ($type) {
            case 'reconciliation':
                return new ReconciliationAct;

            default:
                $errorMsg = "Ivalid param value. Accepted only 'return'";
                throw new InvalidParamException($errorMsg);
        }
    }

    /**
     * 	Возвращает шаблон реализации по типу, всего 2 типа:
     *
     * 	"invoice" - Счет на оплату
     * 	"bop" - Счет-фактура
     *
     * 	@param $type string Тип шаблона реализации
     * 	@return object Класс реализации или ошибку, что такого класса не существует
     */
    public static function changeInvoice($type)
    {
        switch ($type) {
            case 'invoice':
                return new InvoiceTemplate;

            case 'bop':
                return new BillOfParcels;

            default:
                $errorMsg = "Ivalid param value. Accepted only 'invoice', 'bop'";
                throw new InvalidParamException($errorMsg);
        }
    }

    /**
     * 	Возвращает шаблон заказа по типу, всего 7 типов:
     * 	"order" - Заказ
     * 	"pko" - Приходно кассовый ордер
     *  "rko" - Расходно кассовый ордер
     * 	"cs"  - Договор купли-продажи
     * 	"ci"  - Договор на монтаж
     * 	"co"  - Коммерческое предложение
     *
     * 	@param $type string Тип шаблона реализации
     * 	@return object Класс реализации или ошибку, что такого класса не существует
     */
    public static function changeOrderTemplate($type)
    {
        switch ($type) {
            case 'order':
                return new Order;

            case 'pko':
                return new PkoTemplate;

            case 'rko':
                return new RkoTemplate;

            case 'cs':
                return new ContractOfSale;

            case 'ci':
                return new ContractOfInstallation;

            case 'co':
                return new CommercialOffer;

            case 'bop':
                return new BillOfParcels;

            default:
                $errorMsg = "Ivalid param value. Accepted only 'act', 'pko', 'rko', 'ic', 'pl', and 'sr'";
                throw new InvalidParamException($errorMsg);
        }
    }

    public static function getTemplateName($type)
    {
        switch ($type) {
            case 'act':
                return 'Акт выполненых работ';

            case 'pko':
                return 'Приходно кассовый ордер';

            case 'rko':
                return 'Расходно кассовый ордер';

            case 'ic':
                return 'Расходная накладная';

            case 'pl':
                return 'Товарная накладная';

            case 'sr':
                return 'Товарный чек';

            case 'bop':
                return 'Счет-фактура';

            case 'return':
                return 'Документ возврата';

            case 'invoice':
                return 'Счет на оплату';

            case 'order':
                return 'Заказ';

            case 'cs':
                return 'Договор купли-продажи';

            case 'ci':
                return 'Договор на монтаж';

            case 'co':
                return 'Коммерческое предложение';

            default:
                $errorMsg = "Ivalid param value. Accepted only 'act', 'pko', 'rko', 'ic', 'pl', and 'sr'";
                throw new InvalidParamException($errorMsg);
        }
    }
}
