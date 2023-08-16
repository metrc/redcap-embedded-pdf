<?php
/**
 * Implements the @EMBEDDEDPDF action tag
 * Author:  Paige Julianne Sullivan <psullivan@jhu.edu>, Johns Hopkins Bloomberg School of Public Health - METRC
 * Repo:   https://github.com/metrc/redcap-embedded-pdf
 */

namespace METRC\EmbeddedPDF;

use ExternalModules\AbstractExternalModule;
use DateTimeRC;
use Files;
use Form;
use RCView;
use REDCap;

class EmbeddedPDF extends AbstractExternalModule
{
    public $tag = '@EMBEDDEDPDF';

    public function hook_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        global $Proj;

        $dataDictionary = REDCap::getDataDictionary($project_id, 'array');

        $fields = $this->findActionTag($dataDictionary);

        foreach($fields as $field) {
            $tempName = $pdfData = $html = NULL;  // Initialize variables
            // get the action tag from the field annotation
            preg_match_all('/@EMBEDDEDPDF=([\S]+)/', $dataDictionary[$field]['field_annotation'], $matches);

            $params = explode(':', $matches[1][0]);

            /*
             * $params[] =
             * 0 = event_id or event_name
             * 1 = instrument
             * 2 = instance
             */

            if (!is_numeric($params[0])) {
                // do a lookup on the event name
                $params[0] = $Proj->getEventIdUsingUniqueEventName($params[0]);
            }


            if ($this->isFormEmpty($record, $params[1], $params[0])) {
                print("<script>$('#$field-tr').hide();</script>");
            } else {
                $tempName = EDOC_PATH . '/embeddedpdf_' . PROJECT_ID . '_' . $record . '_' . $params[0] . '_' . $params[1] . '_' . $params[2] . '.pdf';
                // delete the existing file if it exists
                unlink($tempName);

                $pdfData = REDCap::getPDF((int)$record, $params[1], (int)$params[0], 'false', (int)$params[2], true);

                file_put_contents($tempName, $pdfData);
                $url = SERVER_NAME . $this->getSystemSetting('edocs-web-path') . '/' . basename($tempName);
                $html = '<iframe src="//' . $url . '" width="800" height="800"></iframe>';

                print("<script>$('#$field-tr td:last').append('" . ($html) . "');</script>");
            }
        }
        print("<script>window.scrollTo(0, 0);</script>");
    }

    protected function findActionTag($dictionary) {
        $fields = [];
        foreach ($dictionary as $field => $metadata) {
            if (($metadata['field_annotation'])  &&
                (preg_match('/@EMBEDDEDPDF=([\S]+)/', $metadata['field_annotation']))
                && ($metadata['form_name'] == $_GET['page'])) {

                $fields[] = $field;
            }
        }
        return $fields;
    }


    protected function isFormEmpty($record, $instrument, $event_id) {
        $dataDictionary = REDCap::getDataDictionary(PROJECT_ID, 'array');

        foreach($dataDictionary as $field => $metadata) {
            if (($metadata['form_name'] == $instrument)) {
                $instrument_fields[] = $field;
            }
        }

        $data = REDCap::getData($this->getProjectId(), 'array', $record, $instrument_fields, $event_id);
        return empty($data);
    }


}