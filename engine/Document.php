<?php

namespace backend\components\document\engine;

use Yii;
use yii\base\InvalidParamException;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 *	Класс управляет сохранением/печатью документов
 *
 *	Краткое описание.
 *
 *	Как работает этот класс.
 *	Что бы создать шаблон, выполняем консольную команду
 *	"php yii document-template/create"
 *
 *	Эта команда имеет один обязательный параметр - имя класса, и три не
 *	обязательных. Подробнее в "php yii help document-template/create"
 *	Команда сформирует 2 файла, файл шаблона и файл класса шаблона.
 *
 *	Файл шаблона - это обычная html разметка, в которой доступен объект, который
 *	мы передадим в метод. Будет находиться в backend/components/document/templates
 *
 *	Файл класса - класс, который отвечает за создание документа по шаблону. Этот класс
 *	наследуется от абстрактного класса backend/components/document/engine/BaseTemplate.php
 *
 *	Если нам нужно распечатать или сохранить какой то документ
 *	(счет на оплату/документ ПКО/документ заказа) в нужном нам контроллере
 *	подключаем класс Document и класс, сформированый командой "php yii document-template/create"
 *	(предположим InvoiceTemplate)
 *
 *	Создаем объект класса Document:
 *	$document = new Document();
 *
 *	Загружаем шаблон документа:
 *	$document->loadTemplate(new InvoiceTemplate)
 *
 *	В шаблон можно передать параметры, для этого используется метод withParams.
 *	$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *
 *	Далее передаем в метод createDocument() объект, или коллекцию объектов
 *	$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *						->createDocument($model)
 *
 *	Если нужно отправить на печать в браузере вызываем метод printOnBrowser().
 *	Например:
 *	$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *						->createDocument($model)->printOnBrowser();
 *
 *	Если нужно сохранить в формате doc или pdf, вызываем метод saveAs(), и далее формат
 *	и затем, если нужно отдать пользователю в браузер, метод sendResponse()
 *	Например:
 *	$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *						->createDocument($model)->saveAs()->doc()->sendResponse();
 *
 *	В метод saveAs мы можем передать название файла, который будет формироваться.
 *	Например:
 *	$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *				->createDocument($model)->saveAs('Счет от 12.12.2012')->doc()->sendResponse();
 *	В итоге пользователь получит файл с названием "Счет от 12.12.2012.doc"
 *
 *	Если же не нужно отправлять файл пользователю, а что то еще сделать в контроллере:
 *	$document->createDocument($model)->saveAs()->doc()->get();
 *
 *	Примеры:
 *	```php
 *	<?php
 *
 *	namespace backend\modules\document\controllers;
 *	# Some code...
 *	use backend\components\document\engine\Document;
 *	use backend\components\document\classes\InvoiceTemplate;
 *
 *	class InvoiceController extends Controller
 *	{
 *		# Some code...
 *
 *		public function actionPrint($id) {
 *			$model = $this->findModel($id);
 *			$document = new Document();
 *			$document->loadTemplate(new InvoiceTemplate)->withParams('stamp')
 *                        ->createDocument($model)->printOnBrowser();
 *		}
 *
 *		public function actionSave($type = 'pdf') {
 *			$invoices = DocumentInvoice::find()->all();
 *			$document = new Document();
 *			$document->loadTemplate(new InvoiceTemplate);
 *			if($type == 'pdf') {
 *				$document->createDocument($invoices)->saveAs()->pdf()->sendResponse();
 *			} else {
 *				$document->createDocument($invoices)->saveAs()->doc()->sendResponse();
 *			}
 *		}
 *	}
 *	```
 *	@since 1.0
 */

class Document
{
	/**
	*	Разделитель страниц при сохранении в *.doc
	*/
	const DELIMITER_DOC = '<br style="page-break-before: always">';

	/**
	*	Разделитель страниц при сохранении в *.pdf
	*/
	const DELIMITER_PDF = '<table width="100%" cellpadding="1" cellspacing="0" border="0" style="page-break-inside: avoid; page-break-before: always"></table>';

	/**
	*	Разделитель страниц при выводе на печать
	*/
	const DELIMITER_PRINT = '<div class="end_line"></div>';

	/**
	*	Стили для разделителя страниц при печати.
	*/
	const CSS = "<!DOCTYPE html><link href='/css/print.css' rel='stylesheet'></link>";

	/**
	*	@var object экземпляр класса BaseTemplate.
	*/
	private $_template;

	/**
	*	@var string результирующая строка.
	*/
	private $_document;

	/**
	*	@var array массив сформированых документов по шаблону.
	*/
	protected $documents = NULL;

	/**
	*	@var string имя сохраняемого файла, при выборе сохранения в .doc/pdf
	*/
	protected $attachmentName;

	/**
	*	@var boolean указывает на то что печатать с разделениям по страницам или нет.
	*/
	protected $delimiter = true;

	/**
	 * Метод загружает шаблон
	 */
	public function loadTemplate(BaseTemplate $template)
	{
		$this->_template = $template;
		return $this;
	}

	/**
	* 	Создает документ
	*
	*	@param $object object|array
	*	@return string
	*/
	public function createDocument($data)
	{

		if (is_array($data)) {
			$this->documents = $this->_template->createDocuments($data);
		} elseif(is_object($data)) {
			$this->_document = $this->_template->createDocument($data);
		} else {
			throw new InvalidParamException('Method createDocument() allow only object|array');
		}

		return $this;
	}

	/**
	*	Метод указывает что мы будем сохранять документ, и принимает один
	*	параметр - имя файла. Имя файла указывается без расширения.
	*	По умолчанию имя файла - journal
	*
	*	@param $fileName string
	*	@return object
	*/
	public function saveAs($fileName = 'journal')
	{
		if(! is_string($fileName)) {
			throw new InvalidParamException('Method saveAs() allow only string');
		}

		if (empty($fileName)) {
			$fileName = 'journal';
		}

		$this->attachmentName = $fileName;
		return $this;
	}

