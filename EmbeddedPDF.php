<?php
/**
 * Implements the @EMBEDDEDPDF action tag
 * Author:      Paige Julianne Sullivan <psullivan@jhu.edu>, Johns Hopkins Bloomberg School of Public Health - METRC
 * Repo:        https://github.com/metrc/redcap-embedded-pdf
 * Copyright:   2023 The Johns Hopkins University
 * License:     MIT
 */

namespace METRC\EmbeddedPDF;

use ExternalModules\AbstractExternalModule;
use REDCap;

class EmbeddedPDF extends AbstractExternalModule
{
    public $tag = '@EMBEDDEDPDF';

    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
        global $Proj;

        $dataDictionary = REDCap::getDataDictionary(PROJECT_ID, 'array');

        $fields = $this->findActionTag($dataDictionary, $instrument);

        foreach($fields as $field) {
            $tempName = $pdfData = $html = NULL;  // Initialize variables

            // get the action tag from the field annotation
            preg_match_all('/@EMBEDDEDPDF=([\S]+)/', $dataDictionary[$field]['field_annotation'], $matches);

            list($pdf_event_id, $pdf_instrument, $pdf_instance) = explode(':', $matches[1][0]);

            if (!is_numeric($pdf_event_id)) {
                // do a lookup on the event name
                $pdf_event_id = $Proj->getEventIdUsingUniqueEventName($pdf_event_id);
            }

            // for NON-longitudinal projects, get the first (and only) event_id
            if ($pdf_event_id == 0 || $pdf_event_id == '') {
                $pdf_event_id = $this->getFirstEventId();
            }

            switch ($pdf_instance) {
                case '[current-instance]':
                    $pdf_instance = $repeat_instance;
                    break;
                case '[first-instance]':
                    $pdf_instance = 1;
                    break;
                case '[last-instance]':
                    $pdf_instance = PHP_INT_MAX;
                    break;
                case '[all-instances]':
                case '':
                    $pdf_instance = 0;
                    break;
                case '[previous-instance]':
                    $pdf_instance = $repeat_instance - 1;
                    if ($pdf_instance == 0) {
                        $pdf_instance = PHP_INT_MAX;
                    }
                    break;
            }

            if ($this->isFormEmpty($record, $pdf_instrument, $pdf_event_id, $pdf_instance)) {
                print("<script>$('#$field-tr').hide();</script>" . PHP_EOL);
            } else {
                $tempName = EDOC_PATH . '/embeddedpdf_' . PROJECT_ID . '_' . $record . '_' . $pdf_event_id . '_' . $pdf_instrument . '_' . $pdf_instance . '.pdf';
                // delete the existing file if it exists
                unlink($tempName);

                $pdfData = REDCap::getPDF((int)$record, $pdf_instrument, (int)$pdf_event_id, 'false', (int)$pdf_instance, true);

                file_put_contents($tempName, $pdfData);
                $url = SERVER_NAME . $this->getSystemSetting('edocs-web-path') . '/' . basename($tempName);
                $html = '<iframe src="//' . $url . '" width="800" height="800"></iframe>';

                print("<script>$('#$field-tr td:last').append('" . ($html) . "');</script>" . PHP_EOL);
            }
        }
        print("<script>window.scrollTo(0, 0);</script>");
        echo PHP_EOL, PHP_EOL;
    }

    protected function findActionTag($dictionary, $instrument) {
        $fields = [];
        foreach ($dictionary as $field => $metadata) {
            if (($metadata['field_annotation'])  &&
                (preg_match('/@EMBEDDEDPDF=([\S]+)/', $metadata['field_annotation']))
                && ($metadata['form_name'] == $instrument)) {

                $fields[] = $field;
            }
        }
        return $fields;
    }


    protected function isFormEmpty($record, $instrument, $event_id, $instance = 1) {
        $dataDictionary = REDCap::getDataDictionary(PROJECT_ID, 'array');

        foreach($dataDictionary as $field => $metadata) {
            if (($metadata['form_name'] == $instrument)) {
                $instrument_fields[] = $field;
            }
        }

        $data = REDCap::getData(PROJECT_ID, 'array', $record, $instrument_fields, $event_id);

        // fix for non-longitudinal projects
        if (isset($data[$record][$event_id])) {
            return false;
        }

        // instance COULD be 0, so we need to check for that first
        if ((isset($data[$record]['repeat_instances'][$event_id][""]) ||
                (isset($data[$record]['repeat_instances'][$event_id][$instrument]))) && ($instance == 0)) {
            return false;
        }

        if (isset($data[$record]['repeat_instances'][$event_id][""][$instance]) ||
            isset($data[$record]['repeat_instances'][$event_id][$instrument][$instance])) {
            return false;
        }

        return true;


    }


}
