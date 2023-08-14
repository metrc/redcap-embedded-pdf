# Embedded PDF External Module for REDCap
This module allows you to embed PDFs in the middle of your form using
the action tag `@EMBEDDEDPDF`. This is useful for displaying PDFs that 
are needed for reviewing prior forms.

## Installation
* Download from GitHub at https://github.com/metrc/redcap-embedded-pdf and clone this repo into to your modules directory -OR-
download from the official REDCap Repository through the Control Center.
* Enable the module through the Control Panel
* Set the web path to your eDocs folder in the module configuration page

## Usage
I've tried to make this as easy as possible. Simply add the action tag
`@EMBEDDEDPDF=event_name:form_name:instance` to any field and the module will do the rest.

Where `event_name` is the name of the event, `form_name` is the name of the form, and `instance` is the instance number of the form. If you do not specify an instance, the module will generate a PDF with all instances

Example:  `@EMBEDDEDPDF=baseline:demographics:1`

## Bug Reporting
Please report any bugs to the GitHub repository at https://github.com/metrc/redcap-embedded-pdf/issues

## Author
Paige Julianne Sullivan<br>
psullivan@jhu.edu<br>
Major Extremity Trauma Research Consortium<br>
Bloomberg School of Public Health<br>
Johns Hopkins University<br>

## License
This project is licensed under the MIT License - see the LICENSE.md file for details

## Acknowledgments
Thanks to the entire METRC team for their support and feedback, especially
Anthony (Tony) Carlini who challenged me to write this module.