	/**
	*	Метод позволяет сформировать документ в формате doc.
	*	На самом деле, это хак, потому что метод не формирует полноценный *.doc
	*	документ, а всего лишь сохранят html с расширением .doc
	*	Затем если открыть этот файл например в ms word 2007 то word сам
	*	преобразует в нормальный формат. Аналогично и с LibreOffice.
	*
	*	:TODO: проверить в OpenOffice
	*
	*	@param $fileName string имя формируемого файла
	*	@return object
	*/
	public function doc()
	{
		$this->attachmentName .= ".doc";

		if ($this->documents !== NULL) {
			foreach ($this->documents as $document) {
				$this->_document = $this->_document . self::DELIMITER_DOC . $document;
			}
		}
		return $this;
	}

	/**
	*	Метод позволяет сформировать документ в формате pdf.
	*
	*	@param string $customPrintStylesheet - пользовательский файл css для печати
	*	@return object
	*/
	public function pdf($customPrintStylesheet = '')
	{
		$this->attachmentName .= ".pdf";
		if ($this->documents !== NULL) {
			foreach ($this->documents as $key => $document) {
				if ($key == 0) {
					$this->_document = $document;
					continue;
				}
				$this->_document = $this->_document . self::DELIMITER_PDF . $document;
			}
		}

		if (!is_string($customPrintStylesheet) || $customPrintStylesheet == '') {
			$customPrintStylesheet = self::CSS;
		}

		$this->_document = $customPrintStylesheet . $this->_document;

		$pdf = new Dompdf();

		$pdf->set_option('defaultFont', 'lucidagrande');
		$pdf->set_option('allow_url_fopen', 'true');
		$pdf->setPaper('A4', $this->getOrientationPage());
		$pdf->loadHtml($this->_document, "UTF-8");

		$pdf->render();
		$this->_document = $pdf->output();

		return $this;
	}

	/**
	*	Метод позволяет сформировать документ в и отправить в браузер, затем
	*	постредством js отправить на печать из браузера.
	*
	*	@param string $customPrintStylesheet - пользовательский файл css для печати
	*	@return \yii\web\Response
	*/

	public function printOnBrowser($customPrintStylesheet = '')
	{

		if ($this->documents !== NULL) {
			foreach ($this->documents as $key => $document) {
				if ($key == 0) {
					$this->_document = $document;
					continue;
				}
				$this->_document = $this->_document . self::DELIMITER_PRINT . $document;
			}
		}

		if (!is_string($customPrintStylesheet) || $customPrintStylesheet == '') {
			$customPrintStylesheet = self::CSS;
		}

		if ($this->getOrientationPage() == 'landscape') {
			$customPrintStylesheet .= "<style> @media print{@page {size: landscape}} </style>";
		}

		Yii::$app->response->content = $customPrintStylesheet . "<script>window.print()</script>" . $this->_document;

		return Yii::$app->response->send();
	}

	/**
	 * Метод позволяет отправлять на печать/сохраннения документов без разделителя
	 * страниц. Это может потребовать при печати ценников, или прайс листа.
	 *
	 * @param void
	 * @return this;
	 */
	public function withoutDelimiter()
	{
		$this->delimiter = false;
		return $this;
	}

	/**
	*	Метод отправляет ответ сервера непосредственно из класса, т.е.
	*	нам не нужно передавать во вьюху или куда либо еще что бы отрендерить,
	*	в методе контроллера достаточно вызывать метод sendResponse();
	*
	*	@param void
	*	@return \yii\web\Response
	*/
	public function sendResponse()
	{
		return Yii::$app->response->sendContentAsFile($this->_document, $this->attachmentName);
	}

	/**
	*	Метод позволяет получить строку/ссылку на файл, что бы потом с ним что то
	*	сделать, например, отправить ссылку или файл почтой
	*
	*	@param void
	*	@return string
	*/
	public function getDocument()
	{
		return $this->_document;
	}

	/**
	*	Метод позволяет добавить произвольные параметры в шаблон. Эти параметры
	*	будут доступны в шаблоне как свойства объекта $template. Будет полезно
	*	использовать при условиях, например передаем параметр stamp, в шаблоне
	*	делаем условие:
	*	if ($template->stamp)
	*		# вывод изображения печати
	*
	*	@param mixed $params type string, неограниченой количество параметров типа
	*	string
	*	@return object текущий обьект
	*/
	public function withParams(...$params)
	{
		foreach ($params as $param) {
			$this->_template->$param = TRUE;
		}
		return $this;
	}

	/**
	*	Возможно, когда нибудь потребуется реализовать какое либо хранилище,
	*	где будут храниться файлы, этот метод позволит сохранить сформированый
	*	документ на сервере, в указаной директории
	*
	*	Для сохранения в .doc можно будет просто сохранить документ с
	*	расширением .doc, не очень честно, зато и не очень ресурсоемко.
	*	MS WORD > 2003 открывает прекрасно. Но нужно учитывать что картинки так
	*	не сохраняются
	*
	*	Для сохранения в pdf подключить и воспользоваться библиотекой DOMPDF
	*
	*	@param $path string путь к директрории, где будет сохранен файл
	*
	*	@return object
	*/

	public function onServer($path)
	{
		# code...
	}

	/**
	 * Получаем оринтацию страницы в зависимости от документа (альбонмная или обычная)
	 *
	 * @param void
	 * @return string
	 */
	public function getOrientationPage()
	{
		return $this->_template->orientation;
	}
}
