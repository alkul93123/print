<?php

namespace console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * По сути это просто консольный контроллер для yii2. 
 * Создание шаблонов, для печати/сохранения документов
 *
 * Команда создает 2 файла:
 * Класс шаблона и сам шаблон.
 */
class DocumentTemplateController extends Controller
{
    /**
     * @var string Название шаблона
     *
     * По дефолту название шаблона формируется из названия класса, которое
     * разбивается по заглавным буква и добавляется строка _template, например
     * если мы генерируем класс InvoiceTemplate то название шаблона
     * будет invoice_template_template.php
     *
     * Внимание! Если используете свое название шаблона, указывать название
     * нужно без ".php"
     */
    public $templateName;

    /**
     * @var string Путь к директории с шаблонами
     *
     * Внимание! Путь к файлу указыватеся без последнего "/"
     * Внимание! Перед тем как изменить путь, необходимо проверить существование
     * директории, к которой указываете путь. Т.е. если мы хотим выбрать
     * директорию @frontend/components/document/templates, нужно проверить
     * что эта директория существует. Иначе получим "failed to open stream: No such file or directory"
     */
    public $pathTemplate = '@backend/components/document/templates';

    /**
     * @var string Путь к директории с классами
     *
     * Внимание! Перед тем как изменить путь, необходимо проверить существование
     * директории, к которой указываете путь. Т.е. если мы хотим выбрать
     * директорию @frontend/components/document/classes, нужно проверить
     * что эта директория существует. Иначе получим "failed to open stream: No such file or directory"
     */
    public $pathClass = '@backend/components/document/classes';

    /**
     * @var string шаблон для генерации кода
     */
    public $templateFile = 'console/views/documentTempalateClass.php';

    /**
     * Генератор кода шаблона и класса для печати/сохранения
     *
     * Генерируется шаблон класса, в директории /backend/components/doucment/classes.
     * Класс наследуется от backend/components/document/engine/BaseTemplate
     *
     * Так же генерируется шаблон, в директории /backend/components/doucment/templates
     *
     * @param string $name Название класса, который сгенерируется
     */
    public function actionCreate($className)
    {
        $file = Yii::getAlias($this->pathClass) . DIRECTORY_SEPARATOR . $className . '.php';
        $fileTemplate = Yii::getAlias($this->pathTemplate) . DIRECTORY_SEPARATOR . $this->getTemplateName($className);
        if ($this->confirm("Create new template '$file'?")) {
            $content = $this->generateTemplateSourceCode([
                'pathTemplate'  =>  $this->pathTemplate,
                'templateName'  =>  $this->templateName,
                'className'     =>  $className,
            ]);
            file_put_contents($file, $content);
            file_put_contents($fileTemplate, '');
            $this->stdout("New class created successfully.\n", Console::FG_GREEN);
        }
    }


    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['templateName', 'pathTemplate', 'pathClass']
        );
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(),[
            't' =>  'templateName',
            'p' =>  'pathTemplate',
            'c' =>  'pathClass',
        ]);
    }

    protected function generateTemplateSourceCode($params)
    {
        return $this->renderFile(Yii::getAlias($this->templateFile), $params);
    }

    protected function getTemplateName($className)
    {
        if (!empty($this->templateName)) {
            return $this->templateName . ".php";
        }

        preg_match_all('/[A-Z][^A-Z]*/', $className, $results);
        $fileName = implode("_", $results[0]);
        return mb_strtolower($fileName . "_template.php");
    }

}
