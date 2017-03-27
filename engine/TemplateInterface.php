<?php

namespace backend\components\document\engine;

/**
 * 	TemplateInterface определяет общий интерфейс, как будет реализована генерация
 * 	шаблонов, и документов
 *
 * 	@since 1.0
 */
interface TemplateInterface {

    /**
     * 	Метод получает шаблон из файла.
     *
     * 	@return string
     */
    public function getTemplate();

    /**
     * 	Метод получает название шаблона. Название шаблона формируется
     * 	из названия класа + строка _template, объект которого мы используем
     * 	т.е. если мы используем объект класса InvoiceDocument то шаблон будет
     * 	называться invoice_document_template.html
     *
     * 	@return string
     */
    public function getTemplateName();

    /**
     * 	Метод формирует документ, после сформирования документа мы
     * 	можем сохранить этот документ в формате doc/pdf или отправить в браузер,
     * 	и вызвать печать из браузера.
     *
     * 	@param $object object
     * 	@return string
     */
    public function createDocument($object);

    /**
     * 	Метод перибирает коллекцию объектов, для каждого подставляя шаблон и
     * 	значения, и возвращает массив документов.
     *
     * 	@param array
     * 	@return array
     */
    public function createDocuments($collection);
}
