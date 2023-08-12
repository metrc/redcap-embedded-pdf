<?php
namespace METRC\EmbeddedPDF;

use ExternalModules\AbstractExternalModule;
use DateTimeRC;
use Files;
use Form;
use RCView;
use REDCap;

class EmbeddedPDF extends AbstractExternalModule
{
    public function hook_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        $dataDictionary = REDCap::getDataDictionary($project_id, 'array');
        $fields = $this->findActionTag($dataDictionary);
        foreach($fields as $field) {
            $tempName = $pdf_data = $html = NULL;  // Initialize variables

            $params = $this->getTagParams($dataDictionary[$field]['field_annotation']);
            $tempName = tempnam(EDOC_PATH, 'EmbeddedPDF') .  rand(). '.pdf';
            $pdf_data = REDCap::getPDF($record, $params[1], $params[0], 'false', $params[2], true);
            file_put_contents($tempName, $pdf_data);
            $html = '<iframe src="//' . SERVER_NAME . $tempName . '" width="650" height="700"></iframe>';

            $this->writeJavaScript($field, $html);
        }

    }

    protected function findActionTag($dictionary) {
        $fields = [];
        foreach ($dictionary as $field => $metadata) {
            if (($metadata['field_annotation'])  && (substr(trim($metadata['field_annotation']), 0, 12) == '@EMBEDDEDPDF')) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    protected function writeJavaScript($field, $html) {
        print("<script>$('#$field-tr td:last').append('" . ($html) . "');</script>");
    }

    protected function getTagParams($field_annotation) {
        $params = [];
        $tag = substr(trim($field_annotation), 13);
        $params = explode(':', $tag);

        return $params;
    }


}