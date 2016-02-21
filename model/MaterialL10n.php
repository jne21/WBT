<?php

namespace WBT;

use common\L10n;
use common\Registry;
use WBT\LocaleManager;

class MaterialL10n extends L10n {
    const
        DB = 'db',
        TABLE = 'material_l10n'
    ;

    function loadDataFromArray($localeId, $data)
    {
        $this
            ->set('fileName', $data['file_name'], $localeId)
            ->set('originalFileName', $data['original_file_name'], $localeId)
            ->set('mimeType', $data['mime_type'], $localeId);
    }

    function save()
    {
        foreach(array_keys($this->getLocales()) as $localeId) {
            if ($this->get('uploadedFileName', $localeId)) {
                $this->saveFile($localeId);
            }
            $data[$localeId] = [
                'file_name'          => $this->get('fileName', $localeId),
                'original_file_name' => $this->get('originalUploadedFileName', $localeId),
                'mime_type'          => $this->get('mimeType', $localeId),
            ];
        }
        $this->saveData($data);
    }

    function prepareToReceiveFile($uploadedFileName, $originalFileName, $localeId)
    {
        if (file_exists($uploadedFileName)) {
            $this
                ->set('uploadedFileName', $uploadedFileName, $localeId)
                ->set('originalUploadedFileName', $originalFileName, $localeId);
        }
    }
    
    function saveFile($localeId)
    {
        $registry = Registry::getInstance();
        $extension = pathinfo($this->get('originalUploadedFileName', $localeId), PATHINFO_EXTENSION);
        $fileName = sha1(microtime().rand()) . '.' . $extension;
        $materialPath = $registry->get('material_path');
        $fileSpec = $materialPath . $fileName;
        if (rename($this->get('uploadedFileName', $localeId), $fileSpec)) {
            if ($this->get('originalFileName', $localeId) != $this->get('originalUploadedFileName', $localeId)) {
                @unlink($materialPath . $this->get('fileName', $localeId));
            }
            $this
                ->set('fileName', $fileName, $localeId)
                ->set('originalFileName', $this->get('originalUploadedFileName', $localeId), $localeId)
                ->set('mimeType', mime_content_type($fileSpec), $localeId);
        }
    }

    static function deleteAll($parentId)
    {
        $l10n = new self($parentId);
        $materialPath = Registry::getInstance()->get('material_path');
        foreach (array_keys($l10n->getLocales()) as $localeId) {
            @unlink($materialPath . $l10n->get('fileName', $localeId));
        }
        parent::deleteAll($parentId);
    }
            
    function getLocales()
    {
        return LocaleManager::getLocales();
    }
}
