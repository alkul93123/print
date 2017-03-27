<?php

namespace backend\components\document\engine;

use backend\components\document\engine\TemplateInterface;
use yii\base\InvalidConfigException;
use yii\base\Component;
use Yii;
use yii\helpers\Url;


/**
 * 	Класс позволяет создать документ по шаблону.
 *
 * 	По умолчанию все шаблоны лежат в "@backend/components/document/templates",
 * 	но можно переопределить путь к директории с шаблонами, для этого нужно
 * 	переопределить переменную $path
 * 	Например:
 * 	protected $path = '@frontend/components/docs/templates';
 *
 * 	По умолчанию название шаблона формируется из названия класса, разделенного
 * 	по заглавным буквам, и добаленой строкой _template. Т.е. если у нас класс
 * 	InvoiceTemplate, то название шаблона будет invoice_template_template.php,
 * 	но название шаблона можно переопределить, для этого достаточно изменить
 * 	значение переменной $templateName.
 * 	Например:
 * 	protected $templateName = "someName";
 * 	Важно! Название шаблона указыватеся БЕЗ расширения ".php".
 *
 * 	Метод beforRender позволяет изменить объект, или добавить кастомные параметры
 * перед рендерингом шаблона. Для этого нужно переопределить метод beforeRender.
 *
 * 	Например:
 * 		public function beforeRender($object)
 * 		{
 * 			foreach($object->products as &$product) {
 * 				$product->price = round($product->price, 2);
 * 			}
 *
 *         $object->total_price = number_format($object->total_price, 2, ".", " ");
 *         $this->pathStamp = Yii::getAlias('@web/images/print.png'); // В шаблоне
 *         можно обратиться к этому свойству как к свойству объекта $template.
 *
 * 			return $object;
 * 		}
 * 	Важно! Метод должен возвращать объект.
 *
 * Объект в шаблоне доступен в переменной $object, так же можно определить
 * кастомные методы и свойства текущего класса, которые так же будут доступны в
 * шаблоне, как свойства/методы объекта $template.
 * Например:
 * 		public function testFunc($variable)
 * 		{
 * 			return $variable * 2;
 * 		}
 *
 * В шаблоне можно обраться к этому методу нарпимер так:
 * $template->testFunc($object->total_price);
 *
 */
abstract class BaseTemplate implements TemplateInterface {

    /**
     *   @var string, путь к шаблону
     */
    protected $path;

    /**
     *   @var string, название шаблона
     */

    protected $templateName;

    /**
     *   @var boolean, указывает на то что шаблон будет с печатью
     */
    protected $stamp = FALSE;

    /**
     *   @var boolean, указывает на то что шаблон будет с подписью
     */
    protected $signature = FALSE;

    /**
     *   @var array, Место хранения перегружаемых данных.
     */
    protected $data = array();

    function __construct($path = NULL)
    {
        if ($this->path === NULL) {
            $this->path = Yii::getAlias('@backend/components/document/templates');
        } else {
            $this->path = Yii::getAlias($this->path);
        }
    }


    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * @inheritdoc
     *
     * 	@param void
     * 	@return string.
     */
    public function getTemplateName()
    {
        preg_match_all('/[A-Z][^A-Z]*/', get_class($this), $results);
        $fileName = implode("_", $results[0]);

        return mb_strtolower($fileName . "_template");
    }

    /**
     * @inheritdoc
     *
     * 	@param void
     * 	@return string.
     */
    public function getTemplate()
    {

        if ($this->templateName == null) {
            $this->templateName = $this->getTemplateName();
        }

        $template = $this->path . "/" . $this->templateName . ".php";

        if (file_exists($template)) {
            return $template;
        } else {
            throw new InvalidConfigException('Template "' . $template . '" doesn`t exists.');
        }
    }

    /**
     * @inheritdoc
     *
     * 	@param $object object/array объект или массив объуетов, который передается в шаблон
     * 	@return string/array
     */
    public function createDocument($object)
    {
        $template = $this->getTemplate();
        $object = $this->beforeRender($object);

        return $this->render($template, $object);
    }

    /**
     * @inheritdoc
     *
     * 	@param $collection array
     * 	@return array
     */
    public function createDocuments($collection)
    {
        $template = $this->getTemplate();

        foreach ($collection as $object) {
            $object = $this->beforeRender($object);
            $documents[] = $this->render($template, $object);
        }

        return $documents;
    }

    /**
     * 	Метод позволяет что то сделать с объектом, перед тем как передать в
     * 	шаблон
     *
     * 	@param object
     * 	@return object.
     */
    public function beforeRender($object)
    {
        return $object;
    }

    /**
     * 	Метод рендерит шаблон, и подставляет значения, возвращает строку, с
     * 	шаблоном и подставлеными значениями
     *
     * 	@param $template string
     * 	@param $object object
     *
     * 	@return string
     */
    protected function render($template, $object)
    {
        return Yii::$app->getView()->renderFile($template, [
                    'object' => $object,
                    'template' => $this,
        ]);
    }

    /**
     * Конвертирует в base64. Нужно для сохранения в pdf
     *
     * @param $img - путь к изображению
     * @return src изображения
     */
    public function convertImageToBase64($img)
    {
        // return Url::base(true) . $img;
        $imageSize = getimagesize(Url::base(true) . $img);
        $imageData = base64_encode(file_get_contents(Url::base(true) . $img));
        $imageSrc = "data:image/png;base64,{$imageData}";
        return $imageSrc;
    }

}
